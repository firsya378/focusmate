<?php
// config/config.php
session_start();

// Database configuration
define('DB_HOST', 'localhost'); // nanti ganti dengan RDS endpoint
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'focusmate_db');

// Base URL (ganti dengan domainmu nanti)
define('BASE_URL', 'https://namakamu.cloud2.my.id');

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting untuk production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');
