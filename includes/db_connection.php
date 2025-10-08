<?php
/**
 * VedaLife - Simple DB Wrapper (using original connection.php)
 * 
 * This file adapts the original simple mysqli connection to the helper
 * functions and DatabaseConnection API expected by the codebase.
 *
 * Chosen Option B: keep includes/bootstrap.php entry-point, but use
 * the simple $conn from /connection.php behind the scenes.
 */

// Ensure app constant (compatibility with includes/config.php)
if (!defined('VEDALIFE_APP')) {
    define('VEDALIFE_APP', true);
}

// Load the original simple connection (defines $conn)
// Adjust path only if you move files; this assumes project root structure
require_once dirname(__DIR__) . '/connection.php';

// Safety check
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not initialized. Please verify connection.php');
}

/**
 * Compatibility class implementing the minimal DatabaseConnection API
 * on top of the global $conn from connection.php
 */
class DatabaseConnection {
    public static function getInstance() {
        global $conn;
        return $conn;
    }

    public static function testConnection() {
        try {
            $c = self::getInstance();
            $r = $c->query('SELECT 1 as test');
            if ($r) {
                $row = $r->fetch_assoc();
                $r->free();
                return isset($row['test']) && (int)$row['test'] === 1;
            }
            return false;
        } catch (Throwable $e) {
            error_log('[VedaLife DB ERROR] ' . $e->getMessage());
            return false;
        }
    }

    public static function getConnectionStats() {
        $c = self::getInstance();
        return [
            'connection_count' => 1,
            'thread_id' => $c ? $c->thread_id : null,
            'server_info' => $c ? $c->server_info : null,
            'client_info' => $c ? $c->client_info : null,
        ];
    }

    public static function executeQuery($query, $params = [], $types = '') {
        global $conn;
        try {
            if (empty($params)) {
                return $conn->query($query);
            }
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            if (!empty($types) && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            if (!$stmt->execute()) {
                $err = $stmt->error;
                $stmt->close();
                throw new Exception('Execute failed: ' . $err);
            }
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        } catch (Throwable $e) {
            error_log('[VedaLife DB ERROR] ' . $e->getMessage() . ' | Query: ' . $query);
            return false;
        }
    }

    public static function getLastInsertId() {
        $c = self::getInstance();
        return $c ? $c->insert_id : 0;
    }

    public static function getAffectedRows() {
        $c = self::getInstance();
        return $c ? $c->affected_rows : 0;
    }

    public static function beginTransaction() {
        $c = self::getInstance();
        return $c ? $c->begin_transaction() : false;
    }

    public static function commitTransaction() {
        $c = self::getInstance();
        return $c ? $c->commit() : false;
    }

    public static function rollbackTransaction() {
        $c = self::getInstance();
        return $c ? $c->rollback() : false;
    }

    public static function closeConnection() {
        global $conn;
        if ($conn && !@$conn->connect_error) {
            @$conn->close();
        }
    }
}

// Helper functions (same signatures as documented)
function getDbConnection() {
    return DatabaseConnection::getInstance();
}

function dbQuery($query) {
    return DatabaseConnection::executeQuery($query);
}

function dbPreparedQuery($query, $params = [], $types = '') {
    return DatabaseConnection::executeQuery($query, $params, $types);
}

function dbGetRow($query, $params = [], $types = '') {
    $result = DatabaseConnection::executeQuery($query, $params, $types);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row;
    }
    return null;
}

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

function dbInsert($table, $data) {
    if (empty($data)) return false;
    $columns = implode(',', array_keys($data));
    $placeholders = str_repeat('?,', count($data) - 1) . '?';
    $query = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
    $types = '';
    $values = [];
    foreach ($data as $value) {
        $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        $values[] = $value;
    }
    $result = DatabaseConnection::executeQuery($query, $values, $types);
    return $result ? DatabaseConnection::getLastInsertId() : false;
}

function dbUpdate($table, $data, $where) {
    if (empty($data) || empty($where)) return false;
    $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
    $whereClause = implode(' = ? AND ', array_keys($where)) . ' = ?';
    $query = "UPDATE `{$table}` SET {$setClause} WHERE {$whereClause}";
    $types = '';
    $values = [];
    foreach ($data as $value) {
        $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        $values[] = $value;
    }
    foreach ($where as $value) {
        $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        $values[] = $value;
    }
    $result = DatabaseConnection::executeQuery($query, $values, $types);
    return $result !== false;
}

// No auto-connect magic needed since connection.php already created $conn
// Keep shutdown cleanup for parity
register_shutdown_function(function() {
    // Optional: comment out if you prefer to keep persistent connections during script lifetime only
    // DatabaseConnection::closeConnection();
});

?>
