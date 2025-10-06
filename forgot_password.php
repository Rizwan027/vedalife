<?php
session_start();
header('Content-Type: application/json');

// Include database connection and email functions
require_once 'connection.php';
require_once 'email_config.php';

// Rate limiting to prevent spam
$max_requests_per_hour = 5;
$current_hour = date('Y-m-d H:00:00');

try {
    // Only process POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get and validate email
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Please enter a valid email address');
    }
    
    // Check rate limiting
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_limit_query = "SELECT COUNT(*) as request_count 
                        FROM password_reset_tokens 
                        WHERE created_at >= ? 
                        AND created_at < DATE_ADD(?, INTERVAL 1 HOUR)";
                        
    $stmt = $conn->prepare($rate_limit_query);
    $stmt->bind_param("ss", $current_hour, $current_hour);
    $stmt->execute();
    $rate_result = $stmt->get_result()->fetch_assoc();
    
    if ($rate_result['request_count'] >= $max_requests_per_hour) {
        throw new Exception('Too many password reset requests. Please try again later.');
    }
    
    // Check if email exists in users table
    $user_query = "SELECT id, email, username FROM users WHERE email = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    if ($user_result->num_rows === 0) {
        // Don't reveal if email exists or not for security
        // But still return success to prevent email enumeration
        echo json_encode([
            'success' => true,
            'message' => 'If this email exists in our system, a reset link has been sent.'
        ]);
        exit;
    }
    
    $user = $user_result->fetch_assoc();
    
    // Generate secure random token
    $token = bin2hex(random_bytes(32)); // 64 character hex string
    
    // Set expiration time (24 hours from now)
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Clean up old/expired tokens for this email
    $cleanup_query = "DELETE FROM password_reset_tokens 
                     WHERE email = ? 
                     OR expires_at < NOW()";
    $stmt = $conn->prepare($cleanup_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    // Insert new reset token
    $insert_query = "INSERT INTO password_reset_tokens (email, token, expires_at) 
                    VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sss", $email, $token, $expires_at);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to generate reset token. Please try again.');
    }
    
    // Send reset email
    $email_sent = sendPasswordResetEmail($email, $token);
    
    if (!$email_sent) {
        // Log the error but don't reveal it to user
        error_log("Failed to send password reset email to: " . $email);
        
        // Remove the token since email failed
        $delete_query = "DELETE FROM password_reset_tokens WHERE token = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        throw new Exception('Unable to send reset email. Please try again later.');
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Password reset link has been sent to your email address. Please check your inbox and spam folder.'
    ]);
    
    // Log successful reset request
    error_log("Password reset requested for email: " . $email . " from IP: " . $ip_address);
    
} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log error
    error_log("Password reset error: " . $e->getMessage() . " for email: " . ($email ?? 'unknown'));
    
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>
