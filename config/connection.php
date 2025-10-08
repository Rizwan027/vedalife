<?php
// Central DB connection with simple environment switch
$env = getenv('VEDALIFE_ENV') ?: 'local';

switch ($env) {
    case 'production':
        $host = getenv('VEDALIFE_DB_HOST') ?: 'localhost';
        $user = getenv('VEDALIFE_DB_USER') ?: 'root';
        $pass = getenv('VEDALIFE_DB_PASS') ?: '';
        $db   = getenv('VEDALIFE_DB_NAME') ?: 'vedalife';
        $port = getenv('VEDALIFE_DB_PORT') ?: null;
        break;
    case 'staging':
        $host = getenv('VEDALIFE_DB_HOST') ?: 'localhost';
        $user = getenv('VEDALIFE_DB_USER') ?: 'root';
        $pass = getenv('VEDALIFE_DB_PASS') ?: '';
        $db   = getenv('VEDALIFE_DB_NAME') ?: 'vedalife';
        $port = getenv('VEDALIFE_DB_PORT') ?: null;
        break;
    default: // local
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db   = 'vedalife';
        $port = null;
}

// Create connection (optionally with port)
if ($port) {
    $conn = new mysqli($host, $user, $pass, $db, (int)$port);
} else {
    $conn = new mysqli($host, $user, $pass, $db);
}

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8');
?>