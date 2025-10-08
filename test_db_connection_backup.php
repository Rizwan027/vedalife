<?php
/**
 * VedaLife - Database Connection Test
 * 
 * This file tests the centralized database connection system.
 * Run this file in your browser to verify database connectivity.
 * 
 * URL: http://localhost/VedaLife/test_db_connection.php
 */

// Include the bootstrap file
require_once 'includes/bootstrap.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - VedaLife</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #f5f5f5;
        }
        .test-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
        .info {
            color: #0c5460;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
        .code {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 1rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        th, td {
            padding: 0.5rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🌿 VedaLife - Database Connection Test</h1>
        
        <?php
        // Test 1: Basic Connection Test
        echo "<h2>1. Basic Connection Test</h2>";
        
        try {
            $conn = getDbConnection();
            if ($conn && !$conn->connect_error) {
                echo "<div class='success'>✅ <strong>SUCCESS:</strong> Database connection established successfully!</div>";
            } else {
                echo "<div class='error'>❌ <strong>ERROR:</strong> Failed to connect to database.</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ <strong>EXCEPTION:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        // Test 2: Connection Statistics
        echo "<h2>2. Connection Statistics</h2>";
        try {
            $stats = DatabaseConnection::getConnectionStats();
            echo "<table>";
            foreach ($stats as $key => $value) {
                echo "<tr><th>" . ucfirst(str_replace('_', ' ', $key)) . "</th><td>" . htmlspecialchars($value ?? 'N/A') . "</td></tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ <strong>ERROR:</strong> Could not get connection stats: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        // Test 3: Query Test
        echo "<h2>3. Query Test</h2>";
        try {
            $result = dbQuery("SELECT 1 as test_value, NOW() as current_time, DATABASE() as database_name");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "<div class='success'>✅ <strong>SUCCESS:</strong> Query executed successfully!</div>";
                echo "<table>";
                foreach ($row as $key => $value) {
                    echo "<tr><th>" . htmlspecialchars($key) . "</th><td>" . htmlspecialchars($value) . "</td></tr>";
                }
                echo "</table>";
                $result->free();
            } else {
                echo "<div class='error'>❌ <strong>ERROR:</strong> Query failed or returned no results.</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ <strong>ERROR:</strong> Query test failed: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        // Test 4: Table Existence Check
        echo "<h2>4. VedaLife Tables Check</h2>";
        $tables = ['users', 'booking', 'orders', 'products', 'admin_users'];
        
        foreach ($tables as $table) {
            try {
                $result = dbQuery("SHOW TABLES LIKE '$table'");
                if ($result && $result->num_rows > 0) {
                    $count_result = dbQuery("SELECT COUNT(*) as count FROM $table");
                    $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
                    echo "<div class='success'>✅ Table '$table' exists ($count records)</div>";
                    if ($count_result) $count_result->free();
                } else {
                    echo "<div class='error'>❌ Table '$table' does not exist</div>";
                }
                if ($result) $result->free();
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error checking table '$table': " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
        
        // Test 5: Prepared Statement Test
        echo "<h2>5. Prepared Statement Test</h2>";
        try {
            $result = dbPreparedQuery("SELECT ? as test_param, ? as test_string", [42, 'Hello World'], 'is');
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "<div class='success'>✅ <strong>SUCCESS:</strong> Prepared statement executed successfully!</div>";
                echo "<table>";
                foreach ($row as $key => $value) {
                    echo "<tr><th>" . htmlspecialchars($key) . "</th><td>" . htmlspecialchars($value) . "</td></tr>";
                }
                echo "</table>";
                $result->free();
            } else {
                echo "<div class='error'>❌ <strong>ERROR:</strong> Prepared statement failed.</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ <strong>ERROR:</strong> Prepared statement test failed: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        // Usage Examples
        echo "<h2>6. Usage Examples</h2>";
        echo "<div class='info'><strong>How to use in your PHP files:</strong></div>";
        echo "<div class='code'>";
        echo htmlspecialchars("<?php
// Method 1: Include bootstrap (recommended)
require_once 'includes/bootstrap.php';

// Method 2: Include just the database connection
require_once 'includes/db_connection.php';

// Usage examples:

// Simple query
\$result = dbQuery(\"SELECT * FROM users\");

// Prepared query
\$user = dbGetRow(\"SELECT * FROM users WHERE id = ?\", [123], \"i\");

// Get all rows
\$users = dbGetAll(\"SELECT * FROM users WHERE active = ?\", [1], \"i\");

// Insert data
\$userId = dbInsert('users', [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT)
]);

// Update data
\$success = dbUpdate('users', 
    ['email' => 'newemail@example.com'], 
    ['id' => 123]
);

// Direct connection access (if needed)
\$conn = getDbConnection();
\$result = \$conn->query(\"SELECT * FROM users\");
?>");
        echo "</div>";
        
        echo "<h2>7. Configuration</h2>";
        echo "<div class='info'><strong>To change database credentials:</strong></div>";
        echo "<div class='code'>";
        echo "Edit the file: <strong>includes/db_connection.php</strong><br>";
        echo "Look for the DatabaseConfig class and update the \$config array:<br><br>";
        echo htmlspecialchars("private static \$config = [
    'host' => 'localhost',        // Change this
    'username' => 'root',         // Change this
    'password' => '',             // Change this
    'database' => 'vedalife',     // Change this
    'charset' => 'utf8mb4',
    'port' => 3306
];");
        echo "</div>";
        ?>
        
        <div style="margin-top: 2rem; padding: 1rem; background-color: #f8f9fa; border-radius: 4px;">
            <strong>🔒 Security Note:</strong> Delete this test file (test_db_connection.php) after testing is complete to prevent unauthorized access to your database information.
        </div>
    </div>
</body>
</html>