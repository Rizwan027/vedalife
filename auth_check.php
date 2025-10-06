<?php
// Authentication check helper file
// Include this file in pages that require user authentication

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin($redirect_to = 'SignUp_LogIn_Form.html') {
    if (!isUserLoggedIn()) {
        // Store the current page URL to redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        header("Location: $redirect_to");
        exit();
    }
}

function redirectIfNotLoggedIn($message = "Please login to access this feature.", $redirect_to = 'SignUp_LogIn_Form.html') {
    if (!isUserLoggedIn()) {
        // You can store a message to show after redirect
        $_SESSION['login_message'] = $message;
        header("Location: $redirect_to");
        exit();
    }
}

// Function to get current user info
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? ''
    ];
}
?>