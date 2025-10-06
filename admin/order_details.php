<?php
require_once 'admin_auth.php';
requireAdminLogin();

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    echo '<div class="text-center text-danger">Invalid order ID</div>';
    exit;
}

$conn = getAdminDbConnection();

// Get order details with customer info
$orderQuery = $conn->prepare("
    SELECT o.*, u.username, u.email as user_email
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$orderQuery->bind_param("i", $order_id);
$orderQuery->execute();
$order = $orderQuery->get_result()->fetch_assoc();

if (!$order) {
    echo '<div class="text-center text-danger">Order not found</div>';
    exit;
}

// Get order items
$itemsQuery = $conn->prepare("
    SELECT oi.*, p.name as current_product_name, p.image as product_image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id
");
$itemsQuery->bind_param("i", $order_id);
$itemsQuery->execute();
$items = $itemsQuery->get_result();

$conn->close();
?>

<div class="row">
    <!-- Order Information -->
    <div class="col-md-6">
        <h6 class="text-success mb-3"><i class="fas fa-file-invoice"></i> Order Information</h6>
        <table class="table table-borderless">
            <tr>
                <td><strong>Order Number:</strong></td>
                <td><span class="badge bg-primary">#<?php echo htmlspecialchars($order['order_number']); ?></span></td>
            </tr>
            <tr>
                <td><strong>Order Date:</strong></td>
                <td><?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>
                    <span class="badge <?php echo getStatusBadgeClass($order['status']); ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Payment Status:</strong></td>
                <td>
                    <span class="badge <?php echo getStatusBadgeClass($order['payment_status']); ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Payment Method:</strong></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
            </tr>
            <tr>
                <td><strong>Total Amount:</strong></td>
                <td><strong class="text-success fs-5"><?php echo formatCurrency($order['total_amount']); ?></strong></td>
            </tr>
        </table>
    </div>
    
    <!-- Customer Information -->
    <div class="col-md-6">
        <h6 class="text-success mb-3"><i class="fas fa-user"></i> Customer Information</h6>
        <table class="table table-borderless">
            <tr>
                <td><strong>Customer:</strong></td>
                <td><?php echo htmlspecialchars($order['shipping_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Username:</strong></td>
                <td>@<?php echo htmlspecialchars($order['username']); ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><?php echo htmlspecialchars($order['user_email']); ?></td>
            </tr>
            <tr>
                <td><strong>Phone:</strong></td>
                <td><?php echo htmlspecialchars($order['shipping_phone']); ?></td>
            </tr>
            <tr>
                <td><strong>Shipping Address:</strong></td>
                <td><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></td>
            </tr>
        </table>
    </div>
</div>

<hr class="my-4">

<!-- Order Items -->
<h6 class="text-success mb-3"><i class="fas fa-box"></i> Order Items</h6>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while($item = $items->fetch_assoc()): ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <?php if (!empty($item['product_image'])): ?>
                        <img src="../<?php echo htmlspecialchars($item['product_image']); ?>" 
                             alt="Product" class="me-2 rounded" style="width: 40px; height: 40px; object-fit: cover;"
                             onerror="this.style.display='none'">
                        <?php endif; ?>
                        <div>
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                            <?php if ($item['current_product_name'] && $item['current_product_name'] != $item['product_name']): ?>
                            <small class="text-muted d-block">
                                Current name: <?php echo htmlspecialchars($item['current_product_name']); ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td><?php echo formatCurrency($item['product_price']); ?></td>
                <td>
                    <span class="badge bg-secondary"><?php echo $item['quantity']; ?></span>
                </td>
                <td><strong><?php echo formatCurrency($item['subtotal']); ?></strong></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr class="table-success">
                <th colspan="3">Total Amount:</th>
                <th><?php echo formatCurrency($order['total_amount']); ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<?php if (!empty($order['notes'])): ?>
<hr class="my-4">
<h6 class="text-success mb-3"><i class="fas fa-sticky-note"></i> Internal Notes</h6>
<div class="alert alert-info">
    <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
</div>
<?php endif; ?>

<hr class="my-4">

<!-- Timeline -->
<h6 class="text-success mb-3"><i class="fas fa-history"></i> Order Timeline</h6>
<div class="timeline">
    <div class="timeline-item">
        <div class="timeline-marker bg-primary"></div>
        <div class="timeline-content">
            <h6>Order Placed</h6>
            <small class="text-muted"><?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?></small>
        </div>
    </div>
    
    <?php if ($order['status'] != 'pending'): ?>
    <div class="timeline-item">
        <div class="timeline-marker bg-info"></div>
        <div class="timeline-content">
            <h6>Status: <?php echo ucfirst($order['status']); ?></h6>
            <small class="text-muted">Updated: <?php echo date('M d, Y g:i A', strtotime($order['updated_at'])); ?></small>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($order['payment_status'] == 'paid'): ?>
    <div class="timeline-item">
        <div class="timeline-marker bg-success"></div>
        <div class="timeline-content">
            <h6>Payment Confirmed</h6>
            <small class="text-muted">Method: <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></small>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -1.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid white;
}

.timeline-content h6 {
    margin-bottom: 0.25rem;
    font-weight: 600;
}
</style>