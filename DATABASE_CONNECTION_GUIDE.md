# VedaLife - Centralized Database Connection System

## Overview

The VedaLife project now uses a centralized database connection system that provides:

- **Single point of configuration** - Change database credentials in one place
- **Enhanced security** - Built-in SQL injection protection and secure connection handling
- **Better error handling** - Comprehensive logging and graceful error management
- **Performance optimizations** - Connection pooling and efficient query execution
- **Easy to use helper functions** - Simplified database operations

## Quick Start

### 1. Include the Bootstrap File

At the beginning of any PHP file that needs database access, include:

```php
<?php
require_once 'includes/bootstrap.php';
```

### 2. Basic Usage Examples

```php
<?php
require_once 'includes/bootstrap.php';

// Simple query
$result = dbQuery("SELECT * FROM users");

// Prepared query (recommended for user input)
$user = dbGetRow("SELECT * FROM users WHERE id = ?", [123], "i");

// Get all rows
$users = dbGetAll("SELECT * FROM users WHERE active = ?", [1], "i");

// Insert data
$userId = dbInsert('users', [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT)
]);

// Update data
$success = dbUpdate('users', 
    ['email' => 'newemail@example.com'], 
    ['id' => 123]
);

// Direct connection access (if needed)
$conn = getDbConnection();
$result = $conn->query("SELECT * FROM users");
```

## Configuration

### Changing Database Credentials

To update database credentials for the entire project, edit **one file only**:

**File:** `includes/db_connection.php`

Look for the `DatabaseConfig` class and update the `$config` array:

```php
private static $config = [
    'host' => 'localhost',        // Your database host
    'username' => 'root',         // Your database username
    'password' => '',             // Your database password
    'database' => 'vedalife',     // Your database name
    'charset' => 'utf8mb4',       // Character set
    'port' => 3306                // Database port
];
```

### Environment-Specific Settings

You can modify settings for different environments by editing `includes/config.php`:

```php
// For development
define('VEDALIFE_DEBUG', true);
define('VEDALIFE_LOG_DB', true);

// For production
define('VEDALIFE_DEBUG', false);
define('VEDALIFE_LOG_DB', false);
```

## Available Functions

### Connection Functions

| Function | Description |
|----------|-------------|
| `getDbConnection()` | Get the database connection instance |
| `DatabaseConnection::testConnection()` | Test if database connection works |
| `DatabaseConnection::getConnectionStats()` | Get connection statistics |

### Query Functions

| Function | Parameters | Description |
|----------|------------|-------------|
| `dbQuery($sql)` | SQL string | Execute a simple query |
| `dbPreparedQuery($sql, $params, $types)` | SQL with placeholders, parameters array, type string | Execute prepared query |
| `dbGetRow($sql, $params, $types)` | SQL, parameters, types | Get single row as array |
| `dbGetAll($sql, $params, $types)` | SQL, parameters, types | Get all rows as array |

### Data Manipulation Functions

| Function | Parameters | Description |
|----------|------------|-------------|
| `dbInsert($table, $data)` | Table name, data array | Insert data, returns ID |
| `dbUpdate($table, $data, $where)` | Table, data array, where conditions | Update data |

### Transaction Functions

| Function | Description |
|----------|-------------|
| `DatabaseConnection::beginTransaction()` | Start transaction |
| `DatabaseConnection::commitTransaction()` | Commit transaction |
| `DatabaseConnection::rollbackTransaction()` | Rollback transaction |

## Parameter Types for Prepared Statements

When using prepared statements, specify parameter types:

- `'s'` - String
- `'i'` - Integer  
- `'d'` - Double/Float
- `'b'` - Blob

Example:
```php
// Mixed parameter types
$result = dbPreparedQuery(
    "SELECT * FROM users WHERE age > ? AND name LIKE ? AND score = ?",
    [18, 'John%', 95.5],
    "iss"  // integer, string, string
);
```

## Security Features

### 1. Prepared Statements
All helper functions use prepared statements to prevent SQL injection:

```php
// Secure - uses prepared statements
$user = dbGetRow("SELECT * FROM users WHERE email = ?", [$email], "s");

// Avoid - direct concatenation (vulnerable)
$result = dbQuery("SELECT * FROM users WHERE email = '$email'");
```

### 2. Connection Security
- Connection timeout settings
- Secure charset configuration
- Error message sanitization in production
- Session security enhancements

### 3. Error Handling
- Errors are logged instead of displayed in production
- Graceful fallbacks for connection failures
- Detailed logging for debugging

## Testing Your Connection

### 1. Run the Test Script

Visit: `http://localhost/VedaLife/test_db_connection.php`

This will test:
- Basic connection
- Query execution
- Prepared statements
- Table existence
- Connection statistics

### 2. Manual Testing

```php
<?php
require_once 'includes/bootstrap.php';

if (DatabaseConnection::testConnection()) {
    echo "✅ Database connection working!";
} else {
    echo "❌ Database connection failed!";
}
?>
```

## Migration from Old System

### Files Already Updated

The following files have been updated to use the new system:
- `booking.php`
- `my_bookings.php` 
- `profile.php`
- `my_orders.php`
- `login.php`
- `auth_check.php`

### Files That Still Need Updates

You may need to update other files manually. Look for:

```php
// Old pattern to replace:
$conn = new mysqli($host, $user, $pass, $db);

// Replace with:
require_once 'includes/bootstrap.php';
$conn = getDbConnection();
```

## Troubleshooting

### Common Issues

1. **"Database connection failed"**
   - Check credentials in `includes/db_connection.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **"File not found" errors**
   - Ensure all files are in correct locations
   - Check file permissions
   - Verify include paths

3. **Prepared statement errors**
   - Check parameter count matches placeholder count
   - Verify parameter types string is correct
   - Ensure parameters array is not empty when types specified

### Debug Mode

Enable debug mode for detailed error messages:

Edit `includes/config.php`:
```php
define('VEDALIFE_DEBUG', true);
```

### Logging

Enable database activity logging:

Edit `includes/config.php`:
```php
define('VEDALIFE_LOG_DB', true);
```

Check error logs in your server's error log file.

## Best Practices

### 1. Always Use Prepared Statements for User Input
```php
// Good
$user = dbGetRow("SELECT * FROM users WHERE id = ?", [$userId], "i");

// Bad
$user = dbQuery("SELECT * FROM users WHERE id = $userId");
```

### 2. Handle Errors Gracefully
```php
$result = dbQuery("SELECT * FROM users");
if ($result === false) {
    // Handle error appropriately
    error_log("Failed to fetch users");
    return false;
}
```

### 3. Free Resources
```php
$result = dbQuery("SELECT * FROM large_table");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Process row
    }
    $result->free(); // Free memory
}
```

### 4. Use Transactions for Related Operations
```php
DatabaseConnection::beginTransaction();

try {
    dbInsert('orders', $orderData);
    $orderId = DatabaseConnection::getLastInsertId();
    
    foreach ($items as $item) {
        $item['order_id'] = $orderId;
        dbInsert('order_items', $item);
    }
    
    DatabaseConnection::commitTransaction();
} catch (Exception $e) {
    DatabaseConnection::rollbackTransaction();
    throw $e;
}
```

## Support

For issues or questions about the database connection system:

1. Check this documentation
2. Run the test script (`test_db_connection.php`)
3. Check server error logs
4. Review the code in `includes/db_connection.php`

## Security Notes

1. **Delete test files** after testing:
   - `test_db_connection.php`

2. **Use environment variables** for sensitive data in production

3. **Enable SSL** for database connections in production

4. **Regularly update credentials** and use strong passwords

5. **Monitor database logs** for suspicious activity