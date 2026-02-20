<?php
/**
 * Application configuration
 * -------------------------
 * Update these values to match your local MySQL settings in WAMP.
 *
 * IMPORTANT:
 * - This file contains credentials; do not commit it to a public repo.
 * - For learning projects, keeping config in PHP is fine.
 */

// Show errors while developing (turn OFF in production).
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Start session for authentication.
// (This must run before any output is sent to the browser.)
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// Database connection settings.
define('DB_HOST', 'localhost');
define('DB_NAME', 'apexgrid');
define('DB_USER', 'root');
define('DB_PASS', ''); // WAMP default is often empty; change if you set a password.
define('DB_CHARSET', 'utf8mb4');

// App settings.
define('APP_NAME', 'ApexGrid');

// Base URL helper (kept simple).
// If you place this project in a subfolder, set BASE_PATH accordingly.
// Example: if URL is http://localhost/apexgrid/cursor/ then BASE_PATH = '/apexgrid/cursor'
define('BASE_PATH', '/apex');

