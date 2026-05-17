<?php
require_once __DIR__ . '/includes/cms-db.php';
require_once __DIR__ . '/includes/auth.php';

define('CMS_SKIP_MAINTENANCE', true);

auth_start();
require_once __DIR__ . '/includes/data.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
  if (auth_login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
    header('Location: ' . redirect_url('login'));
    exit;
  }
  $loginError = 'Invalid username or password.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'login') {
  require __DIR__ . '/includes/admin-actions.php';
  exit;
}

if (isset($_GET['logout'])) {
  auth_logout();
  header('Location: ' . redirect_url('login'));
  exit;
}

$user = auth_user();
$pageTitle = $user ? 'Dashboard | Manuelcode Admin' : 'Login | Manuelcode Admin';
$flash = $_GET['flash'] ?? '';
$flashType = ($_GET['t'] ?? '') === 'err' ? 'err' : 'ok';

if (!$user):
  require_once __DIR__ . '/includes/admin-icons.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <?php include __DIR__ . '/includes/viewport-meta.php'; ?>
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <?php include __DIR__ . '/includes/site-lock-head.php'; ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <?php require __DIR__ . '/includes/admin-head.php'; ?>
</head>
<body class="admin-login-wrap font-sans text-ink">
  <div class="admin-login-card">
    <p class="text-xs font-extrabold text-blue uppercase tracking-widest"><i class="fa-solid fa-lock mr-1.5" aria-hidden="true"></i> Admin</p>
    <h1 class="mt-2 text-2xl font-extrabold">Sign in</h1>
    <p class="mt-1 text-sm text-body">Manage site content at <?= htmlspecialchars($site['website'] ?? 'manuelcode.info') ?></p>
    <?php if (!empty($loginError)): ?>
      <p class="mt-4 admin-flash admin-flash--err"><?= htmlspecialchars($loginError) ?></p>
    <?php endif; ?>
    <form class="mt-6 space-y-4" method="post" action="<?= htmlspecialchars(url('login'), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="action" value="login" />
      <label class="admin-field">
        <span class="admin-field__label"><i class="fa-solid fa-user mr-1" aria-hidden="true"></i> Username</span>
        <input name="username" required autocomplete="username" class="admin-input" />
      </label>
      <label class="admin-field">
        <span class="admin-field__label"><i class="fa-solid fa-key mr-1" aria-hidden="true"></i> Password</span>
        <input name="password" type="password" required autocomplete="current-password" class="admin-input" />
      </label>
      <button type="submit" class="admin-btn admin-btn--primary w-full">
        <?= admin_icon('login') ?> Sign in
      </button>
    </form>
    <a href="<?= page_url('index.php') ?>" class="mt-4 inline-flex items-center gap-1.5 admin-link"><?= admin_icon('back') ?> Back to site</a>
  </div>
  <?php include __DIR__ . '/includes/site-lock-foot.php'; ?>
</body>
</html>
<?php
  exit;
endif;

$pdo = cms_db();
$view = preg_replace('/[^a-z]/', '', (string) ($_GET['p'] ?? 'dashboard'));
if ($view === '') {
  $view = 'dashboard';
}
$messagesUnread = cms_unread_count($pdo);
$quotesUnread = cms_unread_quote_requests_count($pdo);
require_once __DIR__ . '/includes/admin-icons.php';

$adminTitles = [
  'dashboard' => 'Dashboard',
  'pages' => 'Pages',
  'page' => 'Edit page',
  'lists' => 'Lists & data',
  'list' => 'Edit list',
  'team' => 'Team',
  'messages' => 'Messages',
  'settings' => 'Settings',
  'quoterequests' => 'Quote requests',
  'news' => 'News',
  'newsedit' => 'Edit news post',
  'gallery' => 'Design gallery',
];
$adminPageTitle = $adminTitles[$view] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <?php include __DIR__ . '/includes/viewport-meta.php'; ?>
  <title><?= htmlspecialchars($adminPageTitle) ?> | Manuelcode Admin</title>
  <?php include __DIR__ . '/includes/site-lock-head.php'; ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <?php require __DIR__ . '/includes/admin-head.php'; ?>
</head>
<body class="font-sans text-ink admin-dashboard">
<?php include __DIR__ . '/includes/admin-shell.php'; ?>
<?php
$viewFile = __DIR__ . '/admin/views/' . preg_replace('/[^a-z]/', '', $view) . '.php';
if (is_file($viewFile)) {
  include $viewFile;
} else {
  include __DIR__ . '/admin/views/dashboard.php';
}
include __DIR__ . '/includes/admin-shell-end.php';
?>
</body>
</html>
