<?php
session_start();

// Check if it's an admin logout for logging purposes
if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    // Log admin logout activity
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "vedalife";
    
    $conn = new mysqli($host, $user, $pass, $db);
    
    if (!$conn->connect_error) {
        $logStmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $admin_id = $_SESSION['admin_id'];
        $action = 'logout';
        $description = 'Admin logged out';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $logStmt->bind_param("issss", $admin_id, $action, $description, $ip, $user_agent);
        $logStmt->execute();
        $conn->close();
    }
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: index.php");
exit;
?>
