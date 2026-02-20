<?php
/**
 * Authentication helpers
 * ----------------------
 * Keeps session login logic in one place.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Return the currently logged-in user row (or null).
 *
 * @return array|null
 */
function current_user(): ?array
{
  if (empty($_SESSION['user_id'])) {
    return null;
  }

  // Cache user in-session to avoid repeated DB calls in the same request.
  if (!empty($_SESSION['user_cache']) && is_array($_SESSION['user_cache'])) {
    return $_SESSION['user_cache'];
  }

  $stmt = db()->prepare('SELECT id, name, email, created_at FROM users WHERE id = :id');
  $stmt->execute([':id' => (int)$_SESSION['user_id']]);
  $user = $stmt->fetch();

  if (!$user) {
    // Session points to a user that no longer exists; log out for safety.
    unset($_SESSION['user_id'], $_SESSION['user_cache']);
    return null;
  }

  $_SESSION['user_cache'] = $user;
  return $user;
}

/**
 * Enforce that a user is logged in.
 * If not logged in, redirect to login page.
 *
 * @return void
 */
function require_login(): void
{
  if (!current_user()) {
    header('Location: ' . BASE_PATH . '/login.php');
    exit;
  }
}

/**
 * Log in by user ID (regenerates session ID for security).
 *
 * @param int $user_id
 * @return void
 */
function login_user(int $user_id): void
{
  // Prevent session fixation by regenerating the ID on login.
  session_regenerate_id(true);
  $_SESSION['user_id'] = $user_id;
  unset($_SESSION['user_cache']);
}

/**
 * Log out the current user.
 *
 * @return void
 */
function logout_user(): void
{
  unset($_SESSION['user_id'], $_SESSION['user_cache']);

  // Optional: fully destroy session cookie + data.
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }

  session_destroy();
}

