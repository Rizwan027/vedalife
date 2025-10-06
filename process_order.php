<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vedalife";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required_fields = ['fullName', 'phone', 'address', 'cart', 'total'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Optional fields
    $payment_method = isset($input['paymentMethod']) ? trim($input['paymentMethod']) : 'cod';
    $notes = isset($input['notes']) ? trim($input['notes']) : '';
    
    $user_id = $_SESSION['user_id'];
    $full_name = trim($input['fullName']);
    $phone = trim($input['phone']);
    $address = trim($input['address']);
    $cart_items = $input['cart'];
    $total_amount = floatval($input['total']);
    
    // Validate cart is not empty
    if (empty($cart_items) || !is_array($cart_items)) {
        throw new Exception("Cart is empty or invalid");
    }
    
    // Validate total amount
    if ($total_amount <= 0) {
        throw new Exception("Invalid total amount");
    }
    
    // Generate unique order number
    $order_number = 'ORD' . date('Ymd') . sprintf('%04d', rand(1000, 9999));
    
    // Check if order number already exists (unlikely but better to check)
    $checkOrderStmt = $conn->prepare("SELECT id FROM orders WHERE order_number = ?");
    $checkOrderStmt->bind_param("s", $order_number);
    $checkOrderStmt->execute();
    
    if ($checkOrderStmt->get_result()->num_rows > 0) {
        // Generate a new one if collision
        $order_number = 'ORD' . date('Ymd') . sprintf('%04d', rand(1000, 9999)) . rand(10, 99);
    }
    $checkOrderStmt->close();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Set payment status based on payment method
    $payment_status = ($payment_method === 'cod') ? 'pending' : 'pending';
    
    // Insert main order record
    $orderStmt = $conn->prepare("
        INSERT INTO orders (
            user_id, order_number, shipping_name, shipping_phone, shipping_address, 
            total_amount, status, payment_method, notes
        ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)
    ");
    
    $orderStmt->bind_param("issssdss", 
        $user_id, $order_number, $full_name, $phone, $address, $total_amount,
        $payment_method, $notes
    );
    
    if (!$orderStmt->execute()) {
        throw new Exception("Failed to create order: " . $orderStmt->error);
    }
    
    $order_id = $conn->insert_id;
    $orderStmt->close();
    
    // Insert order items (use NULL for product_id if product doesn't exist in database)
    $itemStmt = $conn->prepare("
        INSERT INTO order_items (
            order_id, product_id, product_name, product_price, quantity, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($cart_items as $item) {
        $product_name = $item['name'];
        $product_code = isset($item['id']) ? $item['id'] : null;
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $subtotal = $quantity * $price;
        
        // Try to find existing product by product_code first, then by name
        $product_id = null;
        if ($product_code) {
            $findProductStmt = $conn->prepare("SELECT id FROM products WHERE product_code = ? LIMIT 1");
            $findProductStmt->bind_param("s", $product_code);
            $findProductStmt->execute();
            $productResult = $findProductStmt->get_result();
            
            if ($productResult->num_rows > 0) {
                $product_id = $productResult->fetch_assoc()['id'];
            }
            $findProductStmt->close();
        }
        
        // If not found by product_code, try by name
        if ($product_id === null) {
            $findProductStmt = $conn->prepare("SELECT id FROM products WHERE name = ? LIMIT 1");
            $findProductStmt->bind_param("s", $product_name);
            $findProductStmt->execute();
            $productResult = $findProductStmt->get_result();
            
            if ($productResult->num_rows > 0) {
                $product_id = $productResult->fetch_assoc()['id'];
            }
            $findProductStmt->close();
        }
        
        if ($product_id === null) {
            $itemStmt->bind_param("issidd", 
                $order_id, $product_id, $product_name, $price, $quantity, $subtotal
            );
        } else {
            $itemStmt->bind_param("iisidd", 
                $order_id, $product_id, $product_name, $price, $quantity, $subtotal
            );
        }
        
        if (!$itemStmt->execute()) {
            throw new Exception("Failed to add order item: " . $itemStmt->error);
        }
    }
    $itemStmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Get user email for confirmation
    $userStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userEmail = $userResult->fetch_assoc()['email'] ?? null;
    $userStmt->close();
    
    // Send order confirmation email
    if ($userEmail) {
        try {
            require_once 'email_config.php';
            $emailSender = new EmailSender();
            
            $orderData = [
                'order_number' => $order_number,
                'customer_name' => $full_name,
                'phone' => $phone,
                'shipping_address' => $address,
                'payment_method' => $payment_method,
                'items' => $cart_items,
                'total' => $total_amount
            ];
            
            $emailSender->sendOrderConfirmationEmail($userEmail, $orderData);
        } catch (Exception $emailError) {
            // Log email error but don't fail the order
            error_log('Failed to send order confirmation email: ' . $emailError->getMessage());
        }
    }
    
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'order_number' => $order_number,
        'redirect' => 'my_orders.php'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} finally {
    $conn->close();
}
?>