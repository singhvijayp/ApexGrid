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
function db()
{
  static $pdo = null;

  if ($pdo instanceof PDO) {
    return $pdo;
  }

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  );

  $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
  // Ensure charset on older PHP/MySQL driver (WAMP 2.4)
  $pdo->exec('SET NAMES ' . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'));
  return $pdo;
}

