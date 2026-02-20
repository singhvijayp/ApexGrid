<?php
/**
 * Password hashing compatibility for PHP 5.4 (WAMP 2.4 64-bit).
 * Provides password_hash() and password_verify() using crypt() when not available (PHP 5.5+).
 */
if (!defined('PASSWORD_DEFAULT')) {
  define('PASSWORD_DEFAULT', 1);
}
if (!defined('PASSWORD_BCRYPT')) {
  define('PASSWORD_BCRYPT', 1);
}
if (!function_exists('password_hash')) {
  function password_hash($password, $algo, array $options = array()) {
    if ($algo !== PASSWORD_DEFAULT && $algo !== PASSWORD_BCRYPT) {
      trigger_error('password_hash(): Unknown hashing algorithm.', E_USER_WARNING);
      return null;
    }
    $cost = isset($options['cost']) ? (int) $options['cost'] : 10;
    $cost = max(4, min(31, $cost));
    $salt = substr(str_replace('+', '.', base64_encode(openssl_random_pseudo_bytes(16))), 0, 22);
    $hash = crypt($password, '$2y$' . str_pad($cost, 2, '0', STR_PAD_LEFT) . '$' . $salt);
    return $hash;
  }
}
if (!function_exists('password_verify')) {
  function password_verify($password, $hash) {
    return crypt($password, $hash) === $hash;
  }
}
