<?php
/**
 * Cars module (catalogue + CRUD-lite)
 * ----------------------------------
 * - Car catalogue list with images
 * - Add a new car (requires a team)
 * - Delete a car (stats will cascade-delete due to FK)
 */

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/db.php';

require_login();

$errors = [];

// Fetch teams for the "add car" form.
$teams = [];
try {
  $teams = db()->query('SELECT id, name FROM teams ORDER BY name ASC')->fetchAll();
} catch (Throwable $e) {
  $errors[] = 'Database not ready. Import `schema.sql`. (' . $e->getMessage() . ')';
}

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (input($_POST, 'action') === 'create_car')) {
  $team_id = (int)(input($_POST, 'team_id') ?? 0);
  $model = input($_POST, 'model') ?? '';
  $manufacturer = input($_POST, 'manufacturer');
  $season_year = (int)(input($_POST, 'season_year') ?? 0);
  $engine = input($_POST, 'engine');
  $horsepower = input($_POST, 'horsepower');
  $image_url = input($_POST, 'image_url');

  if ($team_id <= 0) {
    $errors[] = 'Please select a team.';
  }
  if ($model === '' || mb_strlen($model) < 2) {
    $errors[] = 'Car model must be at least 2 characters.';
  }
  if ($season_year < 1950 || $season_year > ((int)date('Y') + 1)) {
    $errors[] = 'Please enter a realistic season year.';
  }

  // Optional numeric validation for horsepower
  $hpInt = null;
  if ($horsepower !== null) {
    if (!ctype_digit($horsepower)) {
      $errors[] = 'Horsepower must be a positive number.';
    } else {
      $hpInt = (int)$horsepower;
    }
  }

  if (!$errors) {
    try {
      $stmt = db()->prepare(
        'INSERT INTO cars (team_id, model, manufacturer, season_year, engine, horsepower, image_url)
         VALUES (:team_id, :model, :manufacturer, :season_year, :engine, :horsepower, :image_url)'
      );
      $stmt->execute([
        ':team_id' => $team_id,
        ':model' => $model,
        ':manufacturer' => $manufacturer,
        ':season_year' => $season_year,
        ':engine' => $engine,
        ':horsepower' => $hpInt,
        ':image_url' => $image_url,
      ]);

      // Create an empty stats row for the new car (nice UX for stats module).
      $carId = (int)db()->lastInsertId();
      $stmt = db()->prepare('INSERT INTO car_stats (car_id) VALUES (:car_id)');
      $stmt->execute([':car_id' => $carId]);

      flash_set('success', 'Car added to catalogue.');
      header('Location: ' . url('/cars.php'));
      exit;
    } catch (Throwable $e) {
      $errors[] = 'Could not create car. (' . $e->getMessage() . ')';
    }
  }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (input($_POST, 'action') === 'delete_car')) {
  $id = (int)(input($_POST, 'id') ?? 0);
  if ($id > 0) {
    try {
      $stmt = db()->prepare('DELETE FROM cars WHERE id = :id');
      $stmt->execute([':id' => $id]);
      flash_set('info', 'Car deleted.');
      header('Location: ' . url('/cars.php'));
      exit;
    } catch (Throwable $e) {
      $errors[] = 'Could not delete car. (' . $e->getMessage() . ')';
    }
  }
}

// Fetch cars for catalogue
$cars = [];
try {
  $stmt = db()->query(
    'SELECT c.id, c.model, c.manufacturer, c.season_year, c.engine, c.horsepower, c.image_url, t.name AS team_name
     FROM cars c
     JOIN teams t ON t.id = c.team_id
     ORDER BY c.season_year DESC, c.created_at DESC'
  );
  $cars = $stmt->fetchAll();
} catch (Throwable $e) {
  // Error already captured above; avoid duplicate messages.
}

render_header('Cars', true);
?>

<div class="page-title">
  <h1>Cars</h1>
  <div class="subtitle">Car catalogue and season entries.</div>
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
      <h2>Add car</h2>
    </div>
    <div class="panel__body">
      <?php if (!$teams): ?>
        <div class="alert alert--info">
          You need at least one team before adding a car.
          <a href="<?= e(url('/teams.php')) ?>"><strong>Create a team</strong></a>.
        </div>
      <?php else: ?>
        <form class="form" method="post" action="<?= e(url('/cars.php')) ?>">
          <input type="hidden" name="action" value="create_car">

          <label>
            Team
            <select name="team_id" required>
              <option value="">Select a team...</option>
              <?php foreach ($teams as $t): ?>
                <option value="<?= e((string)$t['id']) ?>"><?= e($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            Model
            <input type="text" name="model" placeholder="e.g. AR-01" required>
          </label>

          <div class="grid grid--2">
            <label>
              Manufacturer
              <input type="text" name="manufacturer" placeholder="e.g. Apex">
            </label>

            <label>
              Season year
              <input type="number" name="season_year" value="<?= e((string)date('Y')) ?>" min="1950" max="<?= e((string)((int)date('Y') + 1)) ?>" required>
            </label>
          </div>

          <div class="grid grid--2">
            <label>
              Engine
              <input type="text" name="engine" placeholder="e.g. V6 Turbo Hybrid">
            </label>

            <label>
              Horsepower (hp)
              <input type="number" name="horsepower" min="1" placeholder="e.g. 1000">
            </label>
          </div>

          <label>
            Image URL (optional)
            <input type="url" name="image_url" placeholder="https://example.com/car.jpg">
          </label>

          <div class="actions">
            <button class="btn btn--primary" type="submit">Add car</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel__header">
      <h2>Catalogue</h2>
    </div>
    <div class="panel__body">
      <?php if (!$cars): ?>
        <div class="alert alert--info">No cars found.</div>
      <?php else: ?>
        <div class="grid grid--2">
          <?php foreach ($cars as $car): ?>
            <div class="card">
              <div class="card__media">
                <?php if (!empty($car['image_url'])): ?>
                  <img src="<?= e($car['image_url']) ?>" alt="<?= e($car['model']) ?>">
                <?php endif; ?>
              </div>
              <div class="card__body">
                <div class="card__title"><?= e($car['model']) ?> <span style="color: var(--muted); font-weight: 700;">(<?= e((string)$car['season_year']) ?>)</span></div>
                <div class="card__meta">
                  Team: <strong><?= e($car['team_name']) ?></strong><br>
                  <?= e($car['manufacturer'] ?: 'Manufacturer N/A') ?> · <?= e($car['engine'] ?: 'Engine N/A') ?><br>
                  <?= e($car['horsepower'] ? ($car['horsepower'] . ' hp') : 'Power N/A') ?>
                </div>
                <div style="height: 10px"></div>
                <form method="post" action="<?= e(url('/cars.php')) ?>" onsubmit="return confirm('Delete this car?');">
                  <input type="hidden" name="action" value="delete_car">
                  <input type="hidden" name="id" value="<?= e((string)$car['id']) ?>">
                  <button class="btn btn--danger" type="submit">Delete</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php render_footer(); ?>

