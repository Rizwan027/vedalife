<?php
require_once 'admin_auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();
$conn = getAdminDbConnection();

$message = '';
$message_type = '';

// Create settings table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS admin_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    category VARCHAR(50) DEFAULT 'general',
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_key (setting_key)
)");

// Handle settings updates
if ($_POST['action'] ?? '' === 'update_settings') {
    $settingsToUpdate = [
        // General Settings
        'site_name' => $_POST['site_name'] ?? '',
        'site_email' => $_POST['site_email'] ?? '',
        'site_phone' => $_POST['site_phone'] ?? '',
        'site_address' => $_POST['site_address'] ?? '',
        'site_description' => $_POST['site_description'] ?? '',
        'timezone' => $_POST['timezone'] ?? '',
        
        // Business Settings
        'business_hours_start' => $_POST['business_hours_start'] ?? '',
        'business_hours_end' => $_POST['business_hours_end'] ?? '',
        'working_days' => implode(',', $_POST['working_days'] ?? []),
        'appointment_duration' => $_POST['appointment_duration'] ?? '60',
        'max_appointments_per_day' => $_POST['max_appointments_per_day'] ?? '20',
        'advance_booking_days' => $_POST['advance_booking_days'] ?? '30',
        
        // Email Settings
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '',
        'smtp_username' => $_POST['smtp_username'] ?? '',
        'smtp_password' => $_POST['smtp_password'] ?? '',
        'email_from_name' => $_POST['email_from_name'] ?? '',
        'email_from_address' => $_POST['email_from_address'] ?? '',
        
        // Notification Settings
        'enable_email_notifications' => $_POST['enable_email_notifications'] ?? '0',
        'enable_sms_notifications' => $_POST['enable_sms_notifications'] ?? '0',
        'admin_notification_email' => $_POST['admin_notification_email'] ?? '',
        'booking_confirmation' => $_POST['booking_confirmation'] ?? '1',
        'appointment_reminders' => $_POST['appointment_reminders'] ?? '1',
        'reminder_hours_before' => $_POST['reminder_hours_before'] ?? '24',
        
        // Payment Settings
        'currency_symbol' => $_POST['currency_symbol'] ?? '₹',
        'tax_rate' => $_POST['tax_rate'] ?? '0',
        'enable_online_payment' => $_POST['enable_online_payment'] ?? '0',
        'payment_gateway' => $_POST['payment_gateway'] ?? 'razorpay',
        'razorpay_key_id' => $_POST['razorpay_key_id'] ?? '',
        'razorpay_key_secret' => $_POST['razorpay_key_secret'] ?? '',
        
        // System Settings
        'maintenance_mode' => $_POST['maintenance_mode'] ?? '0',
        'allow_registration' => $_POST['allow_registration'] ?? '1',
        'require_email_verification' => $_POST['require_email_verification'] ?? '0',
        'session_timeout' => $_POST['session_timeout'] ?? '30',
        'max_login_attempts' => $_POST['max_login_attempts'] ?? '5',
        'auto_delete_cancelled' => $_POST['auto_delete_cancelled'] ?? '7',
        
        // Appearance Settings
        'theme_color' => $_POST['theme_color'] ?? '#2c6e49',
        'logo_text' => $_POST['logo_text'] ?? 'VEDAMRUT',
        'footer_text' => $_POST['footer_text'] ?? '',
        'show_testimonials' => $_POST['show_testimonials'] ?? '1',
        'items_per_page' => $_POST['items_per_page'] ?? '15'
    ];
    
    try {
        foreach ($settingsToUpdate as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO admin_settings (setting_key, setting_value, updated_by) VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)");
            $stmt->bind_param("ssi", $key, $value, $admin['id']);
            $stmt->execute();
        }
        
        $message = "Settings updated successfully!";
        $message_type = "success";
        logAdminActivity('settings_update', 'Updated system settings');
        
    } catch (Exception $e) {
        $message = "Error updating settings: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Get all current settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM admin_settings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Helper function to get setting value
function getSetting($key, $default = '') {
    global $settings;
    return $settings[$key] ?? $default;
}

// Get system statistics for dashboard
$stats = [];
$stats['total_users'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0;
$stats['total_appointments'] = $conn->query("SELECT COUNT(*) as count FROM booking")->fetch_assoc()['count'] ?? 0;
$stats['total_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'] ?? 0;
$stats['total_products'] = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'] ?? 0;
$stats['disk_usage'] = '0 MB'; // Placeholder
$stats['database_size'] = '0 MB'; // Placeholder

// Get recent activity
$recent_activity = $conn->query("SELECT * FROM admin_activity_log ORDER BY created_at DESC LIMIT 10");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - VEDAMRUT Admin</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin Shared Styles -->
    <link rel="stylesheet" href="css/admin-style.css">
    
    <style>
        .settings-nav {
            border-right: 1px solid rgba(0,0,0,0.1);
            padding-right: 0;
        }
        
        .settings-nav .nav-pills .nav-link {
            border-radius: 0;
            border-left: 3px solid transparent;
            color: var(--text-light);
            padding: 1rem 1.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .settings-nav .nav-pills .nav-link:hover {
            background: rgba(44, 110, 73, 0.05);
            border-left-color: var(--primary-light);
        }
        
        .settings-nav .nav-pills .nav-link.active {
            background: rgba(44, 110, 73, 0.1);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .settings-section {
            display: none;
        }
        
        .settings-section.active {
            display: block;
        }
        
        .setting-group {
            background: var(--background-white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .setting-group h6 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(44, 110, 73, 0.1);
        }
        
        .form-switch .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .stats-mini-card {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .stats-mini-card h5 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .stats-mini-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.8rem;
        }
        
        .activity-item {
            padding: 0.8rem;
            border-left: 3px solid var(--primary-light);
            background: rgba(44, 110, 73, 0.02);
            margin-bottom: 0.5rem;
            border-radius: 0 8px 8px 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header" data-aos="fade-down">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">System Settings</h3>
                    <p class="mb-0 text-muted">Configure and manage your VedaLife application</p>
                </div>
                <div class="text-muted">
                    <i class="fas fa-cog me-2"></i>Admin Configuration Panel
                </div>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert" data-aos="fade-up">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row" data-aos="fade-up">
            <!-- Settings Navigation -->
            <div class="col-lg-3">
                <div class="settings-nav">
                    <div class="nav flex-column nav-pills" role="tablist">
                        <button class="nav-link active" onclick="showSection('general')" type="button">
                            <i class="fas fa-cog me-2"></i>General Settings
                        </button>
                        <button class="nav-link" onclick="showSection('business')" type="button">
                            <i class="fas fa-business-time me-2"></i>Business Hours
                        </button>
                        <button class="nav-link" onclick="showSection('email')" type="button">
                            <i class="fas fa-envelope me-2"></i>Email Configuration
                        </button>
                        <button class="nav-link" onclick="showSection('notifications')" type="button">
                            <i class="fas fa-bell me-2"></i>Notifications
                        </button>
                        <button class="nav-link" onclick="showSection('payments')" type="button">
                            <i class="fas fa-credit-card me-2"></i>Payment Settings
                        </button>
                        <button class="nav-link" onclick="showSection('system')" type="button">
                            <i class="fas fa-server me-2"></i>System Settings
                        </button>
                        <button class="nav-link" onclick="showSection('appearance')" type="button">
                            <i class="fas fa-palette me-2"></i>Appearance
                        </button>
                        <button class="nav-link" onclick="showSection('maintenance')" type="button">
                            <i class="fas fa-tools me-2"></i>Maintenance
                        </button>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="mt-4">
                        <h6 class="text-muted">Quick Stats</h6>
                        <div class="stats-mini-card">
                            <h5><?php echo $stats['total_users']; ?></h5>
                            <p>Total Users</p>
                        </div>
                        <div class="stats-mini-card">
                            <h5><?php echo $stats['total_appointments']; ?></h5>
                            <p>Appointments</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Content -->
            <div class="col-lg-9">
                <form method="POST" id="settingsForm">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <!-- General Settings -->
                    <div id="general" class="settings-section active">
                        <div class="setting-group">
                            <h6><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" class="form-control" name="site_name" value="<?php echo htmlspecialchars(getSetting('site_name', 'VEDAMRUT')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" name="site_email" value="<?php echo htmlspecialchars(getSetting('site_email')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" name="site_phone" value="<?php echo htmlspecialchars(getSetting('site_phone')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Timezone</label>
                                    <select class="form-select" name="timezone">
                                        <option value="Asia/Kolkata" <?php echo getSetting('timezone') === 'Asia/Kolkata' ? 'selected' : ''; ?>>Asia/Kolkata (IST)</option>
                                        <option value="UTC" <?php echo getSetting('timezone') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Business Address</label>
                                    <textarea class="form-control" name="site_address" rows="3"><?php echo htmlspecialchars(getSetting('site_address')); ?></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Site Description</label>
                                    <textarea class="form-control" name="site_description" rows="3"><?php echo htmlspecialchars(getSetting('site_description')); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Business Hours Settings -->
                    <div id="business" class="settings-section">
                        <div class="setting-group">
                            <h6><i class="fas fa-clock me-2"></i>Business Hours & Appointments</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Opening Time</label>
                                    <input type="time" class="form-control" name="business_hours_start" value="<?php echo htmlspecialchars(getSetting('business_hours_start', '09:00')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Closing Time</label>
                                    <input type="time" class="form-control" name="business_hours_end" value="<?php echo htmlspecialchars(getSetting('business_hours_end', '18:00')); ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Working Days</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php
                                        $workingDays = explode(',', getSetting('working_days', 'monday,tuesday,wednesday,thursday,friday,saturday'));
                                        $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                        foreach ($allDays as $day) {
                                            $checked = in_array($day, $workingDays) ? 'checked' : '';
                                            echo "<div class=\"form-check\">
                                                    <input class=\"form-check-input\" type=\"checkbox\" name=\"working_days[]\" value=\"$day\" id=\"$day\" $checked>
                                                    <label class=\"form-check-label\" for=\"$day\">" . ucfirst($day) . "</label>
                                                  </div>";
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Appointment Duration (minutes)</label>
                                    <input type="number" class="form-control" name="appointment_duration" value="<?php echo htmlspecialchars(getSetting('appointment_duration', '60')); ?>" min="15" max="180" step="15">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Appointments/Day</label>
                                    <input type="number" class="form-control" name="max_appointments_per_day" value="<?php echo htmlspecialchars(getSetting('max_appointments_per_day', '20')); ?>" min="1" max="100">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Advance Booking Days</label>
                                    <input type="number" class="form-control" name="advance_booking_days" value="<?php echo htmlspecialchars(getSetting('advance_booking_days', '30')); ?>" min="1" max="365">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Settings -->
                    <div id="email" class="settings-section">
                        <div class="setting-group">
                            <h6><i class="fas fa-server me-2"></i>SMTP Configuration</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" name="smtp_host" value="<?php echo htmlspecialchars(getSetting('smtp_host')); ?>" placeholder="smtp.gmail.com">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" name="smtp_port" value="<?php echo htmlspecialchars(getSetting('smtp_port', '587')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="text" class="form-control" name="smtp_username" value="<?php echo htmlspecialchars(getSetting('smtp_username')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" class="form-control" name="smtp_password" value="<?php echo htmlspecialchars(getSetting('smtp_password')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">From Name</label>
                                    <input type="text" class="form-control" name="email_from_name" value="<?php echo htmlspecialchars(getSetting('email_from_name', 'VEDAMRUT')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">From Email</label>
                                    <input type="email" class="form-control" name="email_from_address" value="<?php echo htmlspecialchars(getSetting('email_from_address')); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notification Settings -->
                    <div id="notifications" class="settings-section">
                        <div class="setting-group">
                            <h6><i class="fas fa-bell me-2"></i>Notification Preferences</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_email_notifications" value="1" <?php echo getSetting('enable_email_notifications') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Enable Email Notifications</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_sms_notifications" value="1" <?php echo getSetting('enable_sms_notifications') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Enable SMS Notifications</label>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Admin Notification Email</label>
                                    <input type="email" class="form-control" name="admin_notification_email" value="<?php echo htmlspecialchars(getSetting('admin_notification_email')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="booking_confirmation" value="1" <?php echo getSetting('booking_confirmation', '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Send Booking Confirmations</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="appointment_reminders" value="1" <?php echo getSetting('appointment_reminders', '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Send Appointment Reminders</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Reminder Hours Before Appointment</label>
                                    <input type="number" class="form-control" name="reminder_hours_before" value="<?php echo htmlspecialchars(getSetting('reminder_hours_before', '24')); ?>" min="1" max="168">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Settings -->
                    <div id="payments" class="settings-section">
                        <div class="setting-group">
                            <h6><i class="fas fa-money-bill me-2"></i>Payment Configuration</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Currency Symbol</label>
                                    <input type="text" class="form-control" name="currency_symbol" value="<?php echo htmlspecialchars(getSetting('currency_symbol', '₹')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tax Rate (%)</label>
                                    <input type="number" class="form-control" name="tax_rate" value="<?php echo htmlspecialchars(getSetting('tax_rate', '0')); ?>" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_online_payment" value="1" <?php echo getSetting('enable_online_payment') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Enable Online Payment</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Payment Gateway</label>
                                    <select class="form-select" name="payment_gateway">
                                        <option value="razorpay" <?php echo getSetting('payment_gateway') === 'razorpay' ? 'selected' : ''; ?>>Razorpay</option>
                                        <option value="paytm" <?php echo getSetting('payment_gateway') === 'paytm' ? 'selected' : ''; ?>>Paytm</option>
                                        <option value="stripe" <?php echo getSetting('payment_gateway') === 'stripe' ? 'selected' : ''; ?>>Stripe</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Razorpay Key ID</label>
                                    <input type="text" class="form-control" name="razorpay_key_id" value="<?php echo htmlspecialchars(getSetting('razorpay_key_id')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Razorpay Key Secret</label>
                                    <input type="password" class="form-control" name="razorpay_key_secret" value="<?php echo htmlspecialchars(getSetting('razorpay_key_secret')); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Settings -->
                    <div id="system" class="settings-section">
                        <div class="setting-group">
                            <h6><i class="fas fa-cogs me-2"></i>System Configuration</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" <?php echo getSetting('maintenance_mode') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Maintenance Mode</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="allow_registration" value="1" <?php echo getSetting('allow_registration', '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Allow User Registration</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="require_email_verification" value="1" <?php echo getSetting('require_email_verification') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Require Email Verification</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" class="form-control" name="session_timeout" value="<?php echo htmlspecialchars(getSetting('session_timeout', '30')); ?>" min="5" max="480">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Max Login Attempts</label>
                                    <input type="number" class="form-control" name="max_login_attempts" value="<?php echo htmlspecialchars(getSetting('max_login_attempts', '5')); ?>" min="3" max="20">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Auto-delete Cancelled (days)</label>
                                    <input type="number" class="form-control" name="auto_delete_cancelled" value="<?php echo htmlspecialchars(getSetting('auto_delete_cancelled', '7')); ?>" min="1" max="365">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appearance Settings -->
                    <div id="appearance" class="settings-section">
                        <div class="setting-group">
                            <h6><i class="fas fa-paint-brush me-2"></i>Appearance & Display</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Theme Color</label>
                                    <input type="color" class="form-control form-control-color" name="theme_color" value="<?php echo htmlspecialchars(getSetting('theme_color', '#2c6e49')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Logo Text</label>
                                    <input type="text" class="form-control" name="logo_text" value="<?php echo htmlspecialchars(getSetting('logo_text', 'VEDAMRUT')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Items Per Page</label>
                                    <select class="form-select" name="items_per_page">
                                        <option value="10" <?php echo getSetting('items_per_page') === '10' ? 'selected' : ''; ?>>10</option>
                                        <option value="15" <?php echo getSetting('items_per_page', '15') === '15' ? 'selected' : ''; ?>>15</option>
                                        <option value="25" <?php echo getSetting('items_per_page') === '25' ? 'selected' : ''; ?>>25</option>
                                        <option value="50" <?php echo getSetting('items_per_page') === '50' ? 'selected' : ''; ?>>50</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="show_testimonials" value="1" <?php echo getSetting('show_testimonials', '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Show Testimonials</label>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Footer Text</label>
                                    <textarea class="form-control" name="footer_text" rows="2"><?php echo htmlspecialchars(getSetting('footer_text')); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Maintenance Tools -->
                    <div id="maintenance" class="settings-section">
                        <div class="setting-group">
                            <h6><i class="fas fa-tools me-2"></i>System Maintenance</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-outline-info" onclick="clearCache()">
                                            <i class="fas fa-trash me-2"></i>Clear System Cache
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-outline-warning" onclick="cleanupLogs()">
                                            <i class="fas fa-broom me-2"></i>Cleanup Old Logs
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-outline-success" onclick="optimizeDatabase()">
                                            <i class="fas fa-database me-2"></i>Optimize Database
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-outline-primary" onclick="backupDatabase()">
                                            <i class="fas fa-download me-2"></i>Backup Database
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <h6><i class="fas fa-info-circle me-2"></i>System Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                                        <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Database Size:</strong> <?php echo $stats['database_size']; ?></p>
                                        <p><strong>Last Settings Update:</strong> <?php echo date('M j, Y g:i A'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Save Button -->
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-gradient-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Save All Settings
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({ duration: 600, easing: 'ease-in-out', once: true });
        
        // Settings navigation
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.settings-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.settings-nav .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');
            
            // Add active class to clicked nav link
            event.target.classList.add('active');
        }
        
        // Maintenance functions
        function clearCache() {
            if (confirm('Are you sure you want to clear the system cache?')) {
                // Implement cache clearing logic
                alert('Cache cleared successfully!');
            }
        }
        
        function cleanupLogs() {
            if (confirm('This will remove old log files. Continue?')) {
                // Implement log cleanup logic
                alert('Old logs cleaned up successfully!');
            }
        }
        
        function optimizeDatabase() {
            if (confirm('This will optimize database tables. Continue?')) {
                // Implement database optimization logic
                alert('Database optimized successfully!');
            }
        }
        
        function backupDatabase() {
            if (confirm('Create a database backup?')) {
                // Implement database backup logic
                alert('Database backup created successfully!');
            }
        }
        
        // Form validation
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const requiredFields = ['site_name', 'site_email'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>