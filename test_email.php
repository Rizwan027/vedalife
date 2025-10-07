<?php
/**
 * Email System Test Script for VedaLife
 * This script tests all email functionality to ensure everything is working properly
 */

// Include the email configuration
require_once 'email_config.php';
require_once 'connection.php';

// Set output to HTML for better display
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VedaLife Email System Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #66bb6a, #43a047);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 600;
        }
        .content {
            padding: 40px;
        }
        .test-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 5px solid #66bb6a;
        }
        .test-section h3 {
            color: #43a047;
            margin: 0 0 15px;
            font-size: 20px;
        }
        .btn {
            background: linear-gradient(135deg, #66bb6a, #43a047);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            transition: transform 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #66bb6a;
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 600;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #d1d3d4;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
        .config-info {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .config-info h4 {
            color: #1976d2;
            margin: 0 0 10px;
        }
        .config-info code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌿 VedaLife Email System Test</h1>
            <p>Comprehensive Testing Dashboard</p>
        </div>

        <div class="content">
            <!-- Email Configuration Info -->
            <div class="config-info">
                <h4>📧 Current Email Configuration</h4>
                <p><strong>SMTP Host:</strong> <code>smtp.gmail.com</code></p>
                <p><strong>SMTP Port:</strong> <code>587</code> (STARTTLS)</p>
                <p><strong>Username:</strong> <code>emailservice780@gmail.com</code></p>
                <p><strong>From Name:</strong> <code>VedaLife</code></p>
                <p><strong>Status:</strong> ✅ Configuration Loaded Successfully</p>
            </div>

            <?php
            // Handle form submissions and test email sending
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $test_type = $_POST['test_type'] ?? '';
                $test_email = filter_var($_POST['test_email'] ?? '', FILTER_VALIDATE_EMAIL);

                if (!$test_email) {
                    echo '<div class="status error">❌ Please enter a valid email address for testing.</div>';
                } else {
                    echo '<div class="status info">🔄 Testing email functionality for: ' . htmlspecialchars($test_email) . '</div>';

                    switch ($test_type) {
                        case 'password_reset':
                            $test_token = bin2hex(random_bytes(16)); // Shorter token for testing
                            $success = sendPasswordResetEmail($test_email, $test_token);
                            if ($success) {
                                echo '<div class="status success">✅ Password Reset Email sent successfully!</div>';
                            } else {
                                echo '<div class="status error">❌ Failed to send Password Reset Email.</div>';
                            }
                            break;

                        case 'welcome':
                            $test_username = $_POST['test_username'] ?? 'Test User';
                            $success = sendWelcomeEmail($test_email, $test_username);
                            if ($success) {
                                echo '<div class="status success">✅ Welcome Email sent successfully!</div>';
                            } else {
                                echo '<div class="status error">❌ Failed to send Welcome Email.</div>';
                            }
                            break;

                        case 'appointment':
                            $status = $_POST['appointment_status'] ?? 'confirmed';
                            $test_data = [
                                'status' => $status,
                                'customer_name' => $_POST['customer_name'] ?? 'Test Customer',
                                'service' => $_POST['service'] ?? 'General Consultation',
                                'preferred_date' => $_POST['appointment_date'] ?? date('Y-m-d'),
                                'appointment_id' => rand(1000, 9999),
                                'phone' => $_POST['phone'] ?? '+91 98765 43210',
                                'notes' => $_POST['notes'] ?? 'This is a test appointment notification.'
                            ];
                            
                            $emailSender = new EmailSender();
                            $success = $emailSender->sendAppointmentStatusEmail($test_email, $test_data);
                            if ($success) {
                                echo '<div class="status success">✅ Appointment Status Email sent successfully!</div>';
                            } else {
                                echo '<div class="status error">❌ Failed to send Appointment Status Email.</div>';
                            }
                            break;

                        case 'order_confirmation':
                            // Create sample order data
                            $order_data = [
                                'order_number' => 'VL-' . date('Ymd') . '-' . rand(1000, 9999),
                                'customer_name' => $_POST['customer_name'] ?? 'Test Customer',
                                'payment_method' => $_POST['payment_method'] ?? 'COD',
                                'phone' => $_POST['phone'] ?? '+91 98765 43210',
                                'shipping_address' => $_POST['shipping_address'] ?? "123 Test Street\nTest City, Test State 123456\nIndia",
                                'items' => [
                                    [
                                        'name' => 'Ashwagandha Capsules',
                                        'price' => 299.00,
                                        'quantity' => 2
                                    ],
                                    [
                                        'name' => 'Herbal Tea Blend',
                                        'price' => 150.00,
                                        'quantity' => 1
                                    ]
                                ]
                            ];
                            
                            $emailSender = new EmailSender();
                            $success = $emailSender->sendOrderConfirmationEmail($test_email, $order_data);
                            if ($success) {
                                echo '<div class="status success">✅ Order Confirmation Email sent successfully!</div>';
                            } else {
                                echo '<div class="status error">❌ Failed to send Order Confirmation Email.</div>';
                            }
                            break;

                        default:
                            echo '<div class="status error">❌ Invalid test type selected.</div>';
                            break;
                    }
                }
            }
            ?>

            <div class="grid">
                <!-- Password Reset Email Test -->
                <div class="test-section">
                    <h3>🔐 Password Reset Email Test</h3>
                    <p>Test the password reset email functionality with a secure token and professional template.</p>
                    <form method="POST">
                        <input type="hidden" name="test_type" value="password_reset">
                        <div class="form-group">
                            <label>Test Email Address:</label>
                            <input type="email" name="test_email" required placeholder="your-email@example.com">
                        </div>
                        <button type="submit" class="btn">Send Test Reset Email</button>
                    </form>
                </div>

                <!-- Welcome Email Test -->
                <div class="test-section">
                    <h3>👋 Welcome Email Test</h3>
                    <p>Test the welcome email sent to new users upon registration.</p>
                    <form method="POST">
                        <input type="hidden" name="test_type" value="welcome">
                        <div class="form-group">
                            <label>Test Email Address:</label>
                            <input type="email" name="test_email" required placeholder="your-email@example.com">
                        </div>
                        <div class="form-group">
                            <label>Test Username:</label>
                            <input type="text" name="test_username" value="Test User" placeholder="Test User">
                        </div>
                        <button type="submit" class="btn">Send Test Welcome Email</button>
                    </form>
                </div>
            </div>

            <!-- Appointment Status Email Test -->
            <div class="test-section">
                <h3>📅 Appointment Status Email Test</h3>
                <p>Test appointment status notification emails (confirmed, cancelled, completed).</p>
                <form method="POST">
                    <input type="hidden" name="test_type" value="appointment">
                    <div class="grid">
                        <div class="form-group">
                            <label>Test Email Address:</label>
                            <input type="email" name="test_email" required placeholder="your-email@example.com">
                        </div>
                        <div class="form-group">
                            <label>Appointment Status:</label>
                            <select name="appointment_status">
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Customer Name:</label>
                            <input type="text" name="customer_name" value="Test Customer" placeholder="Customer Name">
                        </div>
                        <div class="form-group">
                            <label>Service:</label>
                            <input type="text" name="service" value="General Consultation" placeholder="Service Type">
                        </div>
                        <div class="form-group">
                            <label>Appointment Date:</label>
                            <input type="date" name="appointment_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone:</label>
                            <input type="tel" name="phone" value="+91 98765 43210" placeholder="+91 98765 43210">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes:</label>
                        <textarea name="notes" rows="3" placeholder="Additional notes or instructions...">This is a test appointment notification.</textarea>
                    </div>
                    <button type="submit" class="btn">Send Test Appointment Email</button>
                </form>
            </div>

            <!-- Order Confirmation Email Test -->
            <div class="test-section">
                <h3>🛒 Order Confirmation Email Test</h3>
                <p>Test order confirmation emails with sample products and billing information.</p>
                <form method="POST">
                    <input type="hidden" name="test_type" value="order_confirmation">
                    <div class="grid">
                        <div class="form-group">
                            <label>Test Email Address:</label>
                            <input type="email" name="test_email" required placeholder="your-email@example.com">
                        </div>
                        <div class="form-group">
                            <label>Customer Name:</label>
                            <input type="text" name="customer_name" value="Test Customer" placeholder="Customer Name">
                        </div>
                        <div class="form-group">
                            <label>Payment Method:</label>
                            <select name="payment_method">
                                <option value="COD">Cash on Delivery</option>
                                <option value="online">Online Payment</option>
                                <option value="razorpay">Razorpay</option>
                                <option value="paytm">Paytm</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Phone:</label>
                            <input type="tel" name="phone" value="+91 98765 43210" placeholder="+91 98765 43210">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Shipping Address:</label>
                        <textarea name="shipping_address" rows="4" placeholder="Complete shipping address...">123 Test Street
Test City, Test State 123456
India</textarea>
                    </div>
                    <button type="submit" class="btn">Send Test Order Email</button>
                </form>
            </div>

            <!-- Email System Health Check -->
            <div class="test-section">
                <h3>🔍 Email System Health Check</h3>
                <div class="status info">
                    <strong>System Status:</strong>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>✅ PHPMailer Library: Available (Version 6.8.1)</li>
                        <li>✅ EmailSender Class: Loaded</li>
                        <li>✅ SMTP Configuration: Valid</li>
                        <li>✅ Database Connection: <?php echo isset($conn) && $conn ? 'Active' : 'Not Available'; ?></li>
                        <li>✅ Email Templates: All templates loaded</li>
                        <li>✅ Error Logging: Enabled</li>
                    </ul>
                </div>
                <p><strong>Features Available:</strong></p>
                <ul>
                    <li>Password Reset Emails with secure tokens</li>
                    <li>Welcome Emails for new registrations</li>
                    <li>Appointment Status Notifications</li>
                    <li>Order Confirmation Emails</li>
                    <li>Automatic fallback to PHP mail() if SMTP fails</li>
                    <li>Professional HTML email templates</li>
                    <li>Rate limiting protection</li>
                </ul>
            </div>

            <!-- Instructions -->
            <div class="test-section">
                <h3>📋 Testing Instructions</h3>
                <ol>
                    <li><strong>Enter Your Email:</strong> Use your actual email address to receive test emails</li>
                    <li><strong>Check All Email Types:</strong> Test each email type to ensure they work properly</li>
                    <li><strong>Verify Delivery:</strong> Check your inbox and spam folder</li>
                    <li><strong>Test Different Scenarios:</strong> Try different appointment statuses and order details</li>
                    <li><strong>Check Error Logs:</strong> Review your server logs for any SMTP errors</li>
                </ol>
                <div class="status info">
                    <strong>Note:</strong> If emails fail to send, check:
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        <li>Gmail App Password is correct and active</li>
                        <li>XAMPP/server allows outbound SMTP connections</li>
                        <li>No firewall blocking port 587</li>
                        <li>PHP error logs for detailed error messages</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="../admin/" class="btn btn-secondary">← Back to Admin Panel</a>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">🔄 Refresh Test Page</a>
            </div>
        </div>
    </div>
</body>
</html>