<?php
session_start();
require_once 'connection.php';

$error_message = '';
$success_message = '';
$valid_token = false;
$token = '';

// Get token from URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    // Validate token
    $query = "SELECT email, expires_at FROM password_reset_tokens 
             WHERE token = ? AND used = 0 AND expires_at > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $valid_token = true;
        $reset_data = $result->fetch_assoc();
    } else {
        $error_message = 'Invalid or expired reset link. Please request a new password reset.';
    }
} else {
    $error_message = 'No reset token provided.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (strlen($new_password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update user password
        $update_query = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ss", $hashed_password, $reset_data['email']);
        
        if ($stmt->execute()) {
            // Mark token as used
            $mark_used_query = "UPDATE password_reset_tokens SET used = 1 WHERE token = ?";
            $stmt = $conn->prepare($mark_used_query);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            $success_message = 'Your password has been successfully updated!';
            $valid_token = false; // Hide form
        } else {
            $error_message = 'Failed to update password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - VedaLife</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .reset-container {
            background: #ffffff;
            width: 100%;
            max-width: 480px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(102, 187, 106, 0.2);
            overflow: hidden;
            border: 1px solid rgba(102, 187, 106, 0.1);
        }
        
        .reset-header {
            background: linear-gradient(135deg, #66bb6a 0%, #43a047 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .reset-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.05"><circle cx="30" cy="30" r="2"/></g></g></svg>');
            opacity: 0.3;
        }
        
        .reset-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        
        .reset-header p {
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .reset-body {
            padding: 40px 30px;
            background: linear-gradient(180deg, #fafffa 0%, #ffffff 100%);
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .error {
            background: #fee;
            color: #c53030;
            border: 1px solid #fed7d7;
        }
        
        .success {
            background: #f0fff4;
            color: #22543d;
            border: 1px solid #c6f6d5;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2e3d2e;
            font-weight: 500;
            font-size: 15px;
        }
        
        .input-box {
            position: relative;
        }
        
        .input-box input {
            width: 100%;
            padding: 16px 50px 16px 20px;
            border: 2px solid #e8f5e8;
            border-radius: 15px;
            font-size: 16px;
            color: #2e3d2e;
            background: #fafbfa;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        .input-box input:focus {
            outline: none;
            border-color: #66bb6a;
            background: white;
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(102, 187, 106, 0.15), 0 0 0 4px rgba(102, 187, 106, 0.1);
        }
        
        .input-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #66bb6a;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .input-box i:hover {
            color: #43a047;
            transform: translateY(-50%) scale(1.1);
        }
        
        .password-strength {
            margin-top: 10px;
            font-size: 14px;
        }
        
        .strength-weak { color: #e53e3e; }
        .strength-medium { color: #d69e2e; }
        .strength-strong { color: #38a169; }
        
        .reset-btn {
            width: 100%;
            background: linear-gradient(135deg, #66bb6a 0%, #43a047 100%);
            color: white;
            border: none;
            padding: 18px 32px;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(102, 187, 106, 0.3);
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .reset-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(102, 187, 106, 0.4);
        }
        
        .reset-btn:hover::before {
            left: 100%;
        }
        
        .reset-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(102, 187, 106, 0.3);
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-to-login a {
            color: #43a047;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-to-login a:hover {
            color: #66bb6a;
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .reset-container {
                margin: 10px;
                border-radius: 20px;
            }
            
            .reset-header {
                padding: 25px 20px;
            }
            
            .reset-header h1 {
                font-size: 24px;
            }
            
            .reset-body {
                padding: 30px 20px;
            }
            
            .input-box input {
                padding: 14px 45px 14px 18px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>🌿 Reset Password</h1>
            <p>Create a new secure password for your VedaLife account</p>
        </div>
        
        <div class="reset-body">
            <?php if ($error_message): ?>
                <div class="message error">
                    <i class='bx bx-error-circle'></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="message success">
                    <i class='bx bx-check-circle'></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($valid_token): ?>
                <form method="POST" id="resetForm">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="input-box">
                            <input type="password" id="password" name="password" 
                                   placeholder="Enter new password" required minlength="6">
                            <i class='bx bx-hide' id="togglePassword" onclick="togglePasswordVisibility('password', this)"></i>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-box">
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm new password" required>
                            <i class='bx bx-hide' id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_password', this)"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="reset-btn">
                        <i class='bx bx-check'></i> Update Password
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="back-to-login">
                <a href="SignUp_LogIn_Form.html">
                    <i class='bx bx-arrow-back'></i> Back to Login
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Password visibility toggle
        function togglePasswordVisibility(inputId, toggleIcon) {
            const input = document.getElementById(inputId);
            const isPassword = input.type === 'password';
            
            input.type = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('bx-hide');
            toggleIcon.classList.toggle('bx-show');
        }
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            let strengthText = '';
            let strengthClass = '';
            
            if (strength < 2) {
                strengthText = 'Weak password';
                strengthClass = 'strength-weak';
            } else if (strength < 4) {
                strengthText = 'Medium strength';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Strong password';
                strengthClass = 'strength-strong';
            }
            
            strengthDiv.innerHTML = `<span class="${strengthClass}">${strengthText}</span>`;
        });
        
        // Form validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return;
            }
        });
        
        // Auto-redirect to login after successful password reset
        <?php if ($success_message): ?>
        setTimeout(function() {
            window.location.href = 'SignUp_LogIn_Form.html?reset=success';
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>