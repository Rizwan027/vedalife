<?php
// User Profile Dashboard
require_once 'auth_check.php';
requireLogin();

$currentUser = getCurrentUser();
$user_id = $currentUser['id'];

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "vedalife";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user details
$userQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc();

// Get user statistics
$statsQuery = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM booking WHERE user_id = ?) as total_bookings,
        (SELECT COUNT(*) FROM booking WHERE user_id = ? AND status = 'pending') as pending_bookings,
        (SELECT COUNT(*) FROM booking WHERE user_id = ? AND status = 'completed') as completed_bookings,
        (SELECT COUNT(*) FROM orders WHERE user_id = ?) as total_orders,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = ?) as total_spent
");
$statsQuery->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();

// Get recent bookings
$recentBookingsQuery = $conn->prepare("
    SELECT * FROM booking 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 3
");
$recentBookingsQuery->bind_param("i", $user_id);
$recentBookingsQuery->execute();
$recentBookings = $recentBookingsQuery->get_result();

// Get recent orders (we'll simulate this for now since orders table might be empty)
$recentOrdersQuery = $conn->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 3
");
$recentOrdersQuery->bind_param("i", $user_id);
$recentOrdersQuery->execute();
$recentOrders = $recentOrdersQuery->get_result();

// Get upcoming appointments
$upcomingQuery = $conn->prepare("
    SELECT * FROM booking 
    WHERE user_id = ? AND preferred_date >= CURDATE() AND status != 'cancelled'
    ORDER BY preferred_date ASC 
    LIMIT 2
");
$upcomingQuery->bind_param("i", $user_id);
$upcomingQuery->execute();
$upcomingBookings = $upcomingQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=3.0, user-scalable=yes">
    <title>My Profile - VedaLife</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="navbarstylemain.css">
    
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
            --shadow-soft: 0 10px 30px rgba(44, 110, 73, 0.08);
            --shadow-medium: 0 20px 40px rgba(44, 110, 73, 0.12);
            --shadow-large: 0 30px 60px rgba(44, 110, 73, 0.15);
            --gradient-primary: linear-gradient(135deg, #2c6e49 0%, #4c956c 100%);
            --gradient-secondary: linear-gradient(135deg, #ffc145 0%, #ffb347 100%);
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 20px;
            font-size: 100%;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            -webkit-text-size-adjust: 100%;
            -moz-text-size-adjust: 100%;
            text-size-adjust: 100%;
            font-size: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--background-light);
            overflow-x: hidden;
            font-size: 1rem;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Enhanced Navbar */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 9%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition-smooth);
        }
        
        .header.scrolled {
            padding: 0.8rem 9%;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .header .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-dark);
            font-family: 'Cormorant Garamond', serif;
            letter-spacing: 1px;
            text-decoration: none;
        }
        
        .header .navbar {
            display: flex;
            align-items: center;
        }
        
        .header .navbar a {
            position: relative;
            font-size: 0.875rem;
            margin: 0 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition-smooth);
        }
        
        .header .navbar a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 0;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 2px;
            transition: var(--transition-smooth);
        }
        
        .header .navbar a:hover::after,
        .header .navbar a.active::after {
            width: 100%;
        }
        
        .header .navbar a:hover,
        .header .navbar a.active {
            color: var(--primary-color);
        }
        
        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .nav-buttons .btn {
            padding: 0.7rem 1.5rem;
            font-size: 0.85rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition-smooth);
            border: 2px solid transparent;
            text-decoration: none;
        }
        
        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent-color);
            color: white;
            font-size: 0.75rem;
            font-weight: bold;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            min-width: 20px;
            text-align: center;
            line-height: 1;
        }
        
        .profile-header {
            background: var(--gradient-primary);
            color: white;
            padding: 4rem 0 3rem;
            margin-top: 80px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.05"><circle cx="30" cy="30" r="2"/></g></g></svg>');
            opacity: 0.3;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.15);
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 1.5rem;
            position: relative;
            z-index: 2;
            transition: var(--transition-smooth);
            box-shadow: var(--shadow-soft);
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-medium);
        }
        
        
        .section-card {
            background: var(--background-white);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(44, 110, 73, 0.08);
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
        }
        
        .section-card:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 0.8rem;
            font-size: 1.4rem;
            position: relative;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--gradient-secondary);
            border-radius: 2px;
        }
        
        .booking-item, .order-item {
            background: var(--background-light);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.2rem;
            border-left: 4px solid var(--primary-color);
            transition: var(--transition-smooth);
            box-shadow: var(--shadow-soft);
        }
        
        .booking-item:hover, .order-item:hover {
            transform: translateX(8px);
            box-shadow: var(--shadow-medium);
            border-left-color: var(--secondary-color);
        }
        
        /* History Sections */
        .history-container {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 8px;
        }
        
        .history-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .history-container::-webkit-scrollbar-track {
            background: var(--background-light);
            border-radius: 10px;
        }
        
        .history-container::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 10px;
        }
        
        .history-container::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }
        
        .history-item {
            background: var(--background-white);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(44, 110, 73, 0.1);
            border-left: 4px solid var(--primary-color);
            transition: var(--transition-smooth);
            position: relative;
        }
        
        .history-item:hover {
            border-left-color: var(--secondary-color);
            box-shadow: var(--shadow-soft);
            transform: translateY(-2px);
        }
        
        .history-item:last-child {
            margin-bottom: 0;
        }
        
        .history-item h6 {
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        .history-item .status-badge {
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
        }
        
        .history-item small {
            margin-bottom: 0.3rem;
        }
        
        .history-item small i {
            width: 16px;
            color: var(--primary-light);
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; border: 1px solid #74b9ff; }
        .status-completed { background: #d4edda; color: #155724; border: 1px solid #00b894; }
        .status-cancelled { background: #f8d7da; color: #721c24; border: 1px solid #e17055; }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .action-btn {
            padding: 1.5rem 1rem;
            border-radius: 15px;
            text-decoration: none;
            color: white;
            background: var(--gradient-primary);
            text-align: center;
            transition: var(--transition-smooth);
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .action-btn:hover {
            transform: translateY(-5px) scale(1.02);
            color: white;
            text-decoration: none;
            box-shadow: var(--shadow-medium);
        }
        
        .action-btn:hover::before {
            left: 100%;
        }
        
        .profile-nav {
            background: var(--background-white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(44, 110, 73, 0.08);
        }
        
        .profile-nav a {
            color: var(--text-light);
            text-decoration: none;
            padding: 0.8rem 1.2rem;
            border-radius: 25px;
            margin-right: 0.8rem;
            margin-bottom: 0.5rem;
            display: inline-block;
            transition: var(--transition-smooth);
            font-weight: 500;
            border: 1px solid transparent;
        }
        
        .profile-nav a:hover, .profile-nav a.active {
            background: var(--gradient-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 1rem 5%;
            }
            
            .header .navbar {
                display: none;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .profile-header {
                margin-top: 70px;
                padding: 3rem 0 2rem;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
            
            
            .section-card {
                padding: 1.5rem;
            }
            
            .profile-nav {
                padding: 1rem;
                text-align: center;
            }
            
            .profile-nav a {
                display: inline-block;
                margin: 0.3rem;
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <!-- Enhanced Navbar -->
    <header class="header" id="navbar">
        <a href="index.php" class="logo">🌿VEDAMRUT</a>
        
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="services.php">Services</a>
            <a href="products.php">Products</a>
            <a href="index.php#pricing">Packages</a>
            <a href="index.php#testimonials">Testimonials</a>
            <a href="index.php#contact">Contact</a>
        </nav>

        <div class="nav-buttons">
            <a href="profile.php" class="btn"><i class="fa-solid fa-user"></i></a>
            <a href="cart.php" class="btn position-relative">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-badge" id="cartCountBadge">0</span>
            </a>
            <a href="logout.php" class="btn btn-danger"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </header>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="profile-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h2>Welcome back, <?php echo htmlspecialchars($userData['username']); ?>!</h2>
                    <p class="mb-0">Member since <?php echo date('F Y', strtotime($userData['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <!-- Profile Navigation -->
        <div class="profile-nav">
            <a href="profile.php" class="active"><i class="fa-solid fa-home"></i> Dashboard</a>
            <a href="appointment.php"><i class="fa-solid fa-calendar-plus"></i> Book Appointment</a>
            <a href="products.php"><i class="fa-solid fa-shopping-cart"></i> Shop Products</a>
        </div>


        <!-- Quick Actions -->
        <div class="section-card">
            <h4 class="section-title"><i class="fa-solid fa-zap"></i> Quick Actions</h4>
            <div class="quick-actions">
                <a href="appointment.php" class="action-btn">
                    <i class="fa-solid fa-calendar-plus mb-2"></i><br>
                    Book Appointment
                </a>
                <a href="products.php" class="action-btn">
                    <i class="fa-solid fa-shopping-cart mb-2"></i><br>
                    Shop Products
                </a>
            </div>
        </div>

        <!-- Complete Appointment History -->
        <div class="section-card">
            <h4 class="section-title"><i class="fa-solid fa-calendar-check"></i> Appointment History</h4>
            <?php 
            // Get all appointments for the user
            $allBookingsQuery = $conn->prepare("
                SELECT * FROM booking 
                WHERE user_id = ? 
                ORDER BY preferred_date DESC, created_at DESC
            ");
            $allBookingsQuery->bind_param("i", $user_id);
            $allBookingsQuery->execute();
            $allBookings = $allBookingsQuery->get_result();
            ?>
            
            <?php if ($allBookings->num_rows > 0): ?>
                <div class="history-container">
                    <?php while ($booking = $allBookings->fetch_assoc()): ?>
                        <div class="history-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-3"><?php echo htmlspecialchars($booking['service']); ?></h6>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">
                                                <i class="fa-solid fa-calendar"></i> 
                                                <strong>Date:</strong> <?php echo date('M d, Y', strtotime($booking['preferred_date'])); ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fa-solid fa-clock"></i> 
                                                <strong>Time:</strong> <?php echo htmlspecialchars($booking['preferred_time']); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">
                                                <i class="fa-solid fa-user"></i> 
                                                <strong>Name:</strong> <?php echo htmlspecialchars($booking['name']); ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fa-solid fa-phone"></i> 
                                                <strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php if (!empty($booking['notes'])): ?>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fa-solid fa-note-sticky"></i> 
                                            <strong>Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted ms-3">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                    Booked: <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Appointments Yet</h5>
                    <p class="text-muted mb-4">Book your first Ayurvedic consultation to begin your wellness journey.</p>
                    <a href="appointment.php" class="btn btn-success">Book Your First Appointment</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Complete Order History -->
        <div class="section-card">
            <h4 class="section-title"><i class="fa-solid fa-shopping-bag"></i> Order History</h4>
            <?php 
            // Get all orders for the user
            $allOrdersQuery = $conn->prepare("
                SELECT * FROM orders 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $allOrdersQuery->bind_param("i", $user_id);
            $allOrdersQuery->execute();
            $allOrders = $allOrdersQuery->get_result();
            ?>
            
            <?php if ($allOrders->num_rows > 0): ?>
                <div class="history-container">
                    <?php while ($order = $allOrders->fetch_assoc()): ?>
                        <div class="history-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-3">Order #<?php echo $order['order_number']; ?></h6>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">
                                                <i class="fa-solid fa-calendar"></i> 
                                                <strong>Order Date:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                            </small>
                                            <small class="text-success d-block fw-bold">
                                                <i class="fa-solid fa-rupee-sign"></i> 
                                                <strong>Total:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if (isset($order['delivery_address'])): ?>
                                                <small class="text-muted d-block">
                                                    <i class="fa-solid fa-location-dot"></i> 
                                                    <strong>Address:</strong> <?php echo htmlspecialchars(substr($order['delivery_address'], 0, 30)) . '...'; ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (isset($order['payment_method'])): ?>
                                                <small class="text-muted d-block">
                                                    <i class="fa-solid fa-credit-card"></i> 
                                                    <strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted ms-3">
                                    <?php echo date('g:i A', strtotime($order['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-shopping-basket fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Orders Yet</h5>
                    <p class="text-muted mb-4">Explore our premium Ayurvedic products to start your wellness journey.</p>
                    <a href="products.php" class="btn btn-success">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Enhanced Footer -->
    <footer class="footer" style="background: var(--gradient-primary); color: white; padding: 4rem 0 2rem; margin-top: 4rem; position: relative; overflow: hidden;">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h3>🌿VEDAMRUT</h3>
                    <p class="mb-4">Transform your life with authentic Ayurvedic healing. Experience ancient wisdom through our premium treatments and natural products.</p>
                    
                    <div class="d-flex">
                        <a href="#" class="social-link" style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth); margin-right: 1rem;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth); margin-right: 1rem;"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth); margin-right: 1rem;"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link" style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth); margin-right: 1rem;"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Quick Links</h5>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.8rem;"><a href="index.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Home</a></li>
                        <li style="margin-bottom: 0.8rem;"><a href="services.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Services</a></li>
                        <li style="margin-bottom: 0.8rem;"><a href="products.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Products</a></li>
                        <li style="margin-bottom: 0.8rem;"><a href="index.php#contact" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Our Services</h5>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.8rem;"><a href="services.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Ayurvedic Consultation</a></li>
                        <li style="margin-bottom: 0.8rem;"><a href="services.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Therapeutic Treatments</a></li>
                        <li style="margin-bottom: 0.8rem;"><a href="services.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Wellness Programs</a></li>
                        <li style="margin-bottom: 0.8rem;"><a href="products.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Natural Products</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Contact Info</h5>
                    <div style="color: rgba(255, 255, 255, 0.8);">
                        <div style="margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 1rem;">
                            <i class="fas fa-map-marker-alt" style="color: var(--primary-light); margin-top: 0.2rem;"></i>
                            <div>
                                <strong>Address:</strong><br>
                                123 Wellness Lane<br>
                                Ayurveda District, Health City
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem;">
                            <i class="fas fa-envelope" style="color: var(--primary-light);"></i>
                            <div>
                                <strong>Email:</strong><br>
                                <a href="mailto:info@vedamrut.com" style="color: var(--secondary-color); text-decoration: none;">info@vedamrut.com</a>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <i class="fas fa-phone" style="color: var(--primary-light);"></i>
                            <div>
                                <strong>Phone:</strong><br>
                                <a href="tel:+917382947582" style="color: var(--secondary-color); text-decoration: none;">+91 73829 47582</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 3rem 0 2rem;">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p style="margin: 0; color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">&copy; 2024 VEDAMRUT. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <p style="margin: 0; color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">Designed with ❤️ for holistic wellness</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Cart management functions
        function getCart() {
            return JSON.parse(localStorage.getItem('vedalife_cart')) || [];
        }

        function getCartItemCount() {
            const cart = getCart();
            return cart.reduce((total, item) => total + item.quantity, 0);
        }

        function updateCartCounter() {
            const cartCountBadge = document.getElementById('cartCountBadge');
            const itemCount = getCartItemCount();
            if (cartCountBadge) {
                cartCountBadge.textContent = itemCount;
                
                // Hide badge if cart is empty
                if (itemCount === 0) {
                    cartCountBadge.style.display = 'none';
                } else {
                    cartCountBadge.style.display = 'flex';
                }
            }
        }

        // Initialize cart counter on page load
        document.addEventListener('DOMContentLoaded', updateCartCounter);
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth hover effects for social links
        document.querySelectorAll('.social-link').forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.05)';
                this.style.boxShadow = 'var(--shadow-medium)';
            });
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = 'none';
            });
        });
        
        // Auto-refresh statistics every 5 minutes
        setInterval(() => {
            // Could add AJAX call to refresh stats
            console.log('Stats refresh available - implement AJAX call here');
        }, 300000);
    </script>
</body>
</html>

<?php
$conn->close();
?>