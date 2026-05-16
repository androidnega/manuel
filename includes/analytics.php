<?php

function analytics_should_track(): bool
{
  if (PHP_SAPI === 'cli') {
    return false;
  }
  $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
  if ($script === 'login.php') {
    return false;
  }
  $uri = $_SERVER['REQUEST_URI'] ?? '';
  if (str_contains($uri, '/login')) {
    return false;
  }
  return true;
}

function analytics_page_slug(): string
{
  $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
  $slug = page_slug($script);
  return $slug === '' ? 'home' : $slug;
}

function analytics_track(): void
{
  if (!analytics_should_track()) {
    return;
  }
  try {
    $pdo = cms_db();
    $page = analytics_page_slug();
    $day = date('Y-m-d');
    $hour = (int) date('G');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ipHash = $ip !== '' ? hash('sha256', $ip . '|' . date('Y-m-d')) : null;
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $ref = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500);

    $pdo->prepare('INSERT INTO page_views (page_slug, view_date, view_hour, ip_hash, user_agent, referrer, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([$page, $day, $hour, $ipHash, $ua, $ref, date('c')]);

    $pdo->prepare('INSERT INTO page_views_daily (page_slug, view_date, views)
      VALUES (?, ?, 1)
      ON CONFLICT(page_slug, view_date) DO UPDATE SET views = views + 1')->execute([$page, $day]);
  } catch (Throwable $e) {
    // Non-fatal
  }
}

function analytics_dashboard_stats(PDO $pdo, int $days = 14): array
{
  $days = max(7, min(90, $days));
  $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
  $prevSince = date('Y-m-d', strtotime('-' . (($days * 2) - 1) . ' days'));
  $prevUntil = date('Y-m-d', strtotime('-' . $days . ' days'));

  $total = (int) $pdo->query('SELECT COUNT(*) FROM page_views')->fetchColumn();
  $stmtToday = $pdo->prepare('SELECT COALESCE(SUM(views),0) FROM page_views_daily WHERE view_date = ?');
  $stmtToday->execute([date('Y-m-d')]);
  $today = (int) $stmtToday->fetchColumn();

  $stmtPeriod = $pdo->prepare('SELECT COALESCE(SUM(views),0) FROM page_views_daily WHERE view_date >= ?');
  $stmtPeriod->execute([$since]);
  $periodViews = (int) $stmtPeriod->fetchColumn();

  $stmtPrev = $pdo->prepare('SELECT COALESCE(SUM(views),0) FROM page_views_daily WHERE view_date >= ? AND view_date < ?');
  $stmtPrev->execute([$prevSince, $since]);
  $prevViews = (int) $stmtPrev->fetchColumn();

  $trendPct = $prevViews > 0
    ? round((($periodViews - $prevViews) / $prevViews) * 100, 1)
    : ($periodViews > 0 ? 100.0 : 0.0);

  $byDayStmt = $pdo->prepare('SELECT view_date, SUM(views) AS views FROM page_views_daily WHERE view_date >= ? GROUP BY view_date ORDER BY view_date ASC');
  $byDayStmt->execute([$since]);
  $byDayRows = $byDayStmt->fetchAll();
  $byDayMap = [];
  foreach ($byDayRows as $r) {
    $byDayMap[$r['view_date']] = (int) $r['views'];
  }
  $labels = [];
  $values = [];
  for ($i = $days - 1; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $labels[] = date('M j', strtotime($d));
    $values[] = $byDayMap[$d] ?? 0;
  }

  $topStmt = $pdo->prepare('SELECT page_slug, SUM(views) AS views FROM page_views_daily WHERE view_date >= ? GROUP BY page_slug ORDER BY views DESC LIMIT 8');
  $topStmt->execute([$since]);
  $topPages = $topStmt->fetchAll();

  $hourStmt = $pdo->query('SELECT view_hour, COUNT(*) AS c FROM page_views WHERE created_at >= datetime("now", "-7 days") GROUP BY view_hour ORDER BY view_hour');
  $hourLabels = range(0, 23);
  $hourValues = array_fill(0, 24, 0);
  foreach ($hourStmt->fetchAll() as $h) {
    $hourValues[(int) $h['view_hour']] = (int) $h['c'];
  }

  return [
    'total' => $total,
    'today' => $today,
    'period_views' => $periodViews,
    'trend_pct' => $trendPct,
    'days' => $days,
    'chart_labels' => $labels,
    'chart_values' => $values,
    'top_pages' => $topPages,
    'hour_labels' => array_map(fn($h) => sprintf('%02d:00', $h), $hourLabels),
    'hour_values' => $hourValues,
  ];
}
