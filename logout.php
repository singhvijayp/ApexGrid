<?php
/**
 * Logout endpoint
 * ---------------
 * Destroys session and returns to login.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logout_user();
flash_set('info', 'You have been logged out.');
header('Location: ' . url('/login.php'));
exit;

