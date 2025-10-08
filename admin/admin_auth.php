<?php
// Admin Authentication System for VedaLife
session_start();

// Database connection (robust)
function getAdminDbConnection() {
    // Try legacy admin connection first
    @require_once __DIR__ . '/../config/connection.php';
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        return $conn;
    }

    // Fallback to centralized bootstrap wrapper that returns a mysqli
    @require_once __DIR__ . '/../includes/bootstrap.php';
    if (function_exists('getDbConnection')) {
        $dbc = getDbConnection();
        if ($dbc instanceof mysqli && !$dbc->connect_error) {
            return $dbc;
        }
    }

    // As a last resort, try the simple root connection
    @require_once __DIR__ . '/../connection.php';
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        return $conn;
    }

    // Final direct attempt with sane defaults / env overrides
    try {
        $host = getenv('VEDALIFE_DB_HOST') ?: 'localhost';
        $user = getenv('VEDALIFE_DB_USER') ?: 'root';
        $pass = getenv('VEDALIFE_DB_PASS') ?: '';
        $db   = getenv('VEDALIFE_DB_NAME') ?: 'vedalife';
        $port = getenv('VEDALIFE_DB_PORT') ?: null;
        if ($port) {
            $dbc = @new mysqli($host, $user, $pass, $db, (int)$port);
        } else {
            $dbc = @new mysqli($host, $user, $pass, $db);
        }
        if ($dbc instanceof mysqli && !$dbc->connect_error) {
            return $dbc;
        }
    } catch (Throwable $e) {
        // ignore, fall through to exception below
    }

    // If all methods failed, throw a clear error
    throw new Exception('Admin DB connection could not be established.');
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Get current admin info
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $conn = getAdminDbConnection();
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    
    return $admin;
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: admin_login.php");
        exit;
    }
}

// Check admin role permission
function hasPermission($required_role = 'admin') {
    $admin = getCurrentAdmin();
    if (!$admin) return false;
    
    $role_hierarchy = ['manager' => 1, 'admin' => 2, 'super_admin' => 3];
    $user_level = $role_hierarchy[$admin['role']] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

// Log admin activity
function logAdminActivity($action, $description = null) {
    if (!isAdminLoggedIn()) return;
    
    $conn = getAdminDbConnection();
    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt->bind_param("issss", $admin_id, $action, $description, $ip, $user_agent);
    $stmt->execute();
}

// Admin login function
function adminLogin($username, $password) {
    $conn = getAdminDbConnection();
    $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM admin_users WHERE username = ? AND is_active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            
            // Update last login
            $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $admin['id']);
            $updateStmt->execute();
            
            // Log login activity
            logAdminActivity('login', 'Admin logged in successfully');
            
            return true;
        }
    }
    
    return false;
}

// Admin logout function
function adminLogout() {
    if (isAdminLoggedIn()) {
        logAdminActivity('logout', 'Admin logged out');
    }
    
    // Clear all session data
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    header("Location: admin_login.php");
    exit;
}

// Get dashboard statistics
function getDashboardStats() {
    $conn = getAdminDbConnection();
    
    // Get all statistics in one query for better performance
    $query = "
        SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()) as new_users_today,
            (SELECT COUNT(*) FROM booking) as total_appointments,
            (SELECT COUNT(*) FROM booking WHERE status = 'pending') as pending_appointments,
            (SELECT COUNT(*) FROM booking WHERE DATE(created_at) = CURDATE()) as appointments_today,
            (SELECT COUNT(*) FROM orders) as total_orders,
            (SELECT COUNT(*) FROM orders WHERE status = 'pending') as pending_orders,
            (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()) as orders_today,
            (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled') as total_revenue,
            (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = CURDATE()) as revenue_today,
            (SELECT COUNT(*) FROM products WHERE is_active = 1) as active_products,
            (SELECT COUNT(*) FROM products WHERE stock < 10) as low_stock_products
    ";
    
    $result = $conn->query($query);
    $stats = $result->fetch_assoc();
    
    return $stats;
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format((float)$amount, 2);
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Get user role badge class
function getRoleBadgeClass($role) {
    switch($role) {
        case 'super_admin': return 'bg-danger';
        case 'admin': return 'bg-primary';
        case 'manager': return 'bg-info';
        default: return 'bg-secondary';
    }
}

// Get status badge class
function getStatusBadgeClass($status) {
    switch($status) {
        case 'pending': return 'bg-warning text-dark';
        case 'confirmed': return 'bg-info';
        case 'completed': return 'bg-success';
        case 'cancelled': return 'bg-danger';
        case 'delivered': return 'bg-success';
        case 'shipped': return 'bg-primary';
        case 'processing': return 'bg-info';
        default: return 'bg-secondary';
    }
}
?>