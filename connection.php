<?php
// Database configuration for VedaLife
$host = "localhost";
$user = "root";
$pass = "";
$db   = "vedalife";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8 to avoid encoding issues
$conn->set_charset("utf8");
?>