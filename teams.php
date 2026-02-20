<?php
/**
 * Teams module (CRUD-lite)
 * -----------------------
 * - List all teams
 * - Add a new team
 * - Delete a team (only if no cars/drivers reference it due to FK RESTRICT)
 */

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/db.php';

require_login();

$errors = [];

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (input($_POST, 'action') === 'create_team')) {
  $name = input($_POST, 'name') ?? '';
  $base_country = input($_POST, 'base_country');
  $principal = input($_POST, 'principal');

  if ($name === '' || mb_strlen($name) < 2) {
    $errors[] = 'Team name must be at least 2 characters.';
  }

  if (!$errors) {
    try {
      $stmt = db()->prepare('INSERT INTO teams (name, base_country, principal) VALUES (:name, :base_country, :principal)');
      $stmt->execute([
        ':name' => $name,
        ':base_country' => $base_country,
        ':principal' => $principal,
      ]);
      flash_set('success', 'Team created.');
      header('Location: ' . url('/teams.php'));
      exit;
    } catch (Throwable $e) {
      $errors[] = 'Could not create team. (' . $e->getMessage() . ')';
    }
  }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (input($_POST, 'action') === 'delete_team')) {
  $id = (int)(input($_POST, 'id') ?? 0);
  if ($id > 0) {
    try {
      $stmt = db()->prepare('DELETE FROM teams WHERE id = :id');
      $stmt->execute([':id' => $id]);
      flash_set('info', 'Team deleted (if not referenced by cars/drivers).');
      header('Location: ' . url('/teams.php'));
      exit;
    } catch (Throwable $e) {
      // If FK restrict blocks deletion, MySQL will throw an error.
      $errors[] = 'Could not delete team. Remove related cars/drivers first. (' . $e->getMessage() . ')';
    }
  }
}

// Fetch teams
$teams = [];
try {
  $teams = db()->query('SELECT id, name, base_country, principal, created_at FROM teams ORDER BY name ASC')->fetchAll();
} catch (Throwable $e) {
  $errors[] = 'Database not ready. Import `schema.sql`. (' . $e->getMessage() . ')';
}

render_header('Teams', true);
?>

<div class="page-title">
  <h1>Teams</h1>
  <div class="subtitle">Create and manage constructor teams.</div>
</div>

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

<div class="grid grid--2">
  <div class="panel">
    <div class="panel__header">
      <h2>Add team</h2>
    </div>
    <div class="panel__body">
      <form class="form" method="post" action="<?= e(url('/teams.php')) ?>">
        <input type="hidden" name="action" value="create_team">

        <label>
          Team name
          <input type="text" name="name" placeholder="e.g. Apex Racing" required>
        </label>

        <label>
          Base country
          <input type="text" name="base_country" placeholder="e.g. UK">
        </label>

        <label>
          Team principal
          <input type="text" name="principal" placeholder="e.g. Jordan Miles">
        </label>

        <div class="actions">
          <button class="btn btn--primary" type="submit">Create team</button>
        </div>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel__header">
      <h2>All teams</h2>
    </div>
    <div class="panel__body">
      <?php if (!$teams): ?>
        <div class="alert alert--info">No teams found.</div>
      <?php else: ?>
        <div class="table-wrap panel">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Base</th>
                <th>Principal</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($teams as $t): ?>
                <tr>
                  <td><strong><?= e($t['name']) ?></strong></td>
                  <td><?= e($t['base_country'] ?? '') ?></td>
                  <td><?= e($t['principal'] ?? '') ?></td>
                  <td>
                    <form method="post" action="<?= e(url('/teams.php')) ?>" onsubmit="return confirm('Delete this team? This may fail if cars/drivers still reference it.');" style="display:inline;">
                      <input type="hidden" name="action" value="delete_team">
                      <input type="hidden" name="id" value="<?= e((string)$t['id']) ?>">
                      <button class="btn btn--danger" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php render_footer(); ?>

