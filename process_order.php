<?php
session_start();
require_once 'config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit();
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'order_id' => null
];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
    $zip = isset($_POST['zip']) ? trim($_POST['zip']) : '';
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($state) || empty($zip) || empty($payment_method)) {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response);
        exit();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, phone, address, city, state, zip, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("issssssss", $user_id, $name, $email, $phone, $address, $city, $state, $zip, $payment_method);
        $stmt->execute();
        
        // Get order ID
        $order_id = $conn->insert_id;
        
        // Get cart items
        $cart_query = $conn->prepare("SELECT c.product_id, c.quantity, p.price, p.name, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $cart_query->bind_param("i", $user_id);
        $cart_query->execute();
        $cart_result = $cart_query->get_result();
        
        $total_amount = 0;
        
        // Check if cart is empty
        if ($cart_result->num_rows === 0) {
            throw new Exception("Your cart is empty.");
        }
        
        // Process each cart item
        while ($item = $cart_result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $product_name = $item['name'];
            $current_stock = $item['stock'];
            
            // Check if enough stock
            if ($quantity > $current_stock) {
                throw new Exception("Not enough stock for {$product_name}. Only {$current_stock} available.");
            }
            
            // Calculate item total
            $item_total = $price * $quantity;
            $total_amount += $item_total;
            
            // Add to order items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
            $item_stmt->bind_param("iiidi", $order_id, $product_id, $quantity, $price, $item_total);
            $item_stmt->execute();
            
            // Update product stock
            $new_stock = $current_stock - $quantity;
            $stock_stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stock_stmt->bind_param("ii", $new_stock, $product_id);
            $stock_stmt->execute();
        }
        
        // Update order with total amount
        $update_order = $conn->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
        $update_order->bind_param("di", $total_amount, $order_id);
        $update_order->execute();
        
        // Clear user's cart
        $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_cart->bind_param("i", $user_id);
        $clear_cart->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Set success response
        $response['success'] = true;
        $response['message'] = 'Order placed successfully!';
        $response['order_id'] = $order_id;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>