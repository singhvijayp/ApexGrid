<?php
/**
 * Login page
 * ----------
 * Validates credentials and starts a session.
 */

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/db.php';

// If already logged in, go to dashboard.
if (current_user()) {
  header('Location: ' . url('/dashboard.php'));
  exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = input($_POST, 'email') ?? '';
  $password = input($_POST, 'password') ?? '';

  if ($email === '' || !is_valid_email($email)) {
    $errors[] = 'Please enter a valid email address.';
  }
  if ($password === '') {
    $errors[] = 'Please enter your password.';
  }

  if (!$errors) {
    try {
      $stmt = db()->prepare('SELECT id, password_hash, name FROM users WHERE email = :email');
      $stmt->execute([':email' => $email]);
      $user = $stmt->fetch();

      if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Invalid email or password.';
      } else {
        login_user((int)$user['id']);
        flash_set('success', 'Welcome back, ' . $user['name'] . '!');
        header('Location: ' . url('/dashboard.php'));
        exit;
      }
    } catch (Throwable $e) {
      $errors[] = 'Login failed. Please check database setup. (' . $e->getMessage() . ')';
    }
  }
}

render_header('Login');
?>

<div class="page-title">
  <h1>Login</h1>
  <div class="subtitle">Access your motorsports management dashboard.</div>
</div>

<div class="panel">
  <div class="panel__body">
    <?php if ($errors): ?>
      <div class="alert alert--error">
        <strong>Please fix the following:</strong>
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(url('/login.php')) ?>">
      <label>
        Email
        <input type="email" name="email" value="<?= e($email) ?>" placeholder="you@example.com" required>
      </label>

      <label>
        Password
        <input type="password" name="password" required>
      </label>

      <div class="actions">
        <button class="btn btn--primary" type="submit">Login</button>
        <a class="btn" href="<?= e(url('/register.php')) ?>">Create account</a>
      </div>
    </form>
  </div>
</div>

<?php render_footer(); ?>

