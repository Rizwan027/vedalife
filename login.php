<?php
session_start(); // start session for login tracking

$host = "localhost";
$user = "root";
$pass = "";
$db   = "vedalife";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // First, check if it's an admin trying to login
    $adminSql = "SELECT * FROM admin_users WHERE username = ? AND is_active = 1";
    $adminStmt = $conn->prepare($adminSql);
    $adminStmt->bind_param("s", $username);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();

    if ($adminResult->num_rows > 0) {
        $adminRow = $adminResult->fetch_assoc();
        
        // Verify admin password
        if (password_verify($password, $adminRow['password'])) {
            // Set admin session
            $_SESSION['admin_id'] = $adminRow['id'];
            $_SESSION['admin_username'] = $adminRow['username'];
            $_SESSION['admin_name'] = $adminRow['full_name'];
            $_SESSION['admin_role'] = $adminRow['role'];
            
            // Update last login
            $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $adminRow['id']);
            $updateStmt->execute();
            
            // Log admin activity
            $logStmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $admin_id = $adminRow['id'];
            $action = 'login';
            $description = 'Admin logged in via user login form';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $logStmt->bind_param("issss", $admin_id, $action, $description, $ip, $user_agent);
            $logStmt->execute();
            
            // Redirect to admin dashboard
            header("Location: admin/index.php");
            exit();
        }
    }
    
    // If not admin or admin login failed, try regular user login
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            // Redirect back to the page user was trying to access
            $redirect_url = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']); // Clear the redirect URL
            
            header("Location: $redirect_url");
            exit();
        } else {
            // Redirect back with error
            header("Location: SignUp_LogIn_Form.html?error=invalid_password");
            exit();
        }
    } else {
        // Check if this username exists in admin table for better error message
        $adminCheckSql = "SELECT username FROM admin_users WHERE username = ?";
        $adminCheckStmt = $conn->prepare($adminCheckSql);
        $adminCheckStmt->bind_param("s", $username);
        $adminCheckStmt->execute();
        $adminCheckResult = $adminCheckStmt->get_result();
        
        if ($adminCheckResult->num_rows > 0) {
            // Admin exists but password was wrong
            header("Location: SignUp_LogIn_Form.html?error=admin_invalid_password");
        } else {
            // User doesn't exist at all
            header("Location: SignUp_LogIn_Form.html?error=user_not_found");
        }
        exit();
    }

    $stmt->close();
    $adminStmt->close();
}

$conn->close();
?>
