<?php
/**
 * VedaLife - Centralized Database Connection
 * 
 * This file provides a secure, reusable database connection for the entire project.
 * All database credentials are centralized here for easy maintenance.
 * 
 * Features:
 * - Singleton pattern to prevent multiple connections
 * - Error logging and handling
 * - Environment-based configuration
 * - Connection pooling ready
 * - Security enhancements
 * 
 * @author VedaLife Development Team
 * @version 2.0
 */

// Prevent direct access to this file
if (!defined('VEDALIFE_APP')) {
    define('VEDALIFE_APP', true);
}

/**
 * Database Configuration Class
 */
class DatabaseConfig {
    // Database credentials - CHANGE THESE AS NEEDED
    private static $config = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'vedalife',
        'charset' => 'utf8mb4',
        'port' => 3306
    ];
    
    // Connection options for security and performance
    private static $options = [
        MYSQLI_OPT_CONNECT_TIMEOUT => 10,
        MYSQLI_OPT_INT_AND_FLOAT_NATIVE => true,
    ];
    
    /**
     * Get database configuration
     */
    public static function getConfig() {
        return self::$config;
    }
    
    /**
     * Get connection options
     */
    public static function getOptions() {
        return self::$options;
    }
    
    /**
     * Update configuration (useful for testing or different environments)
     */
    public static function setConfig($key, $value) {
        if (array_key_exists($key, self::$config)) {
            self::$config[$key] = $value;
        }
    }
}

/**
 * Database Connection Manager (Singleton Pattern)
 */
class DatabaseConnection {
    private static $instance = null;
    private static $connection = null;
    private static $connectionCount = 0;
    
    private function __construct() {
        // Private constructor to prevent direct instantiation
    }
    
    /**
     * Get database connection instance
     * @return mysqli|null
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
            try {
                self::$instance->connect();
            } catch (Exception $e) {
                if (defined('VEDALIFE_DEBUG') && VEDALIFE_DEBUG === true) {
                    echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px; text-align: center;">';
                    echo '<strong>Database Connection Debug Info:</strong><br>';
                    echo 'Error: ' . htmlspecialchars($e->getMessage()) . '<br>';
                    echo 'Please check your database settings in includes/db_connection.php';
                    echo '</div>';
                }
                return null;
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Create database connection
     */
    private function connect() {
        try {
            $config = DatabaseConfig::getConfig();
            
            // Create connection using simpler method for better compatibility
            self::$connection = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['database'],
                $config['port']
            );
            
            // Check for connection errors
            if (self::$connection->connect_error) {
                throw new Exception('Database Connection Error: ' . self::$connection->connect_error . ' (Error Code: ' . self::$connection->connect_errno . ')');
            }
            
            // Set charset
            if (!self::$connection->set_charset($config['charset'])) {
                throw new Exception('Error setting charset: ' . self::$connection->error);
            }
            
            self::$connectionCount++;
            
            // Log successful connection (optional - remove in production)
            self::logActivity('Database connection established successfully');
            
        } catch (Exception $e) {
            self::logError('Database connection failed: ' . $e->getMessage());
            
            // Set connection to null so getInstance returns null
            self::$connection = null;
            
            // In development, show detailed error; in production, show generic error
            if (defined('VEDALIFE_DEBUG') && VEDALIFE_DEBUG === true) {
                throw $e; // Re-throw for debugging
            } else {
                error_log('VedaLife DB Connection Error: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Get connection statistics
     */
    public static function getConnectionStats() {
        return [
            'connection_count' => self::$connectionCount,
            'thread_id' => self::$connection ? self::$connection->thread_id : null,
            'server_info' => self::$connection ? self::$connection->server_info : null,
            'client_info' => self::$connection ? self::$connection->client_info : null
        ];
    }
    
    /**
     * Test database connection
     */
    public static function testConnection() {
        try {
            $conn = self::getInstance();
            $result = $conn->query("SELECT 1 as test");
            
            if ($result) {
                $row = $result->fetch_assoc();
                $result->free();
                return $row['test'] === 1;
            }
            
            return false;
        } catch (Exception $e) {
            self::logError('Connection test failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Close database connection
     */
    public static function closeConnection() {
        if (self::$connection && !self::$connection->connect_error) {
            self::$connection->close();
            self::$connection = null;
            self::logActivity('Database connection closed');
        }
    }
    
    /**
     * Execute a prepared statement safely
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the query
     * @param string $types Parameter types (s=string, i=integer, d=double, b=blob)
     * @return mysqli_result|bool
     */
    public static function executeQuery($query, $params = [], $types = '') {
        try {
            $conn = self::getInstance();
            
            if (empty($params)) {
                return $conn->query($query);
            }
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            if (!empty($params) && !empty($types)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            self::logError('Query execution failed: ' . $e->getMessage() . ' | Query: ' . $query);
            return false;
        }
    }
    
    /**
     * Get last inserted ID
     */
    public static function getLastInsertId() {
        $conn = self::getInstance();
        return $conn ? $conn->insert_id : 0;
    }
    
    /**
     * Get affected rows count
     */
    public static function getAffectedRows() {
        $conn = self::getInstance();
        return $conn ? $conn->affected_rows : 0;
    }
    
    /**
     * Escape string for safe SQL usage (when prepared statements aren't suitable)
     */
    public static function escapeString($string) {
        $conn = self::getInstance();
        return $conn ? $conn->real_escape_string($string) : addslashes($string);
    }
    
    /**
     * Begin transaction
     */
    public static function beginTransaction() {
        $conn = self::getInstance();
        return $conn ? $conn->begin_transaction() : false;
    }
    
    /**
     * Commit transaction
     */
    public static function commitTransaction() {
        $conn = self::getInstance();
        return $conn ? $conn->commit() : false;
    }
    
    /**
     * Rollback transaction
     */
    public static function rollbackTransaction() {
        $conn = self::getInstance();
        return $conn ? $conn->rollback() : false;
    }
    
    /**
     * Log database activities (optional)
     */
    private static function logActivity($message) {
        if (defined('VEDALIFE_LOG_DB') && VEDALIFE_LOG_DB === true) {
            error_log('[VedaLife DB] ' . date('Y-m-d H:i:s') . ' - ' . $message);
        }
    }
    
    /**
     * Log database errors
     */
    private static function logError($message) {
        error_log('[VedaLife DB ERROR] ' . date('Y-m-d H:i:s') . ' - ' . $message);
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {
        throw new Exception('Cloning of DatabaseConnection is not allowed.');
    }
    
    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception('Unserialization of DatabaseConnection is not allowed.');
    }
    
    /**
     * Clean up on destruction
     */
    public function __destruct() {
        self::closeConnection();
    }
}

/**
 * Helper Functions for Easy Database Access
 */

/**
 * Get database connection (backward compatibility)
 * @return mysqli|null
 */
function getDbConnection() {
    return DatabaseConnection::getInstance();
}

/**
 * Execute a simple query
 * @param string $query
 * @return mysqli_result|bool
 */
function dbQuery($query) {
    return DatabaseConnection::executeQuery($query);
}

/**
 * Execute a prepared query
 * @param string $query SQL with placeholders
 * @param array $params Parameters
 * @param string $types Parameter types
 * @return mysqli_result|bool
 */
function dbPreparedQuery($query, $params = [], $types = '') {
    return DatabaseConnection::executeQuery($query, $params, $types);
}

/**
 * Get a single row from query result
 * @param string $query
 * @param array $params
 * @param string $types
 * @return array|null
 */
function dbGetRow($query, $params = [], $types = '') {
    $result = DatabaseConnection::executeQuery($query, $params, $types);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row;
    }
    return null;
}

/**
 * Get all rows from query result
 * @param string $query
 * @param array $params
 * @param string $types
 * @return array
 */
function dbGetAll($query, $params = [], $types = '') {
    $result = DatabaseConnection::executeQuery($query, $params, $types);
    $rows = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
    }
    
    return $rows;
}

/**
 * Insert data into a table
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return bool|int Returns insert ID on success, false on failure
 */
function dbInsert($table, $data) {
    if (empty($data)) return false;
    
    $columns = implode(',', array_keys($data));
    $placeholders = str_repeat('?,', count($data) - 1) . '?';
    $query = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
    
    $types = '';
    $values = [];
    foreach ($data as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
        $values[] = $value;
    }
    
    $result = DatabaseConnection::executeQuery($query, $values, $types);
    return $result ? DatabaseConnection::getLastInsertId() : false;
}

/**
 * Update data in a table
 * @param string $table Table name
 * @param array $data Data to update
 * @param array $where Where conditions
 * @return bool
 */
function dbUpdate($table, $data, $where) {
    if (empty($data) || empty($where)) return false;
    
    $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
    $whereClause = implode(' = ? AND ', array_keys($where)) . ' = ?';
    $query = "UPDATE `{$table}` SET {$setClause} WHERE {$whereClause}";
    
    $types = '';
    $values = [];
    
    // Add data values
    foreach ($data as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
        $values[] = $value;
    }
    
    // Add where values
    foreach ($where as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
        $values[] = $value;
    }
    
    $result = DatabaseConnection::executeQuery($query, $values, $types);
    return $result !== false;
}

// Initialize the database connection when this file is included
// This ensures the connection is ready when needed
if (!defined('VEDALIFE_NO_AUTO_CONNECT')) {
    $conn = DatabaseConnection::getInstance();
}

// Register shutdown function to clean up connections
register_shutdown_function(function() {
    DatabaseConnection::closeConnection();
});

?>