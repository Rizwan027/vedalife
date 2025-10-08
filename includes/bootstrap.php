<?php
/**
 * VedaLife - Application Bootstrap
 * 
 * This file initializes the core application components.
 * Include this file at the beginning of any script that needs database access.
 * 
 * Usage:
 *   require_once 'includes/bootstrap.php';
 * 
 * @author VedaLife Development Team
 * @version 2.0
 */

// Include configuration
require_once __DIR__ . '/config.php';

// Include database connection
require_once __DIR__ . '/db_connection.php';

// Include any additional core files here as needed
// require_once __DIR__ . '/auth_helper.php';
// require_once __DIR__ . '/validation_helper.php';

// Application is now ready to use
// You can access database via:
//   - $conn = getDbConnection();
//   - dbQuery("SELECT * FROM table");
//   - dbGetRow("SELECT * FROM table WHERE id = ?", [1], "i");

?>