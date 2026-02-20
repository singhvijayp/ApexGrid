<?php
/**
 * Drivers module (CRUD-lite)
 * --------------------------
 * - List all drivers with their team
 * - Add a new driver (requires a team)
 * - Delete a driver (stats will cascade-delete due to FK)
 */

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/db.php';

require_login();

$errors = [];

// Fetch teams for the "add driver" form.
$teams = [];
try {
  $teams = db()->query('SELECT id, name FROM teams ORDER BY name ASC')->fetchAll();
} catch (Exception $e) {
  $errors[] = 'Database not ready. Import `schema.sql`. (' . $e->getMessage() . ')';
}

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (input($_POST, 'action') === 'create_driver')) {
  $team_id = (int)coalesce(input($_POST, 'team_id'), 0);
  $first_name = coalesce(input($_POST, 'first_name'), '');
  $last_name = coalesce(input($_POST, 'last_name'), '');
  $nationality = input($_POST, 'nationality');
  $date_of_birth = input($_POST, 'date_of_birth');
  $driver_number = input($_POST, 'driver_number');

  if ($team_id <= 0) {
    $errors[] = 'Please select a team.';
  }
  if ($first_name === '' || mb_strlen($first_name) < 2) {
    $errors[] = 'First name must be at least 2 characters.';
  }
  if ($last_name === '' || mb_strlen($last_name) < 2) {
    $errors[] = 'Last name must be at least 2 characters.';
  }

  // Optional numeric validation for driver number
  $numInt = null;
  if ($driver_number !== null) {
    if (!ctype_digit($driver_number)) {
      $errors[] = 'Driver number must be a positive number.';
    } else {
      $numInt = (int)$driver_number;
    }
  }

  // Optional date validation (simple): accept empty or valid YYYY-MM-DD.
  $dob = null;
  if ($date_of_birth !== null) {
    $dt = DateTime::createFromFormat('Y-m-d', $date_of_birth);
    if (!$dt || $dt->format('Y-m-d') !== $date_of_birth) {
      $errors[] = 'Date of birth must be in YYYY-MM-DD format.';
    } else {
      $dob = $date_of_birth;
    }
  }

  if (!$errors) {
    try {
      $stmt = db()->prepare(
        'INSERT INTO drivers (team_id, first_name, last_name, nationality, date_of_birth, driver_number)
         VALUES (:team_id, :first_name, :last_name, :nationality, :date_of_birth, :driver_number)'
      );
      $stmt->execute([
        ':team_id' => $team_id,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':nationality' => $nationality,
        ':date_of_birth' => $dob,
        ':driver_number' => $numInt,
      ]);

      // Create an empty stats row for the new driver.
      $driverId = (int)db()->lastInsertId();
      $stmt = db()->prepare('INSERT INTO driver_stats (driver_id) VALUES (:driver_id)');
      $stmt->execute([':driver_id' => $driverId]);

      flash_set('success', 'Driver created.');
      header('Location: ' . url('/drivers.php'));
      exit;
    } catch (Exception $e) {
      $errors[] = 'Could not create driver. (' . $e->getMessage() . ')';
    }
  }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (input($_POST, 'action') === 'delete_driver')) {
  $id = (int)coalesce(input($_POST, 'id'), 0);
  if ($id > 0) {
    try {
      $stmt = db()->prepare('DELETE FROM drivers WHERE id = :id');
      $stmt->execute([':id' => $id]);
      flash_set('info', 'Driver deleted.');
      header('Location: ' . url('/drivers.php'));
      exit;
    } catch (Exception $e) {
      $errors[] = 'Could not delete driver. (' . $e->getMessage() . ')';
    }
  }
}

// Fetch drivers
$drivers = [];
try {
  $stmt = db()->query(
    'SELECT d.id, d.first_name, d.last_name, d.nationality, d.date_of_birth, d.driver_number, t.name AS team_name
     FROM drivers d
     JOIN teams t ON t.id = d.team_id
     ORDER BY d.last_name ASC, d.first_name ASC'
  );
  $drivers = $stmt->fetchAll();
} catch (Throwable $e) {
  // Error already captured above.
}

render_header('Drivers', true);
?>

<div class="page-title">
  <h1>Drivers</h1>
  <div class="subtitle">Manage drivers and their team assignments.</div>
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
      <h2>Add driver</h2>
    </div>
    <div class="panel__body">
      <?php if (!$teams): ?>
        <div class="alert alert--info">
          You need at least one team before adding a driver.
          <a href="<?= e(url('/teams.php')) ?>"><strong>Create a team</strong></a>.
        </div>
      <?php else: ?>
        <form class="form" method="post" action="<?= e(url('/drivers.php')) ?>">
          <input type="hidden" name="action" value="create_driver">

          <label>
            Team
            <select name="team_id" required>
              <option value="">Select a team...</option>
              <?php foreach ($teams as $t): ?>
                <option value="<?= e((string)$t['id']) ?>"><?= e($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <div class="grid grid--2">
            <label>
              First name
              <input type="text" name="first_name" placeholder="e.g. Asha" required>
            </label>
            <label>
              Last name
              <input type="text" name="last_name" placeholder="e.g. Khan" required>
            </label>
          </div>

          <div class="grid grid--2">
            <label>
              Nationality
              <input type="text" name="nationality" placeholder="e.g. India">
            </label>
            <label>
              Driver number
              <input type="number" name="driver_number" min="1" placeholder="e.g. 7">
            </label>
          </div>

          <label>
            Date of birth
            <input type="date" name="date_of_birth">
          </label>

          <div class="actions">
            <button class="btn btn--primary" type="submit">Create driver</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel__header">
      <h2>All drivers</h2>
    </div>
    <div class="panel__body">
      <?php if (!$drivers): ?>
        <div class="alert alert--info">No drivers found.</div>
      <?php else: ?>
        <div class="table-wrap panel">
          <table>
            <thead>
              <tr>
                <th>Driver</th>
                <th>Team</th>
                <th>Nationality</th>
                <th>DOB</th>
                <th>No.</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($drivers as $d): ?>
                <tr>
                  <td><strong><?= e($d['first_name'] . ' ' . $d['last_name']) ?></strong></td>
                  <td><?= e($d['team_name']) ?></td>
                  <td><?= e(coalesce($d['nationality'], '')) ?></td>
                  <td><?= e(coalesce($d['date_of_birth'], '')) ?></td>
                  <td><?= e($d['driver_number'] ? (string)$d['driver_number'] : '') ?></td>
                  <td>
                    <form method="post" action="<?= e(url('/drivers.php')) ?>" onsubmit="return confirm('Delete this driver?');" style="display:inline;">
                      <input type="hidden" name="action" value="delete_driver">
                      <input type="hidden" name="id" value="<?= e((string)$d['id']) ?>">
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

