<?php
/** @var array $config @var array $site */
global $brand;
if (!isset($brand) || !is_array($brand)) {
  $brand = [
    'logo' => 'assets/images/main-logo.png',
    'logo_dark' => 'assets/images/dark-logo.png',
    'favicon' => 'assets/images/favicon.png',
  ];
}

$endsAt = $config['ends_at'] ?? '';
$endsTs = $endsAt !== '' ? strtotime($endsAt) : false;
$hasCountdown = $endsTs !== false && $endsTs > time();
$pageTitle = htmlspecialchars($config['title']) . ' | ' . htmlspecialchars($site['name'] ?? 'Manuelcode');
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?></title>
  <meta name="robots" content="noindex, nofollow" />
  <?php include __DIR__ . '/includes/theme-head.php'; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= asset('assets/css/update.css') ?>" />
</head>
<body class="update-page">
  <header class="update-header">
    <div class="update-header__inner">
      <a href="<?= page_url('index.php') ?>" class="update-header__brand" aria-label="<?= htmlspecialchars($site['name'] ?? 'Manuelcode') ?> home">
        <?php
        $logoVariant = 'wordmark';
        $logoTheme = 'light';
        $showIcon = true;
        $showTagline = false;
        include __DIR__ . '/includes/logo.php';
        ?>
      </a>
    </div>
  </header>

  <main class="update-main">
    <div class="update-main__glow" aria-hidden="true"></div>

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
  <script src="<?= asset('assets/js/theme.js') ?>"></script>
</body>
</html>
