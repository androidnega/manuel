<?php
/** @var string $view @var array $user @var string $adminPageTitle @var int $messagesUnread @var int $quotesUnread @var string $flash @var string $flashType */
$maintenance = cms_maintenance_config($pdo);
$adminNavItems = [
  ['section' => 'dashboard', 'href' => url('login'), 'label' => 'Dashboard', 'icon' => 'dashboard'],
  ['section' => 'homehero', 'href' => url('login') . '?p=homehero', 'label' => 'Home hero', 'icon' => 'homehero'],
  ['section' => 'pages', 'href' => url('login') . '?p=pages', 'label' => 'Pages', 'icon' => 'pages'],
  ['section' => 'lists', 'href' => url('login') . '?p=lists', 'label' => 'Lists & data', 'icon' => 'lists'],
  ['section' => 'gallery', 'href' => url('login') . '?p=gallery', 'label' => 'Design gallery', 'icon' => 'gallery'],
  ['section' => 'team', 'href' => url('login') . '?p=team', 'label' => 'Team', 'icon' => 'team'],
  ['section' => 'news', 'href' => url('login') . '?p=news', 'label' => 'News', 'icon' => 'news'],
  ['section' => 'messages', 'href' => url('login') . '?p=messages', 'label' => 'Messages', 'icon' => 'messages', 'badge' => $messagesUnread],
  ['section' => 'quoterequests', 'href' => url('login') . '?p=quoterequests', 'label' => 'Quote requests', 'icon' => 'quote', 'badge' => $quotesUnread],
  ['section' => 'settings', 'href' => url('login') . '?p=settings', 'label' => 'Settings', 'icon' => 'settings'],
];
?>
<div class="admin-shell">
  <div id="adminOverlay" class="admin-overlay" aria-hidden="true"></div>

  <aside id="adminSidebar" class="admin-sidebar" aria-label="Admin navigation">
    <p class="text-xs font-extrabold uppercase tracking-widest text-blue"><i class="fa-solid fa-code mr-1" aria-hidden="true"></i> Manuelcode</p>
    <p class="mt-1 text-sm font-extrabold">Admin</p>
    <nav class="mt-6 flex flex-1 flex-col gap-1 overflow-y-auto text-sm font-semibold">
      <?php foreach ($adminNavItems as $item):
        $active = admin_nav_is_active($item['section'], $view);
      ?>
      <a
        href="<?= htmlspecialchars($item['href']) ?>"
        class="admin-nav-link rounded-lg px-3 py-2 <?= admin_nav_class($item['section'], $view) ?>"
        <?= $active ? 'aria-current="page"' : '' ?>
      >
        <?= admin_icon($item['icon']) ?>
        <?php if (in_array($item['section'], ['messages', 'quoterequests'], true)): ?>
        <span class="flex flex-1 items-center justify-between gap-2">
          <?= htmlspecialchars($item['label']) ?>
          <?php if (!empty($item['badge'])): ?><span class="rounded-full bg-blue px-2 py-0.5 text-[10px] font-extrabold text-white"><?= (int) $item['badge'] ?></span><?php endif; ?>
        </span>
        <?php else: ?>
        <?= htmlspecialchars($item['label']) ?>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </nav>
    <div class="mt-auto border-t border-line pt-4 text-xs text-body">
      <p class="admin-nav-link">
        <?= admin_icon('user') ?>
        <span>Logged in as <strong class="text-ink"><?= htmlspecialchars($user['username']) ?></strong></span>
      </p>
    </div>
  </aside>

  <div class="admin-main-wrap">
    <header class="admin-topbar">
      <div class="admin-topbar__left">
        <button type="button" id="adminMenuBtn" class="admin-menu-btn" aria-label="Open menu" aria-expanded="false" aria-controls="adminSidebar">
          <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
        <h1 class="admin-topbar__title"><?= htmlspecialchars($adminPageTitle) ?></h1>
      </div>
      <div class="admin-topbar__actions">
        <?php if ($maintenance['enabled']): ?>
          <span class="admin-badge-live hidden sm:inline-flex">Update mode on</span>
        <?php endif; ?>
        <a href="<?= page_url('index.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn--ghost admin-btn--sm">
          <?= admin_icon('site') ?> <span class="hidden sm:inline">View site</span>
        </a>
        <a href="<?= url('login') ?>?logout=1" class="admin-btn admin-btn--primary admin-btn--sm">
          <?= admin_icon('logout') ?> <span class="hidden xs:inline">Logout</span>
        </a>
      </div>
    </header>

    <main class="admin-content">
      <?php if (!empty($flash)): ?>
        <p class="admin-flash <?= $flashType === 'err' ? 'admin-flash--err' : 'admin-flash--ok' ?>"><?= htmlspecialchars($flash) ?></p>
      <?php endif; ?>
