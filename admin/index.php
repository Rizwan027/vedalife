<?php
require_once 'admin_auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();
$stats = getDashboardStats();

// Get recent activity data
$conn = getAdminDbConnection();

// Recent bookings
$recentBookings = $conn->query("
    SELECT b.*, u.username 
    FROM booking b 
    JOIN users u ON b.user_id = u.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");

// Recent orders
$recentOrders = $conn->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

// Recent users
$recentUsers = $conn->query("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Monthly revenue data for chart
$monthlyRevenue = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE status != 'cancelled' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");

$monthlyData = [];
while ($row = $monthlyRevenue->fetch_assoc()) {
    $monthlyData[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VedaLife</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #2c6e49;
            --primary-light: #4c956c;
            --primary-dark: #1a4731;
            --secondary-color: #ffc145;
            --accent-color: #ff6b6b;
            --text-dark: #2d3142;
            --text-light: #4f5d75;
            --background-light: #f8fffe;
            --background-white: #ffffff;
            --sidebar-width: 280px;
            --shadow-soft: 0 10px 30px rgba(44, 110, 73, 0.08);
            --shadow-medium: 0 20px 40px rgba(44, 110, 73, 0.12);
            --shadow-large: 0 30px 60px rgba(44, 110, 73, 0.15);
            --gradient-primary: linear-gradient(135deg, #2c6e49 0%, #4c956c 100%);
            --gradient-secondary: linear-gradient(135deg, #ffc145 0%, #ffb347 100%);
            --gradient-sidebar: linear-gradient(180deg, #2c6e49 0%, #1a4731 50%, #2c6e49 100%);
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 20px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--background-light);
            overflow-x: hidden;
        }
        
        .sidebar {
            background: var(--gradient-sidebar);
            color: white;
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: var(--transition-smooth);
            backdrop-filter: blur(15px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow-large);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.02)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)" /></svg>');
            pointer-events: none;
        }
        
        .sidebar-header {
            position: relative;
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        
        .sidebar-header h4 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .sidebar-header i {
            font-size: 2rem;
            color: var(--secondary-color);
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .sidebar-header small {
            opacity: 0.9;
            font-size: 0.85rem;
            font-weight: 400;
        }
        
        .sidebar-nav {
            position: relative;
            padding: 1rem 0;
            flex: 1;
        }
        
        .nav-link {
            position: relative;
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            border-radius: 0;
            transition: var(--transition-smooth);
            border: none;
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            margin: 0.2rem 1rem;
            border-radius: 15px;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 4px;
            height: 0;
            background: var(--secondary-color);
            border-radius: 0 4px 4px 0;
            transition: var(--transition-smooth);
            transform: translateY(-50%);
        }
        
        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .nav-link:hover::before {
            height: 60%;
        }
        
        .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            transform: translateX(8px);
        }
        
        .nav-link.active::before {
            height: 80%;
            background: var(--secondary-color);
            box-shadow: 0 0 20px rgba(255, 193, 69, 0.4);
        }
        
        .nav-link i {
            margin-right: 1rem;
            width: 20px;
            font-size: 1.1rem;
            text-align: center;
            transition: var(--transition-smooth);
        }
        
        .nav-link:hover i,
        .nav-link.active i {
            color: var(--secondary-color);
            transform: scale(1.1);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }
        
        .top-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            box-shadow: var(--shadow-soft);
            padding: 1.5rem 2rem;
            margin: -2rem -2rem 2rem -2rem;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .top-navbar h3 {
            font-family: 'Cormorant Garamond', serif;
            color: var(--primary-dark);
            font-weight: 700;
        }
        
        
        .stats-card {
            background: var(--background-white);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-soft);
            transition: var(--transition-smooth);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }
        
        .stats-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-medium);
            border-color: rgba(44, 110, 73, 0.1);
        }
        
        .stats-card:nth-child(1)::before {
            background: var(--gradient-primary);
        }
        
        .stats-card:nth-child(2)::before {
            background: var(--gradient-secondary);
        }
        
        .stats-card:nth-child(3)::before {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
        }
        
        .stats-card:nth-child(4)::before {
            background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        /* Specific sizing for different stat cards */
        .stats-card:nth-child(4) .stats-number {
            font-size: 1.8rem;
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .stats-change {
            font-size: 0.8rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
        }
        
        .stats-change.positive {
            background: #d4edda;
            color: #155724;
        }
        
        .stats-change.negative {
            background: #f8d7da;
            color: #721c24;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            background: var(--background-white);
            transition: var(--transition-smooth);
            overflow: hidden;
        }
        
        .card:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            border: none;
            padding: 1.5rem;
            position: relative;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .badge {
            font-size: 0.7rem;
            padding: 0.4rem 0.6rem;
        }
        
        
        /* Enhanced Logout Button Styles */
        .logout-section {
            position: relative;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 1rem 1rem 1.5rem 1rem;
            margin-top: auto;
            flex-shrink: 0;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.4rem;
            width: 70%;
            margin: 0 auto;
            transition: var(--transition-smooth);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 8px rgba(255, 107, 107, 0.2);
            position: relative;
            overflow: hidden;
            font-size: 0.9rem;
        }
        
        .logout-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: var(--transition-smooth);
        }
        
        .logout-btn:hover {
            background: linear-gradient(135deg, #ee5a6f 0%, #d63384 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255, 107, 107, 0.4);
            text-decoration: none;
        }
        
        .logout-btn:hover::before {
            left: 100%;
        }
        
        .logout-btn i {
            margin-right: 0.6rem;
            font-size: 1rem;
            transition: var(--transition-smooth);
        }
        
        .logout-btn span {
            font-size: 0.9rem;
        }
        
        .logout-btn:hover i {
            transform: rotate(10deg) scale(1.1);
        }
        
        .admin-profile {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 1rem 0.8rem;
            margin-bottom: 1rem;
            text-align: center;
            position: relative;
        }
        
        .admin-profile::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3));
            transform: translateX(-50%);
        }
        
        .admin-avatar {
            width: 45px;
            height: 45px;
            background: var(--gradient-secondary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 6px 20px rgba(255, 193, 69, 0.3);
            position: relative;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .admin-avatar::after {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            border-radius: 50%;
            border: 2px solid transparent;
            background: linear-gradient(135deg, var(--secondary-color), transparent) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: exclude;
            mask-composite: exclude;
            animation: rotate 3s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .session-info {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.8);
            margin-top: 0.5rem;
            line-height: 1.4;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar d-flex flex-column">
        <div class="sidebar-header">
            <h4><i class="fas fa-leaf"></i> VEDAMRUT</h4>
            <small>Admin Dashboard</small>
        </div>
        
        <div class="sidebar-nav">
            <a href="index.php" class="nav-link active">
                <i class="fas fa-dashboard"></i> Dashboard
            </a>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="appointments.php" class="nav-link">
                <i class="fas fa-calendar-check"></i> Appointments
            </a>
            <a href="orders.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
            <a href="products.php" class="nav-link">
                <i class="fas fa-box"></i> Products
            </a>
            <a href="settings.php" class="nav-link">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
        
        <div class="mt-auto p-3 logout-section">
            <!-- Admin Profile Info -->
            <div class="admin-profile">
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($admin['full_name'], 0, 1)); ?>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 0.9rem;"><?php echo htmlspecialchars($admin['full_name']); ?></div>
                    <div class="session-info">
                        <i class="fas fa-user-shield"></i> <?php echo ucfirst($admin['role']); ?><br>
                        <i class="fas fa-clock"></i> <?php echo date('g:i A'); ?>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Logout Button -->
            <button type="button" class="logout-btn" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout Safely</span>
            </button>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">Dashboard Overview</h3>
                    <small class="text-muted">Welcome back, <?php echo htmlspecialchars($admin['username']); ?>!</small>
                </div>
                <div class="text-muted">
                    <i class="fas fa-calendar-alt me-2"></i><?php echo date('l, F j, Y'); ?>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="stats-number text-primary"><i class="fas fa-users me-2"></i><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stats-label">Total Users</div>
                    <div class="stats-change positive">
                        <i class="fas fa-arrow-up"></i> +<?php echo $stats['new_users_today']; ?> today
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-number text-warning"><i class="fas fa-calendar-check me-2"></i><?php echo number_format($stats['total_appointments']); ?></div>
                    <div class="stats-label">Total Appointments</div>
                    <div class="stats-change positive">
                        <i class="fas fa-arrow-up"></i> +<?php echo $stats['appointments_today']; ?> today
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="stats-number text-danger"><i class="fas fa-shopping-cart me-2"></i><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stats-label">Total Orders</div>
                    <div class="stats-change positive">
                        <i class="fas fa-arrow-up"></i> +<?php echo $stats['orders_today']; ?> today
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="stats-number text-info"><i class="fas fa-rupee-sign me-2"></i><?php echo formatCurrency($stats['total_revenue']); ?></div>
                    <div class="stats-label">Total Revenue</div>
                    <div class="stats-change positive">
                        <i class="fas fa-arrow-up"></i> <?php echo formatCurrency($stats['revenue_today']); ?> today
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="row">
            <!-- Revenue Chart -->
            <div class="col-md-6">
                <div class="card" data-aos="fade-right" data-aos-delay="500">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i> Revenue Trends (Last 6 Months)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- System Status -->
            <div class="col-md-6">
                <div class="card" data-aos="fade-left" data-aos-delay="600">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> System Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded" style="background: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107;">
                            <div>
                                <span class="fw-bold"><i class="fas fa-calendar-clock me-2 text-warning"></i>Pending Appointments</span>
                                <small class="d-block text-muted mt-1">Awaiting confirmation</small>
                            </div>
                            <span class="badge bg-warning fs-4 px-4 py-3 fw-bold"><?php echo $stats['pending_appointments']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded" style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545;">
                            <div>
                                <span class="fw-bold"><i class="fas fa-shopping-bag me-2 text-danger"></i>Pending Orders</span>
                                <small class="d-block text-muted mt-1">Need processing</small>
                            </div>
                            <span class="badge bg-danger fs-4 px-4 py-3 fw-bold"><?php echo $stats['pending_orders']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded" style="background: rgba(44, 110, 73, 0.1); border-left: 4px solid var(--primary-color);">
                            <div>
                                <span class="fw-bold"><i class="fas fa-box me-2 text-success"></i>Active Products</span>
                                <small class="d-block text-muted mt-1">Available for sale</small>
                            </div>
                            <span class="badge fs-4 px-4 py-3 fw-bold" style="background: var(--primary-color);"><?php echo $stats['active_products']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background: rgba(255, 107, 107, 0.1); border-left: 4px solid #ff6b6b;">
                            <div>
                                <span class="fw-bold"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Low Stock Items</span>
                                <small class="d-block text-muted mt-1">Require restocking</small>
                            </div>
                            <span class="badge bg-warning fs-4 px-4 py-3 fw-bold"><?php echo $stats['low_stock_products']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <!-- Recent Appointments -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-calendar-alt"></i> Recent Appointments</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($booking = $recentBookings->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['service']); ?></td>
                                        <td><?php echo formatDate($booking['preferred_date']); ?></td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($booking['status']); ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-shopping-cart"></i> Recent Orders</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($order = $recentOrders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_number']; ?></td>
                                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger" id="logoutModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-sign-out-alt fa-3x text-warning"></i>
                    </div>
                    <h6 class="mb-3">Are you sure you want to logout?</h6>
                    <p class="text-muted mb-0">You will be redirected to the login page and your current session will be terminated.</p>
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Session started: <strong><?php echo date('M j, Y g:i A'); ?></strong><br>
                            Logged in as: <strong><?php echo htmlspecialchars($admin['full_name']); ?></strong>
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="performLogout()">
                        <i class="fas fa-sign-out-alt"></i> Yes, Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
        
        const labels = monthlyData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });
        
        const data = monthlyData.map(item => parseFloat(item.revenue));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: data,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Auto refresh stats every 30 seconds
        setInterval(() => {
            // You can implement AJAX refresh here
        }, 30000);
        
        // Enhanced Logout Functionality
        function confirmLogout() {
            // Show the logout modal
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        }
        
        function performLogout() {
            // Show loading state
            const logoutBtn = document.querySelector('#logoutModal .btn-danger');
            const originalText = logoutBtn.innerHTML;
            
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
            logoutBtn.disabled = true;
            
            // Add a small delay for better UX
            setTimeout(() => {
                // Redirect to logout
                window.location.href = '../logout.php';
            }, 1000);
        }
        
        // Keyboard shortcut for logout (Ctrl/Cmd + Shift + L)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'L') {
                e.preventDefault();
                confirmLogout();
            }
        });
        
        // Session timeout warning (optional)
        let sessionTimeout;
        const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes
        
        function resetSessionTimeout() {
            clearTimeout(sessionTimeout);
            sessionTimeout = setTimeout(() => {
                alert('Your session is about to expire. Please save your work.');
                // Auto-logout after another 5 minutes of inactivity
                setTimeout(() => {
                    window.location.href = '../logout.php?reason=timeout';
                }, 5 * 60 * 1000);
            }, SESSION_TIMEOUT);
        }
        
        // Reset timeout on user activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetSessionTimeout, true);
        });
        
        // Initialize session timeout
        resetSessionTimeout();
    </script>
</body>
</html>