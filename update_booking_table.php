<?php
/**
 * Database Update Script
 * This script adds the missing 'preferred_time' column to the booking table
 */

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vedalife";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>VedaLife Database Update</h2>";
echo "<p>Adding 'preferred_time' column to booking table...</p>";

// Check if column already exists
$result = $conn->query("SHOW COLUMNS FROM booking LIKE 'preferred_time'");

if ($result->num_rows > 0) {
    echo "<div style='color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Column 'preferred_time' already exists in booking table.";
    echo "</div>";
} else {
    // Add the column
    $sql = "ALTER TABLE `booking` ADD COLUMN `preferred_time` TIME NULL AFTER `preferred_date`";
    
    if ($conn->query($sql) === TRUE) {
        echo "<div style='color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ Column 'preferred_time' added successfully!";
        echo "</div>";
        
        // Set default time for existing bookings
        $updateSql = "UPDATE `booking` SET `preferred_time` = '10:00:00' WHERE `preferred_time` IS NULL";
        if ($conn->query($updateSql) === TRUE) {
            echo "<div style='color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ Default time (10:00 AM) set for existing bookings.";
            echo "</div>";
        }
    } else {
        echo "<div style='color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Error adding column: " . $conn->error;
        echo "</div>";
    }
}

// Show updated table structure
echo "<h3>Updated Booking Table Structure:</h3>";
$result = $conn->query("DESCRIBE booking");

if ($result) {
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f5f5f5;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $highlight = ($row['Field'] === 'preferred_time') ? "style='background-color: #e8f5e9;'" : "";
        echo "<tr $highlight>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();

echo "<div style='color: #1976d2; background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<strong>🎉 Update Complete!</strong><br>";
echo "The booking system now supports time selection. Users can now:";
echo "<ul>";
echo "<li>Select preferred appointment times when booking</li>";
echo "<li>View appointment times in their profile</li>";
echo "<li>See time information in appointment history</li>";
echo "</ul>";
echo "<p><strong>Next steps:</strong> The profile.php warning should now be resolved.</p>";
echo "</div>";

echo "<div style='margin-top: 20px;'>";
echo "<a href='profile.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Test Profile Page</a>";
echo "<a href='appointment.php' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Booking Form</a>";
echo "</div>";
?>