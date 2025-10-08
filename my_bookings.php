<?php
// My Bookings Page
// Prevent browser caching to ensure fresh user data
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'auth_check.php';
requireLogin();
require_once __DIR__ . '/config/connection.php';

$currentUser = getCurrentUser();
$user_id = $currentUser['id'];

// Debug: Add session validation to ensure we have the correct user
if (empty($user_id) || !is_numeric($user_id)) {
    // Log potential session issue
    error_log("VedaLife - Invalid user_id in my_bookings.php: " . print_r($currentUser, true));
    // Redirect to login to refresh session
    header("Location: SignUp_LogIn_Form.html?error=session_expired");
    exit();
}

// Handle refresh request to get fresh user data
if (isset($_GET['refresh']) && $_GET['refresh'] === '1') {
    // Refresh user data from database
    $refreshedUser = refreshCurrentUser();
    if ($refreshedUser) {
        $currentUser = $refreshedUser;
        $user_id = $currentUser['id'];
    }
    // Redirect to clean URL after refresh
    header("Location: my_bookings.php");
    exit();
}

// DB connection is provided by config/connection.php as $conn

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query based on filters
$whereConditions = ["user_id = ?"];
$params = [$user_id];
$paramTypes = "i";

if ($status_filter !== 'all') {
    $whereConditions[] = "status = ?";
    $params[] = $status_filter;
    $paramTypes .= "s";
}

if ($date_filter !== 'all') {
    switch ($date_filter) {
        case 'upcoming':
            $whereConditions[] = "preferred_date >= CURDATE()";
            break;
        case 'past':
            $whereConditions[] = "preferred_date < CURDATE()";
            break;
        case 'this_month':
            $whereConditions[] = "MONTH(preferred_date) = MONTH(CURDATE()) AND YEAR(preferred_date) = YEAR(CURDATE())";
            break;
    }
}

if (!empty($search)) {
    $whereConditions[] = "(service LIKE ? OR notes LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $paramTypes .= "ss";
}

$whereClause = implode(" AND ", $whereConditions);

// Get bookings with pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$bookingsQuery = $conn->prepare("
    SELECT * FROM booking 
    WHERE $whereClause 
    ORDER BY preferred_date DESC, created_at DESC 
    LIMIT $limit OFFSET $offset
");

if (!empty($params)) {
    $bookingsQuery->bind_param($paramTypes, ...$params);
}

$bookingsQuery->execute();
$bookings = $bookingsQuery->get_result();

// Get total count for pagination
$countQuery = $conn->prepare("SELECT COUNT(*) as total FROM booking WHERE $whereClause");
if (!empty($params)) {
    $countQuery->bind_param($paramTypes, ...$params);
}
$countQuery->execute();
$totalBookings = $countQuery->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalBookings / $limit);

// Get booking statistics
$statsQuery = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN preferred_date >= CURDATE() AND status != 'cancelled' THEN 1 ELSE 0 END) as upcoming
    FROM booking WHERE user_id = ?
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
    <title>My Bookings - VedaLife</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="navbarstylemain.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
            padding-top: 70px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .profile-nav {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .profile-nav a {
            color: #6c757d;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .profile-nav a:hover, .profile-nav a.active {
            background: #28a745;
            color: white;
        }
        
        .stats-row {
            margin-bottom: 2rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #28a745;
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .booking-card:hover {
            transform: translateY(-3px);
        }
        
        .booking-header {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        
        .service-title {
            color: #28a745;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .booking-date {
            font-size: 1.1rem;
            color: #28a745;
            font-weight: bold;
        }
        
        .booking-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .booking-notes {
            background: #e8f5e8;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border-left: 4px solid #28a745;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .stats-row {
                text-align: center;
            }
            
            .booking-card {
                margin-bottom: 1rem;
            }
            
            .filter-card .row {
                text-align: center;
            }
            
            .filter-card .col-md-3 {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <header class="header">
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2><i class="fa-solid fa-calendar-check"></i> My Bookings - <?php echo htmlspecialchars($currentUser['username']); ?></h2>
                    <p class="mb-0">Manage and track all your appointments</p>
                    <!-- Debug: Show current user info -->
                    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                        <div class="alert alert-info mt-2">
                            <small><strong>Debug Info:</strong> User ID: <?php echo $user_id; ?> | Username: <?php echo htmlspecialchars($currentUser['username']); ?> | Session ID: <?php echo session_id(); ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Profile Navigation -->
        <div class="profile-nav">
            <a href="profile.php"><i class="fa-solid fa-dashboard"></i> Dashboard</a>
            <a href="my_bookings.php" class="active"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
            <a href="my_orders.php"><i class="fa-solid fa-shopping-bag"></i> My Orders</a>
            <a href="edit_profile.php"><i class="fa-solid fa-user-edit"></i> Edit Profile</a>
            <a href="change_password.php"><i class="fa-solid fa-key"></i> Change Password</a>
            <a href="my_bookings.php?refresh=1" class="text-muted" style="font-size: 0.8rem;"><i class="fa-solid fa-refresh"></i> Refresh Data</a>
        </div>

        <!-- Statistics -->
        <div class="row stats-row">
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total']; ?></div>
                    <div class="stats-label">Total</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['upcoming']; ?></div>
                    <div class="stats-label">Upcoming</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['pending']; ?></div>
                    <div class="stats-label">Pending</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['confirmed']; ?></div>
                    <div class="stats-label">Confirmed</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['completed']; ?></div>
                    <div class="stats-label">Completed</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['cancelled']; ?></div>
                    <div class="stats-label">Cancelled</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="row align-items-end">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Date</label>
                    <select name="date" id="date" class="form-select">
                        <option value="all" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Dates</option>
                        <option value="upcoming" <?php echo $date_filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="past" <?php echo $date_filter === 'past' ? 'selected' : ''; ?>>Past</option>
                        <option value="this_month" <?php echo $date_filter === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Search by service or notes..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa-solid fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Bookings List -->
        <?php if ($bookings->num_rows > 0): ?>
            <?php while ($booking = $bookings->fetch_assoc()): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="service-title"><?php echo htmlspecialchars($booking['service']); ?></div>
                                <div class="booking-date">
                                    <i class="fa-solid fa-calendar"></i> 
                                    <?php echo date('l, F d, Y', strtotime($booking['preferred_date'])); ?>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <i class="fa-solid fa-circle-dot"></i> <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="booking-info">
                                <div class="row">
                                    <div class="col-6">
                                        <strong><i class="fa-solid fa-user"></i> Name:</strong><br>
                                        <?php echo htmlspecialchars($booking['name']); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong><i class="fa-solid fa-envelope"></i> Email:</strong><br>
                                        <?php echo htmlspecialchars($booking['email']); ?>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <strong><i class="fa-solid fa-phone"></i> Phone:</strong><br>
                                        <?php echo htmlspecialchars($booking['phone']); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong><i class="fa-solid fa-calendar-plus"></i> Booked:</strong><br>
                                        <?php echo date('M d, Y g:i A', strtotime($booking['created_at'])); ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($booking['notes'])): ?>
                                <div class="booking-notes">
                                    <strong><i class="fa-solid fa-note-sticky"></i> Notes:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($booking['notes'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <?php
                                $isUpcoming = strtotime($booking['preferred_date']) >= strtotime(date('Y-m-d'));
                                $isPending = $booking['status'] === 'pending';
                                ?>
                                
                                <?php if ($isUpcoming && $isPending): ?>
                                    <div class="alert alert-info">
                                        <i class="fa-solid fa-clock"></i><br>
                                        <strong>Awaiting Confirmation</strong><br>
                                        <small>We'll contact you soon to confirm your appointment</small>
                                    </div>
                                <?php elseif ($isUpcoming && $booking['status'] === 'confirmed'): ?>
                                    <div class="alert alert-success">
                                        <i class="fa-solid fa-check-circle"></i><br>
                                        <strong>Confirmed</strong><br>
                                        <small>Your appointment is confirmed!</small>
                                    </div>
                                <?php elseif ($booking['status'] === 'completed'): ?>
                                    <div class="alert alert-success">
                                        <i class="fa-solid fa-check-double"></i><br>
                                        <strong>Completed</strong><br>
                                        <small>Thank you for visiting us!</small>
                                    </div>
                                <?php elseif ($booking['status'] === 'cancelled'): ?>
                                    <div class="alert alert-danger">
                                        <i class="fa-solid fa-times-circle"></i><br>
                                        <strong>Cancelled</strong><br>
                                        <small>This appointment was cancelled</small>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-2">
                                    <a href="appointment.php" class="btn btn-outline-success btn-sm">
                                        <i class="fa-solid fa-plus"></i> Book Another
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-wrapper">
                    <nav>
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === (int)$page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-calendar-xmark fa-4x text-muted mb-3"></i>
                <h4>No appointments found</h4>
                <p class="text-muted">
                    <?php if (!empty($search) || $status_filter !== 'all' || $date_filter !== 'all'): ?>
                        No appointments match your current filters. Try adjusting your search criteria.
                    <?php else: ?>
                        You haven't booked any appointments yet. Book your first appointment to get started!
                    <?php endif; ?>
                </p>
                
                <?php if (!empty($search) || $status_filter !== 'all' || $date_filter !== 'all'): ?>
                    <a href="my_bookings.php" class="btn btn-outline-secondary me-2">
                        <i class="fa-solid fa-times"></i> Clear Filters
                    </a>
                <?php endif; ?>
                
                <a href="appointment.php" class="btn btn-success">
                    <i class="fa-solid fa-calendar-plus"></i> Book Appointment
                </a>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="text-center my-4">
            <a href="appointment.php" class="btn btn-success btn-lg me-3">
                <i class="fa-solid fa-calendar-plus"></i> Book New Appointment
            </a>
            <a href="profile.php" class="btn btn-outline-success btn-lg">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-success text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2025 VEDALIFE. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
        
        // Auto-submit form when filters change
        document.querySelectorAll('#status, #date').forEach(select => {
            select.addEventListener('change', function() {
                // Optional: Auto-submit on filter change
                // this.form.submit();
            });
        });

        // Clear search functionality
        const searchInput = document.getElementById('search');
        if (searchInput.value) {
            const clearBtn = document.createElement('span');
            clearBtn.innerHTML = '<i class="fa-solid fa-times"></i>';
            clearBtn.className = 'position-absolute top-50 end-0 translate-middle-y me-3 text-muted';
            clearBtn.style.cursor = 'pointer';
            clearBtn.onclick = () => {
                searchInput.value = '';
                searchInput.focus();
            };
            searchInput.parentElement.style.position = 'relative';
            searchInput.parentElement.appendChild(clearBtn);
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>