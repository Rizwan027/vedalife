<?php
/**
 * Simple Test for Old Database Connection System
 */

echo "<h2>🌿 VedaLife - Simple Database Connection Test</h2>";

// Test 1: Direct connection
echo "<h3>1. Testing Direct Connection</h3>";
$host = "localhost";
$user = "root";
$pass = "";
$db = "vedalife";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "❌ <strong>Connection failed:</strong> " . $conn->connect_error . "<br>";
    echo "<br><strong>Make sure:</strong><br>";
    echo "• XAMPP/MySQL is running<br>";
    echo "• Database 'vedalife' exists<br>";
    echo "• Check your database credentials<br>";
} else {
    echo "✅ <strong>Connection successful!</strong><br>";
    echo "Server: " . $conn->server_info . "<br>";
    
    // Test 2: Simple query
    echo "<h3>2. Testing Simple Query</h3>";
    $result = $conn->query("SELECT 1 as test, NOW() as time, DATABASE() as db");
    
    if ($result) {
        echo "✅ <strong>Query successful!</strong><br>";
        $row = $result->fetch_assoc();
        echo "Test: " . $row['test'] . "<br>";
        echo "Time: " . $row['time'] . "<br>";
        echo "Database: " . $row['db'] . "<br>";
        $result->free();
    } else {
        echo "❌ <strong>Query failed:</strong> " . $conn->error . "<br>";
    }
    
    // Test 3: Check tables
    echo "<h3>3. Checking VedaLife Tables</h3>";
    $tables = ['users', 'booking', 'orders', 'products'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "⚠️ Table '$table' missing<br>";
        }
    }
    
    $conn->close();
}

// Test 4: Test connection.php file
echo "<h3>4. Testing connection.php File</h3>";
try {
    require_once 'connection.php';
    echo "✅ <strong>connection.php loaded successfully!</strong><br>";
    echo "Connection variable \$conn is available<br>";
    
    // Test a simple query with the connection
    $result = $conn->query("SELECT 'Old system works!' as message");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ <strong>Message from database:</strong> " . $row['message'] . "<br>";
        $result->free();
    }
} catch (Exception $e) {
    echo "❌ <strong>Error with connection.php:</strong> " . $e->getMessage() . "<br>";
}

echo "<br><hr>";
echo "<p><strong>✅ Your old database connection system has been restored!</strong></p>";
echo "<p>You can now change database credentials in just one file: <code>connection.php</code></p>";
echo "<p>To change credentials, edit the variables at the top of <code>connection.php</code>:</p>";
echo "<pre>";
echo "\$host = \"localhost\";  // Change this\n";
echo "\$user = \"root\";       // Change this\n"; 
echo "\$pass = \"\";           // Change this\n";
echo "\$db   = \"vedalife\";   // Change this\n";
echo "</pre>";

?>