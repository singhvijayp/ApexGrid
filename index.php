<?php
/**
 * App entrypoint
 * --------------
 * Redirect users based on authentication state.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
  header('Location: ' . url('/dashboard.php'));
  exit;
}

header('Location: ' . url('/login.php'));
exit;

