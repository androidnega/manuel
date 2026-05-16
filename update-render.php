<?php
/** @var array $config @var array $site */

$endsAt = $config['ends_at'] ?? '';
$endsTs = $endsAt !== '' ? strtotime($endsAt) : false;
$hasCountdown = $endsTs !== false && $endsTs > time();
$pageTitle = htmlspecialchars($config['title']) . ' | ' . htmlspecialchars($site['name'] ?? 'Manuelcode');
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="color-scheme" content="light" />
  <?php include __DIR__ . '/includes/viewport-meta.php'; ?>
  <link rel="stylesheet" href="<?= asset('assets/css/site-lock.css') ?>" />
  <title><?= $pageTitle ?></title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= asset('assets/css/update.css') ?>" />
</head>
<body class="update-page">
  <main class="update-main">
    <p class="update-eyebrow">Site update in progress</p>
    <h1 class="update-title"><?= htmlspecialchars($config['title']) ?></h1>
    <p class="update-caption"><?= htmlspecialchars($config['caption']) ?></p>

    <?php if ($hasCountdown): ?>
    <div
      class="update-countdown"
      id="updateCountdown"
      data-ends-at="<?= htmlspecialchars(date('c', $endsTs)) ?>"
      aria-live="polite"
      aria-label="Time until site is back"
    >
      <div class="update-countdown__unit">
        <span class="update-countdown__value" data-unit="days">00</span>
        <span class="update-countdown__label">Days</span>
      </div>
      <span class="update-countdown__sep" aria-hidden="true">,</span>
      <div class="update-countdown__unit">
        <span class="update-countdown__value" data-unit="hours">00</span>
        <span class="update-countdown__label">Hours</span>
      </div>
      <span class="update-countdown__sep" aria-hidden="true">,</span>
      <div class="update-countdown__unit">
        <span class="update-countdown__value" data-unit="minutes">00</span>
        <span class="update-countdown__label">Minutes</span>
      </div>
      <span class="update-countdown__sep" aria-hidden="true">,</span>
      <div class="update-countdown__unit">
        <span class="update-countdown__value" data-unit="seconds">00</span>
        <span class="update-countdown__label">Seconds</span>
      </div>
    </div>
    <p class="update-return">Back online <time datetime="<?= htmlspecialchars(date('c', $endsTs)) ?>"><?= htmlspecialchars(date('l, F j · g:i A', $endsTs)) ?></time></p>
    <?php else: ?>
    <p class="update-soon">We will be back online shortly.</p>
    <?php endif; ?>
  </main>

  <?php if ($hasCountdown): ?>
  <script src="<?= asset('assets/js/update-countdown.js') ?>"></script>
  <?php endif; ?>
  <script src="<?= asset('assets/js/site-lock.js') ?>"></script>
</body>
</html>
