<?php
/**
 * Dashboard
 * ---------
 * Shows a quick overview (KPIs) and a car catalogue preview.
 */

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/db.php';

require_login();

// KPI counts for quick overview.
$counts = [
  'teams' => 0,
  'cars' => 0,
  'drivers' => 0,
];

try {
  $counts['teams'] = (int)db()->query('SELECT COUNT(*) AS c FROM teams')->fetch()['c'];
  $counts['cars'] = (int)db()->query('SELECT COUNT(*) AS c FROM cars')->fetch()['c'];
  $counts['drivers'] = (int)db()->query('SELECT COUNT(*) AS c FROM drivers')->fetch()['c'];
} catch (Throwable $e) {
  // If schema isn't imported yet, we still render a helpful page.
  flash_set('error', 'Database tables not found. Import `schema.sql` first. (' . $e->getMessage() . ')');
}

// Car catalogue preview: latest 6 cars
$cars = [];
try {
  $stmt = db()->query(
    'SELECT c.id, c.model, c.manufacturer, c.season_year, c.engine, c.horsepower, c.image_url, t.name AS team_name
     FROM cars c
     JOIN teams t ON t.id = c.team_id
     ORDER BY c.created_at DESC
     LIMIT 6'
  );
  $cars = $stmt->fetchAll();
} catch (Throwable $e) {
  // Already handled above; ignore here.
}

render_header('Dashboard', true);
?>

<div class="page-title">
  <h1>Dashboard</h1>
  <div class="subtitle">Overview + quick access to your motorsports catalogue.</div>
</div>

<div class="grid grid--3">
  <div class="kpi">
    <div class="kpi__label">Teams</div>
    <div class="kpi__value"><?= e((string)$counts['teams']) ?></div>
  </div>
  <div class="kpi">
    <div class="kpi__label">Cars</div>
    <div class="kpi__value"><?= e((string)$counts['cars']) ?></div>
  </div>
  <div class="kpi">
    <div class="kpi__label">Drivers</div>
    <div class="kpi__value"><?= e((string)$counts['drivers']) ?></div>
  </div>
</div>

<div style="height: 14px"></div>

<div class="panel">
  <div class="panel__header">
    <h2>Car Catalogue (preview)</h2>
    <div class="actions">
      <a class="btn btn--primary" href="<?= e(url('/cars.php')) ?>">View all cars</a>
    </div>
  </div>

  <div class="panel__body">
    <?php if (!$cars): ?>
      <div class="alert alert--info">
        No cars found yet. Add a team and a car to populate the catalogue.
      </div>
    <?php else: ?>
      <div class="grid grid--3">
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
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php render_footer(); ?>

