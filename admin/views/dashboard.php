<?php
require_once dirname(__DIR__, 2) . '/includes/analytics.php';
$pages = $pdo->query('SELECT slug, hero_title FROM pages ORDER BY slug')->fetchAll();
$msgTotal = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
$teamCount = (int) $pdo->query('SELECT COUNT(*) FROM team_members')->fetchColumn();
$stats = analytics_dashboard_stats($pdo, 14);
$trendUp = $stats['trend_pct'] >= 0;
?>
<div class="admin-intro">
  <p class="admin-intro__text">Site analytics, content overview, and quick edits.</p>
</div>

<div class="admin-grid admin-grid--stats">
  <div class="admin-card admin-stat">
    <p class="admin-stat__label">Visits today</p>
    <p class="admin-stat__value text-blue"><?= number_format($stats['today']) ?></p>
  </div>
  <div class="admin-card admin-stat">
    <p class="admin-stat__label">Last 14 days</p>
    <p class="admin-stat__value"><?= number_format($stats['period_views']) ?></p>
    <p class="admin-stat__meta <?= $trendUp ? 'text-mint' : 'text-red-600' ?>">
      <?= $trendUp ? '↑' : '↓' ?> <?= abs($stats['trend_pct']) ?>% vs prior period
    </p>
  </div>
  <a href="<?= url('login') ?>?p=messages" class="admin-card admin-stat">
    <p class="admin-stat__label">Messages</p>
    <p class="admin-stat__value"><?= (int) $messagesUnread ?> <span class="text-sm font-bold text-body">unread</span></p>
    <p class="admin-stat__meta"><?= $msgTotal ?> total</p>
  </a>
  <a href="<?= url('login') ?>?p=pages" class="admin-card admin-stat">
    <p class="admin-stat__label">Pages</p>
    <p class="admin-stat__value"><?= count($pages) ?></p>
    <p class="admin-stat__meta"><?= $teamCount ?> team members</p>
  </a>
</div>

<div class="admin-grid admin-grid--2-lg mt-6">
  <div class="admin-card">
    <h2 class="font-extrabold text-sm">Visit trends (14 days)</h2>
    <p class="text-xs text-body mt-0.5">Total page views per day</p>
    <canvas id="visitsChart" class="mt-4 w-full max-h-52" height="200"></canvas>
  </div>
  <div class="admin-card">
    <h2 class="font-extrabold text-sm">Top pages</h2>
    <p class="text-xs text-body mt-0.5">Most viewed in the last 14 days</p>
    <canvas id="pagesChart" class="mt-4 w-full max-h-52" height="200"></canvas>
  </div>
</div>

<div class="admin-card mt-6">
  <h2 class="font-extrabold text-sm">Visits by hour (last 7 days)</h2>
  <canvas id="hoursChart" class="mt-4 w-full max-h-40" height="120"></canvas>
</div>

<div class="admin-card mt-6">
  <h2 class="font-extrabold text-sm">Edit pages</h2>
  <ul class="mt-3 grid sm:grid-cols-2 gap-2 text-sm">
    <?php foreach ($pages as $pg): ?>
      <li><a class="admin-link" href="<?= url('login') ?>?p=page&slug=<?= urlencode($pg['slug']) ?>"><?= htmlspecialchars(ucfirst($pg['slug'])) ?></a> — <?= htmlspecialchars($pg['hero_title']) ?></li>
    <?php endforeach; ?>
  </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  const labels = <?= json_encode($stats['chart_labels']) ?>;
  const values = <?= json_encode($stats['chart_values']) ?>;
  const topPages = <?= json_encode($stats['top_pages']) ?>;
  const hourLabels = <?= json_encode($stats['hour_labels']) ?>;
  const hourValues = <?= json_encode($stats['hour_values']) ?>;
  const grid = '#EAECF0';
  const body = '#475467';

  new Chart(document.getElementById('visitsChart'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Views',
        data: values,
        borderColor: '#FF7A00',
        backgroundColor: 'rgba(255, 122, 0, 0.12)',
        fill: true,
        tension: 0.35,
        pointRadius: 3,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: grid }, ticks: { color: body, maxRotation: 45 } },
        y: { beginAtZero: true, grid: { color: grid }, ticks: { color: body, precision: 0 } },
      },
    },
  });

  new Chart(document.getElementById('pagesChart'), {
    type: 'bar',
    data: {
      labels: topPages.map((p) => p.page_slug),
      datasets: [{
        label: 'Views',
        data: topPages.map((p) => p.views),
        backgroundColor: '#0B1E3A',
        borderRadius: 6,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: body } },
        y: { beginAtZero: true, grid: { color: grid }, ticks: { color: body, precision: 0 } },
      },
    },
  });

  new Chart(document.getElementById('hoursChart'), {
    type: 'bar',
    data: {
      labels: hourLabels.filter((_, i) => i % 2 === 0),
      datasets: [{
        label: 'Views',
        data: hourValues.filter((_, i) => i % 2 === 0),
        backgroundColor: 'rgba(18, 183, 106, 0.65)',
        borderRadius: 4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: body, font: { size: 10 } } },
        y: { beginAtZero: true, grid: { color: grid }, ticks: { color: body, precision: 0 } },
      },
    },
  });
})();
</script>
