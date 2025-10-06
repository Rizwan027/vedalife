<?php
// Email Configuration for VedaLife Password Reset
// Include PHPMailer
require_once 'PHPMailer-6.8.1/src/Exception.php';
require_once 'PHPMailer-6.8.1/src/PHPMailer.php';
require_once 'PHPMailer-6.8.1/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    
    // Email configuration - UPDATE THESE WITH YOUR SETTINGS
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = 'emailservice780@gmail.com'; // Your Gmail
    private $smtp_password = 'zbxr jaog eefp idls';    // Gmail App Password
    private $from_email = 'noreply@vedalife.com';
    private $from_name = 'VedaLife';
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($to_email, $reset_token) {
        $reset_link = $this->getResetLink($reset_token);
        $subject = "Reset Your VedaLife Password";
        $message = $this->getPasswordResetTemplate($reset_link);
        
        // Try to send with SMTP first, fallback to PHP mail
        if ($this->sendWithSMTP($to_email, $subject, $message)) {
            return true;
        } else {
            return $this->sendWithPHPMail($to_email, $subject, $message);
        }
    }
    
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmationEmail($to_email, $order_data) {
        $subject = "Order Confirmation - Order #{$order_data['order_number']}";
        $message = $this->getOrderConfirmationTemplate($order_data);
        
        // Try to send with SMTP first, fallback to PHP mail
        if ($this->sendWithSMTP($to_email, $subject, $message)) {
            return true;
        } else {
            return $this->sendWithPHPMail($to_email, $subject, $message);
        }
    }
    
    /**
     * Generate reset link
     */
    private function getResetLink($token) {
        $base_url = $this->getBaseUrl();
        return $base_url . '/reset_password.php?token=' . urlencode($token);
    }
    
    /**
     * Get base URL
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Password reset email template
     */
    private function getPasswordResetTemplate($reset_link) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Your Password</title>
            <style>
                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    background: #f8f9fa;
                }
                .container {
                    background: white;
                    margin: 20px;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #66bb6a, #43a047);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: 600;
                }
                .content {
                    padding: 40px 30px;
                }
                .reset-btn {
                    display: inline-block;
                    background: linear-gradient(135deg, #66bb6a, #43a047);
                    color: white;
                    text-decoration: none;
                    padding: 15px 30px;
                    border-radius: 25px;
                    font-weight: 600;
                    font-size: 16px;
                    margin: 20px 0;
                    transition: transform 0.3s ease;
                }
                .reset-btn:hover {
                    transform: translateY(-2px);
                }
                .footer {
                    background: #f8f9fa;
                    padding: 20px 30px;
                    text-align: center;
                    color: #666;
                    font-size: 14px;
                }
                .warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    color: #856404;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🌿 VedaLife</h1>
                    <p>Password Reset Request</p>
                </div>
                
                <div class="content">
                    <h2>Reset Your Password</h2>
                    <p>Hello!</p>
                    <p>We received a request to reset your VedaLife account password. Click the button below to set a new password:</p>
                    
                    <center>
                        <a href="' . $reset_link . '" class="reset-btn">Reset My Password</a>
                    </center>
                    
                    <div class="warning">
                        <strong>⚠️ Important:</strong>
                        <ul>
                            <li>This link will expire in 24 hours</li>
                            <li>If you didn\'t request this reset, please ignore this email</li>
                            <li>Never share this link with anyone</li>
                        </ul>
                    </div>
                    
                    <p>If the button doesn\'t work, copy and paste this link into your browser:</p>
                    <p style="word-break: break-all; color: #666; background: #f8f9fa; padding: 10px; border-radius: 5px;">
                        ' . $reset_link . '
                    </p>
                </div>
                
                <div class="footer">
                    <p>This email was sent from VedaLife Password Reset System</p>
                    <p>If you have any questions, please contact our support team.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Order confirmation email template
     */
    private function getOrderConfirmationTemplate($order_data) {
        $items_html = '';
        $subtotal = 0;
        
        foreach ($order_data['items'] as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $subtotal += $item_total;
            $items_html .= '
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;">' . htmlspecialchars($item['name']) . '</td>
                    <td style="padding: 10px; border-bottom: 1px solid #f0f0f0; text-align: center;">₹' . number_format($item['price'], 2) . '</td>
                    <td style="padding: 10px; border-bottom: 1px solid #f0f0f0; text-align: center;">' . $item['quantity'] . '</td>
                    <td style="padding: 10px; border-bottom: 1px solid #f0f0f0; text-align: right; font-weight: 600;">₹' . number_format($item_total, 2) . '</td>
                </tr>';
        }
        
        $delivery_charges = $subtotal >= 500 ? 0 : 50;
        $gst = round($subtotal * 0.05);
        $total = $subtotal + $delivery_charges + $gst;
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Order Confirmation</title>
            <style>
                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    background: #f8f9fa;
                }
                .container {
                    background: white;
                    margin: 20px;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #66bb6a, #43a047);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: 600;
                }
                .content {
                    padding: 30px;
                }
                .order-info {
                    background: #f8fff9;
                    padding: 20px;
                    border-radius: 8px;
                    border-left: 4px solid #66bb6a;
                    margin: 20px 0;
                }
                .order-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                .order-table th {
                    background: #f8f9fa;
                    padding: 12px;
                    text-align: left;
                    font-weight: 600;
                    border-bottom: 2px solid #dee2e6;
                }
                .total-row {
                    background: #f8fff9;
                    font-weight: 600;
                }
                .footer {
                    background: #f8f9fa;
                    padding: 20px 30px;
                    text-align: center;
                    color: #666;
                    font-size: 14px;
                }
                .success-badge {
                    background: #d4edda;
                    color: #155724;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 14px;
                    font-weight: 600;
                    display: inline-block;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🌿 VedaLife</h1>
                    <p>Order Confirmation</p>
                </div>
                
                <div class="content">
                    <h2>Thank You for Your Order!</h2>
                    <p>Dear ' . htmlspecialchars($order_data['customer_name']) . ',</p>
                    <p>Your order has been successfully placed. Here are the details:</p>
                    
                    <div class="order-info">
                        <strong>📋 Order Details</strong><br>
                        <strong>Order Number:</strong> ' . htmlspecialchars($order_data['order_number']) . '<br>
                        <strong>Order Date:</strong> ' . date('F j, Y g:i A') . '<br>
                        <strong>Payment Method:</strong> ' . strtoupper($order_data['payment_method']) . '<br>
                        <div class="success-badge">✅ Order Confirmed</div>
                    </div>
                    
                    <h3>📦 Items Ordered</h3>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ' . $items_html . '
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="padding: 10px; text-align: right;"><strong>Subtotal:</strong></td>
                                <td style="padding: 10px; text-align: right; font-weight: 600;">₹' . number_format($subtotal, 2) . '</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding: 10px; text-align: right;"><strong>Delivery Charges:</strong></td>
                                <td style="padding: 10px; text-align: right; font-weight: 600;">' . ($delivery_charges === 0 ? '<span style="color: #28a745;">FREE</span>' : '₹' . number_format($delivery_charges, 2)) . '</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding: 10px; text-align: right;"><strong>GST (5%):</strong></td>
                                <td style="padding: 10px; text-align: right; font-weight: 600;">₹' . number_format($gst, 2) . '</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="3" style="padding: 15px; text-align: right; font-size: 18px;"><strong>Total Amount:</strong></td>
                                <td style="padding: 15px; text-align: right; font-size: 18px; color: #28a745;"><strong>₹' . number_format($total, 2) . '</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="order-info">
                        <strong>🚚 Delivery Information</strong><br>
                        <strong>Delivery Address:</strong><br>
                        ' . nl2br(htmlspecialchars($order_data['shipping_address'])) . '<br><br>
                        <strong>Phone:</strong> ' . htmlspecialchars($order_data['phone']) . '
                    </div>
                    
                    <h3>📋 What\'s Next?</h3>
                    <ul>
                        <li>We\'ll process your order within 1-2 business days</li>
                        <li>You\'ll receive tracking information once shipped</li>
                        <li>Expected delivery: 3-5 business days</li>
                        <li>For COD orders, keep cash ready for delivery</li>
                    </ul>
                </div>
                
                <div class="footer">
                    <p>Thank you for choosing VedaLife for your wellness journey!</p>
                    <p>If you have any questions, please contact our support team.</p>
                    <p><strong>Email:</strong> support@vedalife.com | <strong>Phone:</strong> +91 73829 47582</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Send email using SMTP with PHPMailer
     */
    private function sendWithSMTP($to_email, $subject, $message) {
        try {
            $mail = new PHPMailer(true);
            
            // Enable verbose debug output (remove in production)
            $mail->SMTPDebug = 0; // Set to 2 for debugging
            $mail->Debugoutput = 'error_log';
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtp_username;
            $mail->Password   = $this->smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->smtp_port;
            
            // Recipients
            $mail->setFrom($this->smtp_username, $this->from_name); // Use your Gmail as sender
            $mail->addAddress($to_email);
            $mail->addReplyTo($this->smtp_username, $this->from_name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            
            $mail->send();
            error_log('Email sent successfully to: ' . $to_email);
            return true;
            
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using PHP mail() function (fallback)
     */
    private function sendWithPHPMail($to_email, $subject, $message) {
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        );
        
        return mail($to_email, $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail($to_email, $username) {
    $subject = "Welcome to VedaLife - Your Wellness Journey Begins!";
    $message = $this->getWelcomeEmailTemplate($username);
    
    // Try to send with SMTP first, fallback to PHP mail
    if ($this->sendWithSMTP($to_email, $subject, $message)) {
        return true;
    } else {
        return $this->sendWithPHPMail($to_email, $subject, $message);
    }
    }
    
    /**
     * Beautiful welcome email template
     */
    private function getWelcomeEmailTemplate($username) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome to VedaLife</title>
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 650px;
                margin: 0 auto;
                background: #f0f8f0;
            }
            .container {
                background: white;
                margin: 20px;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 8px 32px rgba(102, 187, 106, 0.2);
            }
            .header {
                background: linear-gradient(135deg, #66bb6a 0%, #43a047 100%);
                color: white;
                padding: 40px 30px;
                text-align: center;
                position: relative;
                overflow: hidden;
            }
            .header::before {
                content: "";
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
                animation: shimmer 3s ease-in-out infinite;
            }
            @keyframes shimmer {
                0%, 100% { transform: rotate(0deg) scale(1); }
                50% { transform: rotate(180deg) scale(1.1); }
            }
            .header h1 {
                margin: 0;
                font-size: 36px;
                font-weight: 700;
                position: relative;
                z-index: 2;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            }
            .header p {
                font-size: 18px;
                margin: 10px 0 0;
                position: relative;
                z-index: 2;
                opacity: 0.95;
            }
            .content {
                padding: 50px 40px;
                background: linear-gradient(180deg, #ffffff 0%, #fafffa 100%);
            }
            .welcome-box {
                background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
                padding: 30px;
                border-radius: 15px;
                border-left: 5px solid #66bb6a;
                margin: 25px 0;
                box-shadow: 0 4px 15px rgba(102, 187, 106, 0.1);
            }
            .welcome-box h2 {
                color: #43a047;
                margin: 0 0 15px;
                font-size: 24px;
            }
            .features {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin: 30px 0;
            }
            .feature {
                background: white;
                padding: 20px;
                border-radius: 12px;
                text-align: center;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                border: 1px solid #e8f5e8;
                transition: transform 0.3s ease;
            }
            .feature:hover {
                transform: translateY(-2px);
            }
            .feature-icon {
                font-size: 32px;
                margin-bottom: 10px;
                display: block;
            }
            .cta-button {
                display: inline-block;
                background: linear-gradient(135deg, #66bb6a, #43a047);
                color: white;
                text-decoration: none;
                padding: 18px 35px;
                border-radius: 30px;
                font-weight: 600;
                font-size: 16px;
                margin: 25px 0;
                text-align: center;
                box-shadow: 0 6px 20px rgba(102, 187, 106, 0.3);
                transition: all 0.3s ease;
            }
            .cta-button:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px rgba(102, 187, 106, 0.4);
            }
            .footer {
                background: linear-gradient(135deg, #2e3d2e, #1a2e1a);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .footer p {
                margin: 8px 0;
                opacity: 0.9;
            }
            .social-links {
                margin: 20px 0;
            }
            .social-links a {
                display: inline-block;
                margin: 0 10px;
                padding: 10px;
                background: rgba(255,255,255,0.1);
                border-radius: 50%;
                color: white;
                text-decoration: none;
                transition: background 0.3s ease;
            }
            .social-links a:hover {
                background: rgba(255,255,255,0.2);
            }
            @media (max-width: 600px) {
                .features {
                    grid-template-columns: 1fr;
                }
                .content {
                    padding: 30px 20px;
                }
                .header {
                    padding: 30px 20px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Welcome to VedaLife!</h1>
                <p>Your journey to holistic wellness begins now</p>
            </div>
            
            <div class="content">
                <div class="welcome-box">
                    <h2>Namaste, ' . htmlspecialchars($username) . '! 🙏</h2>
                    <p><strong>Thank you for joining the VedaLife family!</strong> We are thrilled to have you on board as you embark on your journey toward holistic health and well-being through the ancient wisdom of Ayurveda.</p>
                </div>
                
                <h3 style="color: #43a047; text-align: center; margin: 40px 0 30px;">What Awaits You at VedaLife</h3>
                
                <div class="features">
                    <div class="feature">
                        <span class="feature-icon">🌱</span>
                        <h4>Authentic Ayurvedic Products</h4>
                        <p>Pure, natural remedies crafted with traditional wisdom</p>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">👨‍⚕️</span>
                        <h4>Expert Consultations</h4>
                        <p>Connect with certified Ayurvedic practitioners</p>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🧘‍♀️</span>
                        <h4>Wellness Services</h4>
                        <p>Personalized treatments for mind, body & soul</p>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">📚</span>
                        <h4>Knowledge Hub</h4>
                        <p>Learn about Ayurvedic principles and lifestyle tips</p>
                    </div>
                </div>
                
                <center>
                    <a href="' . $this->getBaseUrl() . '" class="cta-button">
                        Explore VedaLife Now
                    </a>
                </center>
                
                <h3 style="color: #43a047; margin: 30px 0 20px;">Start Your Wellness Journey</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin: 12px 0; padding: 0 0 0 25px; position: relative;">
                        <span style="position: absolute; left: 0; color: #66bb6a; font-weight: bold;">✓</span>
                        Browse our curated collection of authentic Ayurvedic products
                    </li>
                    <li style="margin: 12px 0; padding: 0 0 0 25px; position: relative;">
                        <span style="position: absolute; left: 0; color: #66bb6a; font-weight: bold;">✓</span>
                        Book a consultation with our expert practitioners
                    </li>
                    <li style="margin: 12px 0; padding: 0 0 0 25px; position: relative;">
                        <span style="position: absolute; left: 0; color: #66bb6a; font-weight: bold;">✓</span>
                        Discover wellness services tailored to your needs
                    </li>
                    <li style="margin: 12px 0; padding: 0 0 0 25px; position: relative;">
                        <span style="position: absolute; left: 0; color: #66bb6a; font-weight: bold;">✓</span>
                        Join our community for wellness tips and updates
                    </li>
                </ul>
            </div>
            
            <div class="footer">
                <p><strong>VedaLife - Where Ancient Wisdom Meets Modern Wellness</strong></p>
                <div class="social-links">
                    <a href="#">📘</a>
                    <a href="#">📷</a>
                    <a href="#">🐦</a>
                    <a href="#">💼</a>
                </div>
                <p>Thank you for choosing VedaLife for your wellness journey!</p>
                <p style="font-size: 12px; opacity: 0.7; margin-top: 20px;">
                    This email was sent from VedaLife Welcome System. <br>
                    If you have any questions, please don\'t hesitate to contact our support team.
                </p>
            </div>
        </div>
    </body>
    </html>';
    }
}

/**
 * Simple function to send reset email (easier to use)
 */
function sendPasswordResetEmail($email, $token) {
    $emailSender = new EmailSender();
    return $emailSender->sendPasswordResetEmail($email, $token);
}

/**
 * Simple function to send welcome email (easier to use)
 */
function sendWelcomeEmail($email, $username) {
    $emailSender = new EmailSender();
    return $emailSender->sendWelcomeEmail($email, $username);
}
?>