<?php
/**
 * General-purpose helpers
 * ----------------------
 * These helpers keep templates clean and consistent.
 */

require_once __DIR__ . '/config.php';

/**
 * HTML-escape output to prevent XSS.
 *
 * @param string|null $value
 * @return string
 */
function e($value)
{
  return htmlspecialchars((string)$value, ENT_QUOTES | (defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0), 'UTF-8');
}

/**
 * Build a URL relative to the project BASE_PATH.
 *
 * @param string $path
 * @return string
 */
function url($path)
{
  $path = '/' . ltrim($path, '/');
  return BASE_PATH . $path;
}

/**
 * Store a one-time "flash" message (shows once, then disappears).
 *
 * @param string $type e.g. 'success', 'error', 'info'
 * @param string $message
 * @return void
 */
function flash_set($type, $message)
{
  $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear the current flash message.
 *
 * @return array|null
 */
function flash_get()
{
  if (empty($_SESSION['flash'])) {
    return null;
  }
  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
  return $flash;
}

/**
 * Validate an email address.
 *
 * @param string $email
 * @return bool
 */
function is_valid_email($email)
{
  return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Simple input fetch helper: trims strings and returns null for missing keys.
 *
 * @param array $source Typically $_POST or $_GET
 * @param string $key
 * @return string|null
 */
function input($source, $key)
{
  if (!array_key_exists($key, $source)) {
    return null;
  }
  $value = $source[$key];
  if (is_string($value)) {
    $value = trim($value);
  }
  return ($value === '') ? null : (string)$value;
}

/**
 * Null coalescing for PHP 5.4 (replacement for ?? operator).
 *
 * @param mixed $value
 * @param mixed $default
 * @return mixed
 */
function coalesce($value, $default)
{
  return isset($value) ? $value : $default;
}

