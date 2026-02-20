<?php
/**
 * Stats module
 * ------------
 * Shows car and driver statistics and lets you update them.
 *
 * Design choice:
 * - Stats are kept in separate tables (driver_stats, car_stats) to illustrate a clean relational model.
 * - Each driver/car can have at most 1 stats row (enforced by UNIQUE keys).
 */

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/db.php';

require_login();

$errors = [];

// Handle driver stats update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (input($_POST, 'action') === 'update_driver_stats')) {
  $driver_id = (int)coalesce(input($_POST, 'driver_id'), 0);
  $races = (int)coalesce(input($_POST, 'races'), 0);
  $wins = (int)coalesce(input($_POST, 'wins'), 0);
  $podiums = (int)coalesce(input($_POST, 'podiums'), 0);
  $poles = (int)coalesce(input($_POST, 'poles'), 0);
  $points = (int)coalesce(input($_POST, 'points'), 0);
  $championships = (int)coalesce(input($_POST, 'championships'), 0);

  if ($driver_id <= 0) {
    $errors[] = 'Invalid driver selection.';
  }

  if (!$errors) {
    try {
      $stmt = db()->prepare(
        'INSERT INTO driver_stats (driver_id, races, wins, podiums, poles, points, championships)
         VALUES (:driver_id, :races, :wins, :podiums, :poles, :points, :championships)
         ON DUPLICATE KEY UPDATE
           races = VALUES(races),
           wins = VALUES(wins),
           podiums = VALUES(podiums),
           poles = VALUES(poles),
           points = VALUES(points),
           championships = VALUES(championships)'
      );
      $stmt->execute([
        ':driver_id' => $driver_id,
        ':races' => max(0, $races),
        ':wins' => max(0, $wins),
        ':podiums' => max(0, $podiums),
        ':poles' => max(0, $poles),
        ':points' => max(0, $points),
        ':championships' => max(0, $championships),
      ]);
      flash_set('success', 'Driver stats updated.');
      header('Location: ' . url('/stats.php'));
      exit;
    } catch (Exception $e) {
      $errors[] = 'Could not update driver stats. (' . $e->getMessage() . ')';
    }
  }
}

// Handle car stats update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (input($_POST, 'action') === 'update_car_stats')) {
  $car_id = (int)coalesce(input($_POST, 'car_id'), 0);
  $races = (int)coalesce(input($_POST, 'races'), 0);
  $wins = (int)coalesce(input($_POST, 'wins'), 0);
  $poles = (int)coalesce(input($_POST, 'poles'), 0);
  $fastest_laps = (int)coalesce(input($_POST, 'fastest_laps'), 0);
  $points = (int)coalesce(input($_POST, 'points'), 0);

  if ($car_id <= 0) {
    $errors[] = 'Invalid car selection.';
  }

  if (!$errors) {
    try {
      $stmt = db()->prepare(
        'INSERT INTO car_stats (car_id, races, wins, poles, fastest_laps, points)
         VALUES (:car_id, :races, :wins, :poles, :fastest_laps, :points)
         ON DUPLICATE KEY UPDATE
           races = VALUES(races),
           wins = VALUES(wins),
           poles = VALUES(poles),
           fastest_laps = VALUES(fastest_laps),
           points = VALUES(points)'
      );
      $stmt->execute([
        ':car_id' => $car_id,
        ':races' => max(0, $races),
        ':wins' => max(0, $wins),
        ':poles' => max(0, $poles),
        ':fastest_laps' => max(0, $fastest_laps),
        ':points' => max(0, $points),
      ]);
      flash_set('success', 'Car stats updated.');
      header('Location: ' . url('/stats.php'));
      exit;
    } catch (Exception $e) {
      $errors[] = 'Could not update car stats. (' . $e->getMessage() . ')';
    }
  }
}

// Fetch stats (drivers)
$driverRows = [];
$carRows = [];
try {
  $driverRows = db()->query(
    'SELECT d.id AS driver_id, CONCAT(d.first_name, " ", d.last_name) AS driver_name, t.name AS team_name,
            s.races, s.wins, s.podiums, s.poles, s.points, s.championships
     FROM drivers d
     JOIN teams t ON t.id = d.team_id
     LEFT JOIN driver_stats s ON s.driver_id = d.id
     ORDER BY s.points DESC, d.last_name ASC'
  )->fetchAll();

  $carRows = db()->query(
    'SELECT c.id AS car_id, c.model, c.season_year, t.name AS team_name,
            s.races, s.wins, s.poles, s.fastest_laps, s.points
     FROM cars c
     JOIN teams t ON t.id = c.team_id
     LEFT JOIN car_stats s ON s.car_id = c.id
     ORDER BY s.points DESC, c.season_year DESC'
  )->fetchAll();
} catch (Exception $e) {
  $errors[] = 'Database not ready. Import `schema.sql`. (' . $e->getMessage() . ')';
}

render_header('Stats', true);
?>

<div class="page-title">
  <h1>Stats</h1>
  <div class="subtitle">Track performance for drivers and cars.</div>
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
      <h2>Driver stats</h2>
    </div>
    <div class="panel__body">
      <?php if (!$driverRows): ?>
        <div class="alert alert--info">No drivers found.</div>
      <?php else: ?>
        <div class="table-wrap panel">
          <table>
            <thead>
              <tr>
                <th>Driver</th>
                <th>Team</th>
                <th>R</th>
                <th>W</th>
                <th>POD</th>
                <th>POL</th>
                <th>PTS</th>
                <th>CH</th>
                <th>Update</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($driverRows as $r): ?>
                <tr>
                  <td><strong><?= e($r['driver_name']) ?></strong></td>
                  <td><?= e($r['team_name']) ?></td>
                  <td><?= e((string)coalesce($r['races'], 0)) ?></td>
                  <td><?= e((string)coalesce($r['wins'], 0)) ?></td>
                  <td><?= e((string)coalesce($r['podiums'], 0)) ?></td>
                  <td><?= e((string)coalesce($r['poles'], 0)) ?></td>
                  <td><strong><?= e((string)coalesce($r['points'], 0)) ?></strong></td>
                  <td><?= e((string)coalesce($r['championships'], 0)) ?></td>
                  <td>
                    <details>
                      <summary class="btn" style="display:inline-block;">Edit</summary>
                      <form class="form" method="post" action="<?= e(url('/stats.php')) ?>" style="margin-top:10px;">
                        <input type="hidden" name="action" value="update_driver_stats">
                        <input type="hidden" name="driver_id" value="<?= e((string)$r['driver_id']) ?>">
                        <div class="grid grid--3">
                          <label>Races <input type="number" name="races" min="0" value="<?= e((string)coalesce($r['races'], 0)) ?>"></label>
                          <label>Wins <input type="number" name="wins" min="0" value="<?= e((string)coalesce($r['wins'], 0)) ?>"></label>
                          <label>Podiums <input type="number" name="podiums" min="0" value="<?= e((string)coalesce($r['podiums'], 0)) ?>"></label>
                        </div>
                        <div class="grid grid--3">
                          <label>Poles <input type="number" name="poles" min="0" value="<?= e((string)coalesce($r['poles'], 0)) ?>"></label>
                          <label>Points <input type="number" name="points" min="0" value="<?= e((string)coalesce($r['points'], 0)) ?>"></label>
                          <label>Championships <input type="number" name="championships" min="0" value="<?= e((string)coalesce($r['championships'], 0)) ?>"></label>
                        </div>
                        <div class="actions">
                          <button class="btn btn--primary" type="submit">Save</button>
                        </div>
                      </form>
                    </details>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel__header">
      <h2>Car stats</h2>
    </div>
    <div class="panel__body">
      <?php if (!$carRows): ?>
        <div class="alert alert--info">No cars found.</div>
      <?php else: ?>
        <div class="table-wrap panel">
          <table>
            <thead>
              <tr>
                <th>Car</th>
                <th>Team</th>
                <th>Year</th>
                <th>R</th>
                <th>W</th>
                <th>POL</th>
                <th>FL</th>
                <th>PTS</th>
                <th>Update</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($carRows as $r): ?>
                <tr>
                  <td><strong><?= e($r['model']) ?></strong></td>
                  <td><?= e($r['team_name']) ?></td>
                  <td><?= e((string)$r['season_year']) ?></td>
                  <td><?= e((string)coalesce($r['races'], 0)) ?></td>
                  <td><?= e((string)coalesce($r['wins'], 0)) ?></td>
                  <td><?= e((string)coalesce($r['poles'], 0)) ?></td>
                  <td><?= e((string)coalesce($r['fastest_laps'], 0)) ?></td>
                  <td><strong><?= e((string)coalesce($r['points'], 0)) ?></strong></td>
                  <td>
                    <details>
                      <summary class="btn" style="display:inline-block;">Edit</summary>
                      <form class="form" method="post" action="<?= e(url('/stats.php')) ?>" style="margin-top:10px;">
                        <input type="hidden" name="action" value="update_car_stats">
                        <input type="hidden" name="car_id" value="<?= e((string)$r['car_id']) ?>">
                        <div class="grid grid--3">
                          <label>Races <input type="number" name="races" min="0" value="<?= e((string)coalesce($r['races'], 0)) ?>"></label>
                          <label>Wins <input type="number" name="wins" min="0" value="<?= e((string)coalesce($r['wins'], 0)) ?>"></label>
                          <label>Poles <input type="number" name="poles" min="0" value="<?= e((string)coalesce($r['poles'], 0)) ?>"></label>
                        </div>
                        <div class="grid grid--2">
                          <label>Fastest laps <input type="number" name="fastest_laps" min="0" value="<?= e((string)coalesce($r['fastest_laps'], 0)) ?>"></label>
                          <label>Points <input type="number" name="points" min="0" value="<?= e((string)coalesce($r['points'], 0)) ?>"></label>
                        </div>
                        <div class="actions">
                          <button class="btn btn--primary" type="submit">Save</button>
                        </div>
                      </form>
                    </details>
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

