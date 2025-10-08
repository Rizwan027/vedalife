<?php
// Include authentication check
require_once 'auth_check.php';
require_once __DIR__ . '/config/connection.php';

// Require user to be logged in to book appointments
if (!isUserLoggedIn()) {
    http_response_code(401);
    echo "Unauthorized: Please login to book an appointment.";
    exit();
}

// DB connection is provided by config/connection.php as $conn

// Verify that the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit();
}

// Get current user ID
$user_id = $_SESSION['user_id'];

// Get POST data and validate
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$preferred_date = $_POST['date'] ?? ''; // Note: using 'date' from form, not 'preferred_date'
$preferred_time = $_POST['preferred_time'] ?? '';
$service = $_POST['service'] ?? '';
$notes = trim($_POST['notes'] ?? '');

// Basic validation
if (empty($name) || empty($email) || empty($phone) || empty($preferred_date) || empty($preferred_time) || empty($service)) {
    http_response_code(400);
    echo "Error: All required fields must be filled.";
    exit();
}

// Validate date is not in the past
if (strtotime($preferred_date) < strtotime('today')) {
    http_response_code(400);
    echo "Error: Please select a future date.";
    exit();
}

// Determine if booking table has preferred_time column (backward compatibility)
$hasPreferredTime = false;
$colCheck = $conn->query("SHOW COLUMNS FROM `booking` LIKE 'preferred_time'");
if ($colCheck) {
    $hasPreferredTime = ($colCheck->num_rows > 0);
    $colCheck->free();
}

if ($hasPreferredTime) {
    $sql = "INSERT INTO `booking` (`user_id`, `name`, `email`, `phone`, `service`, `preferred_date`, `preferred_time`, `notes`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo "❌ Error preparing statement: " . $conn->error;
        exit();
    }
    $stmt->bind_param("isssssss", $user_id, $name, $email, $phone, $service, $preferred_date, $preferred_time, $notes);
} else {
    // Fallback for DBs without preferred_time column
    $sql = "INSERT INTO `booking` (`user_id`, `name`, `email`, `phone`, `service`, `preferred_date`, `notes`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo "❌ Error preparing statement: " . $conn->error;
        exit();
    }
    $stmt->bind_param("issssss", $user_id, $name, $email, $phone, $service, $preferred_date, $notes);
}

if ($stmt->execute()) {
    echo "✅ Appointment booked successfully! We'll contact you soon to confirm your booking.";
} else {
    http_response_code(500);
    echo "❌ Error booking appointment: " . $stmt->error;
}

$stmt->close();

$conn->close();
?>
