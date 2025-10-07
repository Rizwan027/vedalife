<?php
// Test script to verify appointment workflow
require_once 'admin_auth.php';

$conn = getAdminDbConnection();

echo "<h2>Appointment Workflow Test</h2>";

// Show current appointment counts
echo "<h3>Current Dashboard Stats:</h3>";

$statsTotalAll = 0; $statsPending = 0; $statsUpcoming = 0; $statsCompleted = 0;

$__r = $conn->query("SELECT COUNT(*) AS c FROM booking");
if ($__r) { $row = $__r->fetch_assoc(); $statsTotalAll = (int)($row['c'] ?? 0); }

$__r2 = $conn->query("SELECT COUNT(*) AS c FROM booking WHERE status = 'pending'");
if ($__r2) { $row = $__r2->fetch_assoc(); $statsPending = (int)($row['c'] ?? 0); }

$__r3 = $conn->query("SELECT COUNT(*) AS c FROM booking WHERE status = 'confirmed' AND preferred_date >= CURDATE()");
if ($__r3) { $row = $__r3->fetch_assoc(); $statsUpcoming = (int)($row['c'] ?? 0); }

$__r4 = $conn->query("SELECT COUNT(*) AS c FROM booking WHERE status = 'completed'");
if ($__r4) { $row = $__r4->fetch_assoc(); $statsCompleted = (int)($row['c'] ?? 0); }

echo "<ul>";
echo "<li>Total Appointments: " . $statsTotalAll . "</li>";
echo "<li>Pending Appointments: " . $statsPending . "</li>";
echo "<li>Confirmed Appointments: " . $statsUpcoming . "</li>";
echo "<li>Completed Appointments: " . $statsCompleted . "</li>";
echo "</ul>";

// Show cancelled appointments that will be auto-deleted
echo "<h3>Cancelled Appointments (to be auto-deleted):</h3>";
$cancelled = $conn->query("SELECT COUNT(*) AS c FROM booking WHERE status = 'cancelled' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($cancelled) {
    $row = $cancelled->fetch_assoc();
    echo "<p>Appointments older than 7 days to be deleted: " . $row['c'] . "</p>";
}

// Show recent appointments by status
echo "<h3>Recent Appointments by Status:</h3>";
$recent = $conn->query("SELECT status, COUNT(*) as count FROM booking WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY status ORDER BY count DESC");

if ($recent && $recent->num_rows > 0) {
    echo "<ul>";
    while($row = $recent->fetch_assoc()) {
        echo "<li>" . ucfirst($row['status']) . ": " . $row['count'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No recent appointments found.</p>";
}

// Workflow explanation
echo "<h3>Appointment Workflow:</h3>";
echo "<ol>";
echo "<li><strong>New Appointment</strong> → Status: 'pending' (shows in Pending count)</li>";
echo "<li><strong>Accept Appointment</strong> → Status: 'confirmed' (moves to Confirmed count, shows future dates)</li>";
echo "<li><strong>Reject Appointment</strong> → Status: 'cancelled' (will be auto-deleted after 7 days)</li>";
echo "<li><strong>Complete Appointment</strong> → Status: 'completed' (shows in Completed count)</li>";
echo "</ol>";

$conn->close();
?>