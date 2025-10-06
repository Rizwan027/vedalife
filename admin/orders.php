<?php
// Simplified, robust Orders page
require_once 'admin_auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();
$conn = getAdminDbConnection();
$message = '';
$message_type = '';

// Check if orders table exists
function tableExists($conn, $table) {
    $res = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
    return $res && $res->num_rows > 0;
}

$orders_table_ok = tableExists($conn, 'orders');
$users_table_ok = tableExists($conn, 'users');

// Handle updates safely (only if orders table exists)
if ($orders_table_ok) {
    if (($_POST['action'] ?? '') === 'update_status' && isset($_POST['order_id'], $_POST['status'])) {
        $order_id = (int)$_POST['order_id'];
        $status = $_POST['status'];
        if ($stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?")) {
            $stmt->bind_param("si", $status, $order_id);
            if ($stmt->execute()) {
                $message = 'Order status updated successfully!';
                $message_type = 'success';
                logAdminActivity('order_status_update', "Updated order ID: $order_id status to: $status");
            } else {
                $message = 'Error updating order status.';
                $message_type = 'danger';
            }
        }
    }
    
    if (($_POST['action'] ?? '') === 'update_payment_status' && isset($_POST['order_id'], $_POST['payment_status'])) {
        $order_id = (int)$_POST['order_id'];
        $payment_status = $_POST['payment_status'];
        if ($stmt = $conn->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?")) {
            $stmt->bind_param("si", $payment_status, $order_id);
            if ($stmt->execute()) {
                $message = 'Payment status updated successfully!';
                $message_type = 'success';
                logAdminActivity('payment_status_update', "Updated order ID: $order_id payment status to: $payment_status");
            } else {
                $message = 'Error updating payment status.';
                $message_type = 'danger';
            }
        }
    }
    
    if (($_POST['action'] ?? '') === 'update_notes' && isset($_POST['order_id'], $_POST['notes'])) {
        $order_id = (int)$_POST['order_id'];
        $notes = trim($_POST['notes']);
        if ($stmt = $conn->prepare("UPDATE orders SET notes = ?, updated_at = NOW() WHERE id = ?")) {
            $stmt->bind_param("si", $notes, $order_id);
            if ($stmt->execute()) {
                $message = 'Order notes updated successfully!';
                $message_type = 'success';
                logAdminActivity('order_notes_update', "Updated notes for order ID: $order_id");
            } else {
                $message = 'Error updating order notes.';
                $message_type = 'danger';
            }
        }
    }
}

// Pagination & filters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$payment_filter = $_GET['payment'] ?? '';
$date_filter = $_GET['date'] ?? '';

$whereClause = 'WHERE 1=1';
$params = [];
$types = '';

if (!empty($search)) {
    $whereClause .= ' AND (o.order_number LIKE ? OR o.shipping_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}
if (!empty($status_filter)) { $whereClause .= ' AND o.status = ?'; $params[] = $status_filter; $types .= 's'; }
if (!empty($payment_filter)) { $whereClause .= ' AND o.payment_status = ?'; $params[] = $payment_filter; $types .= 's'; }
if (!empty($date_filter)) { $whereClause .= ' AND DATE(o.created_at) = ?'; $params[] = $date_filter; $types .= 's'; }

// Initialize data
$totalOrders = 0; 
$totalPages = 1; 
$orders = null; 

if ($orders_table_ok && $users_table_ok) {
    // Count
    $countSql = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id $whereClause";
    if ($countStmt = $conn->prepare($countSql)) {
        if (!empty($params)) { $countStmt->bind_param($types, ...$params); }
        if ($countStmt->execute()) { 
            $totalOrders = ($countStmt->get_result()->fetch_assoc()['total']) ?? 0; 
            $totalPages = max(1, (int)ceil($totalOrders / $limit)); 
        }
    }

    // List orders
    $listSql = "SELECT o.id, o.order_number, o.total_amount, o.status, o.payment_status, o.payment_method, o.created_at, o.shipping_name, o.shipping_phone, o.notes, u.username
                FROM orders o JOIN users u ON o.user_id = u.id $whereClause ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
    $listTypes = $types . 'ii';
    $listParams = $params; 
    $listParams[] = $limit; 
    $listParams[] = $offset;
    
    if ($listStmt = $conn->prepare($listSql)) {
        if (!empty($params)) { 
            $listStmt->bind_param($listTypes, ...$listParams); 
        } else { 
            $listStmt->bind_param('ii', $limit, $offset); 
        }
        if ($listStmt->execute()) { 
            $orders = $listStmt->get_result(); 
        }
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - VEDAMRUT Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header" data-aos="fade-down">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">Order Management</h3>
                    <p class="mb-0 text-muted">Process and track all customer orders</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-gradient-primary" onclick="refreshOrders()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>


        <div class="search-filters" data-aos="fade-up" data-aos-delay="700">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter==='pending'?'selected':''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter==='processing'?'selected':''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter==='shipped'?'selected':''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter==='delivered'?'selected':''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter==='cancelled'?'selected':''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment" class="form-select">
                        <option value="">Payment Status</option>
                        <option value="pending" <?php echo $payment_filter==='pending'?'selected':''; ?>>Pending</option>
                        <option value="paid" <?php echo $payment_filter==='paid'?'selected':''; ?>>Paid</option>
                        <option value="failed" <?php echo $payment_filter==='failed'?'selected':''; ?>>Failed</option>
                        <option value="refunded" <?php echo $payment_filter==='refunded'?'selected':''; ?>>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="orders.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <div class="card" data-aos="fade-up" data-aos-delay="800">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i> Orders List 
                    <span class="badge status-active ms-2"><?php echo number_format((int)$totalOrders); ?> total</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Placed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong>
                                        <small class="text-muted d-block">@<?php echo htmlspecialchars($order['username']); ?></small>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($order['shipping_phone']); ?></small>
                                    </td>
                                    <td>
                                        <div class="order-amount"><?php echo formatCurrency($order['total_amount']); ?></div>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline" onchange="this.submit()">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                            <select name="status" class="form-select form-select-sm status-select <?php echo getStatusBadgeClass($order['status']); ?>">
                                                <option value="pending" <?php echo $order['status']==='pending'?'selected':''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status']==='processing'?'selected':''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status']==='shipped'?'selected':''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status']==='delivered'?'selected':''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status']==='cancelled'?'selected':''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline" onchange="this.submit()">
                                            <input type="hidden" name="action" value="update_payment_status">
                                            <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                            <select name="payment_status" class="form-select form-select-sm payment-select <?php echo getStatusBadgeClass($order['payment_status']); ?>">
                                                <option value="pending" <?php echo $order['payment_status']==='pending'?'selected':''; ?>>Pending</option>
                                                <option value="paid" <?php echo $order['payment_status']==='paid'?'selected':''; ?>>Paid</option>
                                                <option value="failed" <?php echo $order['payment_status']==='failed'?'selected':''; ?>>Failed</option>
                                                <option value="refunded" <?php echo $order['payment_status']==='refunded'?'selected':''; ?>>Refunded</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewOrderModal" onclick="viewOrder(<?php echo (int)$order['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#notesModal" onclick="editNotes(<?php echo (int)$order['id']; ?>, '<?php echo htmlspecialchars($order['notes'] ?? ''); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="printOrder(<?php echo (int)$order['id']; ?>)">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No orders found</p>
                        <small class="text-muted">Orders will appear here once customers start purchasing</small>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment=<?php echo urlencode($payment_filter); ?>&date=<?php echo urlencode($date_filter); ?>">Previous</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment=<?php echo urlencode($payment_filter); ?>&date=<?php echo urlencode($date_filter); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment=<?php echo urlencode($payment_filter); ?>&date=<?php echo urlencode($date_filter); ?>">Next</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Order Modal -->
    <div class="modal fade" id="viewOrderModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Loading order details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="printOrderDetails()">
                        <i class="fas fa-print"></i> Print Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Notes Modal -->
    <div class="modal fade" id="notesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Order Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_notes">
                        <input type="hidden" name="order_id" id="notesOrderId">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Internal Notes</label>
                            <textarea class="form-control" name="notes" id="notesText" rows="4" placeholder="Add internal notes for this order..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Notes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        AOS.init({ duration: 600, easing: 'ease-in-out', once: true });
        
        function viewOrder(orderId) {
            fetch(`order_details.php?id=${orderId}`)
                .then(r => r.text())
                .then(html => {
                    document.getElementById('orderDetailsContent').innerHTML = html;
                })
                .catch(() => {
                    document.getElementById('orderDetailsContent').innerHTML = '<div class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading order details</div>';
                });
        }
        
        function editNotes(orderId, currentNotes) {
            document.getElementById('notesOrderId').value = orderId;
            document.getElementById('notesText').value = currentNotes;
        }
        
        function printOrder(orderId) {
            window.open(`print_order.php?id=${orderId}`, '_blank');
        }
        
        function printOrderDetails() {
            const content = document.getElementById('orderDetailsContent').innerHTML;
            const w = window.open('', '_blank');
            w.document.write(`<!doctype html><html><head><title>Order Details</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="p-4">${content}<script>window.onload=function(){window.print();window.close();}<\/script><\/body><\/html>`);
        }
        
        function refreshOrders() {
            window.location.reload();
        }
    </script>
</body>
</html>

<?php 
if ($conn && !$conn->connect_errno) {
    $conn->close();
}
?>