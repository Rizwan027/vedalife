<?php
// Include authentication check
require_once 'auth_check.php';

// Require user to be logged in to book appointments
if (!isUserLoggedIn()) {
    http_response_code(401);
    echo "Unauthorized: Please login to book an appointment.";
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";  // set your MySQL root password if any
$dbname = "vedalife";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo "Connection failed: " . $conn->connect_error;
    exit();
}

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
$service = $_POST['service'] ?? '';
$notes = trim($_POST['notes'] ?? '');

// Basic validation
if (empty($name) || empty($email) || empty($phone) || empty($preferred_date) || empty($service)) {
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

// Prepare and execute query (fixed SQL injection)
$stmt = $conn->prepare("INSERT INTO booking (user_id, name, email, phone, preferred_date, service, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssss", $user_id, $name, $email, $phone, $preferred_date, $service, $notes);

if ($stmt->execute()) {
    echo "✅ Appointment booked successfully! We'll contact you soon to confirm your booking.";
} else {
    http_response_code(500);
    echo "❌ Error booking appointment: " . $stmt->error;
}

$stmt->close();

$conn->close();
?>
