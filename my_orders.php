<?php
session_start();
require_once 'auth_check.php';
require_once __DIR__ . '/config/connection.php';

// DB connection is provided by config/connection.php as $conn

$user_id = $_SESSION['user_id'];

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$sort_by = $_GET['sort'] ?? 'created_at';
$order_by = $_GET['order'] ?? 'DESC';

// Build the query with filters
$whereClause = "WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($status_filter)) {
    $whereClause .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($date_filter)) {
    switch($date_filter) {
        case 'today':
            $whereClause .= " AND DATE(created_at) = CURDATE()";
            break;
        case 'week':
            $whereClause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $whereClause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case 'year':
            $whereClause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)";
            break;
    }
}

// Valid sort columns
$valid_sorts = ['created_at', 'total_amount', 'status', 'order_number'];
$sort_by = in_array($sort_by, $valid_sorts) ? $sort_by : 'created_at';
$order_by = ($order_by === 'ASC') ? 'ASC' : 'DESC';

$query = "SELECT *, DATE(created_at) as order_date, TIME(created_at) as order_time 
          FROM orders 
          $whereClause 
          ORDER BY $sort_by $order_by";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();

// Get order statistics
$statsQuery = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        COALESCE(SUM(total_amount), 0) as total_spent
    FROM orders WHERE user_id = ?
");
$statsQuery->bind_param("i", $user_id);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=3.0, user-scalable=yes">
    <title>My Orders - VEDALIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --success-color: #198754;
            --success-light: #d1e7dd;
            --text-dark: #2c3e50;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, var(--success-color) 0%, #157347 100%);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
        }

        .container {
            max-width: 1200px;
        }

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .stats-row {
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .orders-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .order-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--success-color);
            transition: all 0.3s ease;
        }

        .order-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .order-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .order-number {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--text-dark);
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cff4fc;
            color: #055160;
        }

        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered, .status-completed {
            background-color: var(--success-light);
            color: #0a3622;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-item i {
            color: var(--success-color);
            width: 20px;
        }

        .amount {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--success-color);
        }

        .btn-success {
            background: var(--success-color);
            border-color: var(--success-color);
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        .btn-outline-success {
            border-color: var(--success-color);
            color: var(--success-color);
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        .btn-outline-success:hover {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        .filter-form {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fa-solid fa-leaf"></i> VEDALIFE
            </a>
            <div class="ms-auto">
                <a href="profile.php" class="btn btn-outline-light me-2">
                    <i class="fa-solid fa-user"></i> Profile
                </a>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="mb-2">
                        <i class="fa-solid fa-shopping-bag text-success"></i> My Orders
                    </h1>
                    <p class="text-muted mb-0">Track and manage all your orders</p>
                </div>
                <a href="products.php" class="btn btn-success">
                    <i class="fa-solid fa-plus"></i> Place New Order
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row stats-row">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number text-primary"><?php echo $stats['total_orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number text-warning"><?php echo $stats['pending_orders']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number text-success"><?php echo $stats['completed_orders']; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number text-success">₹<?php echo number_format($stats['total_spent'], 2); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <h5 class="mb-3"><i class="fa-solid fa-filter"></i> Filter Orders</h5>
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="form-label">Date Range</label>
                    <select name="date" class="form-select">
                        <option value="">All Time</option>
                        <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="year" <?php echo $date_filter === 'year' ? 'selected' : ''; ?>>Last Year</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Date</option>
                        <option value="total_amount" <?php echo $sort_by === 'total_amount' ? 'selected' : ''; ?>>Amount</option>
                        <option value="status" <?php echo $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
                        <option value="order_number" <?php echo $sort_by === 'order_number' ? 'selected' : ''; ?>>Order Number</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="form-label">Order</label>
                    <select name="order" class="form-select">
                        <option value="DESC" <?php echo $order_by === 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="ASC" <?php echo $order_by === 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-search"></i> Apply Filters
                    </button>
                    <a href="my_orders.php" class="btn btn-outline-secondary ms-2">
                        <i class="fa-solid fa-refresh"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Orders List -->
        <div class="orders-card">
            <h5 class="mb-4">
                <i class="fa-solid fa-list"></i> Order History 
                <span class="badge bg-success ms-2"><?php echo $orders->num_rows; ?> orders</span>
            </h5>

            <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div class="order-item">
                        <div class="order-header">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <div class="amount">₹<?php echo number_format($order['total_amount'], 2); ?></div>
                        </div>

                        <div class="order-details">
                            <div class="detail-item">
                                <i class="fa-solid fa-calendar"></i>
                                <span><?php echo date('M d, Y', strtotime($order['order_date'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fa-solid fa-clock"></i>
                                <span><?php echo date('g:i A', strtotime($order['order_time'])); ?></span>
                            </div>
                            <?php if (!empty($order['shipping_address'])): ?>
                            <div class="detail-item">
                                <i class="fa-solid fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars(substr($order['shipping_address'], 0, 30)) . '...'; ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <i class="fa-solid fa-user"></i>
                                <span><?php echo htmlspecialchars($order['shipping_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fa-solid fa-phone"></i>
                                <span><?php echo htmlspecialchars($order['shipping_phone']); ?></span>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-success btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                <i class="fa-solid fa-eye"></i> View Details
                            </button>
                            <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                            <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                <i class="fa-solid fa-times"></i> Cancel
                            </button>
                            <?php endif; ?>
                            <?php if ($order['status'] === 'delivered'): ?>
                            <button class="btn btn-outline-primary btn-sm" onclick="reorder(<?php echo $order['id']; ?>)">
                                <i class="fa-solid fa-redo"></i> Reorder
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-shopping-bag"></i>
                    <h4>No Orders Found</h4>
                    <p>You haven't placed any orders yet or no orders match your current filters.</p>
                    <div class="mt-3">
                        <a href="products.php" class="btn btn-success me-2">
                            <i class="fa-solid fa-shopping-cart"></i> Start Shopping
                        </a>
                        <a href="my_orders.php" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-refresh"></i> Clear Filters
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // View order details
        function viewOrderDetails(orderId) {
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            const content = document.getElementById('orderDetailsContent');
            
            content.innerHTML = '<div class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>';
            modal.show();
            
            // Here you would make an AJAX call to get order details
            // For now, showing a placeholder
            setTimeout(() => {
                content.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fa-solid fa-info-circle"></i>
                        Order details functionality can be implemented with AJAX to fetch detailed order information.
                    </div>
                    <p>Order ID: ${orderId}</p>
                    <p>This would show detailed order information including:</p>
                    <ul>
                        <li>Product details</li>
                        <li>Shipping information</li>
                        <li>Payment details</li>
                        <li>Order timeline</li>
                    </ul>
                `;
            }, 500);
        }
        
        // Cancel order
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                // Here you would make an AJAX call to cancel the order
                alert('Order cancellation functionality would be implemented here.');
            }
        }
        
        // Reorder
        function reorder(orderId) {
            if (confirm('Would you like to reorder the same items?')) {
                // Here you would add items to cart and redirect to checkout
                alert('Reorder functionality would be implemented here.');
            }
        }
        
        // Auto-refresh every 2 minutes for status updates
        setInterval(() => {
            // You could implement auto-refresh for order status updates
        }, 120000);
    </script>
</body>
</html>

<?php
$conn->close();
?>