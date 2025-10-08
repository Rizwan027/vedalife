<?php
/**
 * VedaLife - Simple Database Connection Diagnostic
 * 
 * This file tests the database connection with detailed error reporting
 */

echo "<h2>VedaLife Database Connection Diagnostic</h2>";

// Step 1: Test basic mysqli availability
echo "<h3>Step 1: Check MySQLi Extension</h3>";
if (extension_loaded('mysqli')) {
    echo "✅ MySQLi extension is loaded<br>";
} else {
    echo "❌ MySQLi extension is NOT loaded<br>";
    echo "Please enable MySQLi in your PHP configuration<br>";
    exit;
}

// Step 2: Test basic connection with manual parameters
echo "<h3>Step 2: Test Basic Connection</h3>";
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'vedalife';
$port = 3306;

echo "Attempting to connect to:<br>";
echo "Host: $host<br>";
echo "Username: $username<br>";
echo "Database: $database<br>";
echo "Port: $port<br><br>";

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    echo "❌ <strong>Connection failed:</strong><br>";
    echo "Error: " . $conn->connect_error . "<br>";
    echo "Error Code: " . $conn->connect_errno . "<br><br>";
    
    echo "<strong>Common Solutions:</strong><br>";
    echo "• Make sure XAMPP/MySQL is running<br>";
    echo "• Check if the 'vedalife' database exists<br>";
    echo "• Verify database credentials<br>";
    echo "• Check MySQL port (default 3306)<br>";
} else {
    echo "✅ <strong>Basic connection successful!</strong><br>";
    echo "Server Info: " . $conn->server_info . "<br>";
    echo "Client Info: " . $conn->client_info . "<br>";
    echo "Host Info: " . $conn->host_info . "<br><br>";
    
    // Step 3: Test simple query (avoid reserved alias names)
    echo "<h3>Step 3: Test Simple Query</h3>";
    $result = $conn->query("SELECT 1 AS test_value, NOW() AS current_ts, DATABASE() AS db_name");
    
    if ($result) {
        echo "✅ <strong>Query successful!</strong><br>";
        $row = $result->fetch_assoc();
        echo "Test Value: " . $row['test_value'] . "<br>";
        echo "Current Time: " . $row['current_ts'] . "<br>";
        echo "Database Name: " . $row['db_name'] . "<br>";
        $result->free();
    } else {
        echo "❌ <strong>Query failed:</strong><br>";
        echo "Error: " . $conn->error . "<br>";
    }
    
    // Step 4: Check for VedaLife tables
    echo "<h3>Step 4: Check VedaLife Tables</h3>";
    $tables = ['users', 'booking', 'orders', 'products', 'admin_users'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
            echo "✅ Table '$table' exists ($count records)<br>";
        } else {
            echo "❌ Table '$table' does not exist<br>";
        }
    }
    
    $conn->close();
}

echo "<br><h3>Step 5: Test Centralized Connection System</h3>";

try {
    require_once 'includes/bootstrap.php';
    echo "✅ Bootstrap file loaded successfully<br>";
    
    $centralConn = getDbConnection();
    
    if ($centralConn) {
        echo "✅ <strong>Centralized connection successful!</strong><br>";
        
        // Test the dbQuery helper function (avoid reserved alias names)
        $result = dbQuery("SELECT 1 AS test_value, NOW() AS current_ts");
        if ($result && $result->num_rows > 0) {
            echo "✅ <strong>Helper function dbQuery() works!</strong><br>";
            $row = $result->fetch_assoc();
            echo "Test Value: " . $row['test_value'] . "<br>";
            echo "Current Time: " . $row['current_ts'] . "<br>";
            $result->free();
        } else {
            echo "❌ <strong>Helper function dbQuery() failed</strong><br>";
        }
        
        // Test dbGetRow helper function
        $row = dbGetRow("SELECT 2 as test_value, 'Hello World' as message");
        if ($row) {
            echo "✅ <strong>Helper function dbGetRow() works!</strong><br>";
            echo "Test Value: " . $row['test_value'] . "<br>";
            echo "Message: " . $row['message'] . "<br>";
        } else {
            echo "❌ Helper function dbGetRow() failed<br>";
        }
        
    } else {
        echo "❌ <strong>Centralized connection failed</strong><br>";
        echo "Check the error messages above for details<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error testing centralized system:</strong><br>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<br><hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>If basic connection works but centralized doesn't, check includes/db_connection.php</li>";
echo "<li>If no connection works, check XAMPP/MySQL service</li>";
echo "<li>If tables don't exist, import your database schema</li>";
echo "<li>Delete this diagnostic file after resolving issues</li>";
echo "</ul>";

?>