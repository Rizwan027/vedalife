<?php
session_start();
require_once 'config/connection.php';

// Ensure clean JSON responses (avoid HTML warnings/notices)
@ini_set('display_errors', 0);
header('Content-Type: application/json');
ob_start();
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode(['success' => false, 'message' => 'Internal server error']);
    }
});

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Attempt to parse JSON body (from checkout.js)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Fallback to form fields if JSON not provided
$isJson = is_array($data) && !empty($data);

try {
    if ($isJson) {
        $fullName = trim($data['fullName'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $address = trim($data['address'] ?? '');
        $paymentMethod = trim($data['paymentMethod'] ?? 'cod');
        $notes = trim($data['notes'] ?? '');
        $cartItems = $data['cart'] ?? [];
        $clientTotal = (float)($data['total'] ?? 0);
    } else {
        // Basic form fallback (legacy)
        $fullName = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? 'cod');
        $notes = trim($_POST['notes'] ?? '');
        $cartItems = []; // Not supported via legacy form
        $clientTotal = 0;
    }

    if (empty($fullName) || empty($phone) || empty($address) || ($isJson && empty($cartItems))) {
        throw new Exception('Missing required order details');
    }

    // Try to fetch user email for records if needed (not stored in orders schema)
    $u = $conn->prepare('SELECT email FROM users WHERE id = ?');
    $u->bind_param('i', $user_id);
    $u->execute();
    $userEmail = ($u->get_result()->fetch_assoc()['email'] ?? null);
    $u->close();

    $conn->begin_transaction();

    // Defensive: validate cart content type
    if (!is_array($cartItems) || count($cartItems) === 0) {
        throw new Exception('Cart is empty or invalid');
    }

    // Generate unique order number (max 20 chars to fit schema)
    // Format: ORD + YYYYMMDDHHMMSS (14) + 3 random digits = 20
    $order_number = 'ORD' . date('YmdHis') . sprintf('%03d', random_int(0, 999));

    // Insert into orders matching the actual schema
    $stmt = $conn->prepare("INSERT INTO orders (
        user_id, order_number, status, payment_status, payment_method, total_amount,
        shipping_name, shipping_phone, shipping_address, notes, created_at
    ) VALUES (
        ?, ?, 'pending', 'pending', ?, 0, ?, ?, ?, ?, NOW()
    )");
    if (!$stmt) {
        throw new Exception('Failed to prepare order insert');
    }
    $stmt->bind_param('issssss', $user_id, $order_number, $paymentMethod, $fullName, $phone, $address, $notes);
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order');
    }
    $order_id = (int)$conn->insert_id;
    $stmt->close();

    $total = 0.0;

    if ($isJson) {
        // Prepare atomic stock decrement
        $dec = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        if (!$dec) {
            throw new Exception('Failed to prepare stock update');
        }

        foreach ($cartItems as $item) {
            // item.id may be like "product-123" or just numeric/string
            $rawId = (string)($item['id'] ?? '');
            if (preg_match('~(\\d+)~', $rawId, $m)) {
                $product_id = (int)$m[1];
            } else {
                // Try to resolve by name if id is missing
                $product_id = 0;
            }

            $qty = (int)($item['quantity'] ?? 0);
            if ($qty <= 0) {
                throw new Exception('Invalid quantity for an item');
            }

            // Fetch product to get authoritative price and current stock
            if ($product_id > 0) {
                $ps = $conn->prepare('SELECT price, stock, name FROM products WHERE id = ?');
                $ps->bind_param('i', $product_id);
                $ps->execute();
                $pr = $ps->get_result();
                $product = $pr->fetch_assoc();
                $ps->close();
            } else {
                $nm = trim((string)($item['name'] ?? ''));
                $ps = $conn->prepare('SELECT id, price, stock, name FROM products WHERE name = ? LIMIT 1');
                $ps->bind_param('s', $nm);
                $ps->execute();
                $pr = $ps->get_result();
                $product = $pr->fetch_assoc();
                $product_id = $product ? (int)$product['id'] : 0;
                $ps->close();
            }

            if (!$product_id || !$product) {
                throw new Exception('Product not found for an item in cart');
            }

            $price = (float)$product['price'];
            $itemTotal = $price * $qty;

            // Atomic stock decrement
            $dec->bind_param('iii', $qty, $product_id, $qty);
            $dec->execute();
            if ($dec->affected_rows !== 1) {
                throw new Exception('Insufficient stock for ' . ($product['name'] ?? 'item'));
            }

            // Insert order item matching schema (with product_name and subtotal)
            $oi = $conn->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
            $pname = $product['name'];
            $oi->bind_param('iisidd', $order_id, $product_id, $pname, $qty, $price, $itemTotal);
            if (!$oi->execute()) {
                throw new Exception('Failed to insert order item');
            }
            $oi->close();

            $total += $itemTotal;
        }

        $dec->close();
    } else {
        // Legacy path: no items provided
        throw new Exception('No cart items provided');
    }

    // Update total amount
    $uo = $conn->prepare('UPDATE orders SET total_amount = ? WHERE id = ?');
    $uo->bind_param('di', $total, $order_id);
    $uo->execute();
    $uo->close();

    $conn->commit();

    $payload = [
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'order_number' => $order_number
    ];
    if (ob_get_level() > 0) { @ob_end_clean(); }
    echo json_encode($payload);
} catch (Exception $e) {
    if ($conn && $conn->errno === 0) {
        // Try to rollback only if a transaction is active
        @$conn->rollback();
    }
    http_response_code(400);
    if (ob_get_level() > 0) { @ob_end_clean(); }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
