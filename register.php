<?php
// Include database connection and email functions
require_once 'connection.php';
require_once 'email_config.php';

// Run only when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px; text-align: center;">';
        echo '⚠️ Please fill in all fields!';
        echo '</div>';
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px; text-align: center;">';
        echo '⚠️ Please enter a valid email address!';
        echo '</div>';
        exit;
    }

    // Check if email already exists
    $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px; text-align: center;">';
        echo '❌ Email already exists! Please use a different email or <a href="SignUp_LogIn_Form.html">login here</a>.';
        echo '</div>';
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $sql = "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        // Registration successful - now send welcome email
        $email_sent = sendWelcomeEmail($email, $username);
        
        echo '<div style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9); padding: 40px; margin: 20px; border-radius: 15px; text-align: center; box-shadow: 0 8px 25px rgba(102, 187, 106, 0.2);">';
        echo '<h2 style="color: #43a047; margin: 0 0 20px; font-size: 28px;">🎉 Welcome to VedaLife Family!</h2>';
        echo '<p style="color: #2e3d2e; font-size: 18px; margin-bottom: 20px;"><strong>Registration Successful!</strong></p>';
        
        if ($email_sent) {
            echo '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin: 20px 0; border: 1px solid #c3e6cb;">';
            echo '📧 <strong>Welcome email sent successfully!</strong><br>';
            echo 'Check your inbox at <strong>' . htmlspecialchars($email) . '</strong> for a special welcome message and exclusive offers!';
            echo '</div>';
        } else {
            echo '<div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 10px; margin: 20px 0; border: 1px solid #ffeaa7;">';
            echo '📧 Account created successfully, but welcome email couldn\'t be sent. Don\'t worry, you can still access your account!';
            echo '</div>';
        }
        
        echo '<div style="margin: 30px 0;">';
        echo '<a href="SignUp_LogIn_Form.html" style="background: linear-gradient(135deg, #66bb6a, #43a047); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 187, 106, 0.3); transition: transform 0.3s ease;">🚀 Login Now</a>';
        echo '</div>';
        
        echo '<p style="color: #666; font-size: 14px; margin-top: 20px;">Thank you for joining VedaLife - Your wellness journey starts now!</p>';
        echo '</div>';
        
    } else {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px; text-align: center;">';
        echo '❌ Registration failed: ' . htmlspecialchars($stmt->error);
        echo '</div>';
    }

    $stmt->close();
}

$conn->close();
?>
