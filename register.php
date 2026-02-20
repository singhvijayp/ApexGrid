<?php
/**
 * Registration page
 * -----------------
 * Creates a new user with a securely hashed password.
 */

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/db.php';

// If already logged in, go to dashboard.
if (current_user()) {
  header('Location: ' . url('/dashboard.php'));
  exit;
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = coalesce(input($_POST, 'name'), '');
  $email = coalesce(input($_POST, 'email'), '');
  $password = coalesce(input($_POST, 'password'), '');
  $password2 = coalesce(input($_POST, 'password_confirm'), '');

  // Basic validation. (You can make this stricter if desired.)
  if ($name === '' || mb_strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters.';
  }
  if ($email === '' || !is_valid_email($email)) {
    $errors[] = 'Please enter a valid email address.';
  }
  if ($password === '' || strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters.';
  }
  if ($password !== $password2) {
    $errors[] = 'Password confirmation does not match.';
  }

  // Create user if validation passes.
  if (!$errors) {
    try {
      // Check for existing email (unique constraint also protects us).
      $stmt = db()->prepare('SELECT id FROM users WHERE email = :email');
      $stmt->execute([':email' => $email]);
      if ($stmt->fetch()) {
        $errors[] = 'That email is already registered. Please login instead.';
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)');
        $stmt->execute([
          ':name' => $name,
          ':email' => $email,
          ':hash' => $hash,
        ]);

        $userId = (int)db()->lastInsertId();
        login_user($userId);
        flash_set('success', 'Account created. Welcome!');
        header('Location: ' . url('/dashboard.php'));
        exit;
      }
    } catch (Exception $e) {
      // If DB is not set up yet, this message helps quickly.
      $errors[] = 'Registration failed. Please check database setup. (' . $e->getMessage() . ')';
    }
  }
}

render_header('Register');
?>

<div class="page-title">
  <h1>Create account</h1>
  <div class="subtitle">Register to manage teams, cars, drivers, and stats.</div>
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

    <form class="form" method="post" action="<?= e(url('/register.php')) ?>">
      <label>
        Full name
        <input type="text" name="name" value="<?= e($name) ?>" placeholder="e.g. Alex Driver" required>
      </label>

      <label>
        Email
        <input type="email" name="email" value="<?= e($email) ?>" placeholder="you@example.com" required>
      </label>

      <div class="grid grid--2">
        <label>
          Password
          <input type="password" name="password" placeholder="At least 6 characters" required>
        </label>

        <label>
          Confirm password
          <input type="password" name="password_confirm" required>
        </label>
      </div>

      <div class="actions">
        <button class="btn btn--primary" type="submit">Create account</button>
        <a class="btn" href="<?= e(url('/login.php')) ?>">I already have an account</a>
      </div>
    </form>
  </div>
</div>

<?php render_footer(); ?>

