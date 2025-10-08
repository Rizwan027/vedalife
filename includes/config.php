<?php
/**
 * VedaLife - Application Configuration
 * 
 * Central configuration file for application-wide settings.
 * Modify these settings as needed for different environments.
 * 
 * @author VedaLife Development Team
 * @version 2.0
 */

// Prevent direct access
if (!defined('VEDALIFE_APP')) {
    define('VEDALIFE_APP', true);
}

// Application Settings
define('VEDALIFE_VERSION', '2.0');
define('VEDALIFE_NAME', 'VedaLife Ayurvedic Wellness');

// Environment Settings
define('VEDALIFE_DEBUG', true); // Set to true for development, false for production
define('VEDALIFE_LOG_DB', false); // Set to true to log database activities

// Security Settings
define('VEDALIFE_SESSION_LIFETIME', 3600); // 1 hour in seconds
define('VEDALIFE_SESSION_NAME', 'VEDALIFE_SESSION');
define('VEDALIFE_CSRF_TOKEN_NAME', 'vedalife_csrf_token');

// Application Paths
define('VEDALIFE_ROOT', dirname(dirname(__FILE__)));
define('VEDALIFE_INCLUDES', VEDALIFE_ROOT . '/includes');
define('VEDALIFE_UPLOADS', VEDALIFE_ROOT . '/uploads');

// URL Settings (adjust these based on your server setup)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = '/VedaLife'; // Change this if your app is in a different directory

define('VEDALIFE_BASE_URL', $protocol . '://' . $host . $basePath);
define('VEDALIFE_ADMIN_URL', VEDALIFE_BASE_URL . '/admin');

// Email Settings (for notifications)
define('VEDALIFE_MAIL_FROM', 'noreply@vedalife.com');
define('VEDALIFE_MAIL_FROM_NAME', 'VedaLife Wellness');
define('VEDALIFE_ADMIN_EMAIL', 'admin@vedalife.com');

// Pagination Settings
define('VEDALIFE_DEFAULT_PAGE_SIZE', 10);
define('VEDALIFE_MAX_PAGE_SIZE', 100);

// File Upload Settings
define('VEDALIFE_MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('VEDALIFE_ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Initialize session settings (skip in CLI to avoid warnings during CLI diagnostics)
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', VEDALIFE_SESSION_LIFETIME);
    
    session_name(VEDALIFE_SESSION_NAME);
    session_start();
}

// Set error reporting based on environment
if (VEDALIFE_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Timezone setting
date_default_timezone_set('Asia/Kolkata'); // Change as needed

?>