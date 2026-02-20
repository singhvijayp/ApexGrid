<?php
/**
 * Database connection (PDO)
 * -------------------------
 * This file returns a singleton PDO connection.
 * We use prepared statements everywhere to prevent SQL injection.
 */

require_once __DIR__ . '/config.php';

/**
 * Get a shared PDO connection instance.
 *
 * @return PDO
 */
function db(): PDO
{
  static $pdo = null;

  if ($pdo instanceof PDO) {
    return $pdo;
  }

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

  $options = [
    // Throw exceptions on errors (easier to debug).
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // Fetch associative arrays by default (nice for templates).
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Use native prepared statements.
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

  $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
  return $pdo;
}

