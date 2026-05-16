<?php
$key = $_GET['key'] ?? '';
$allowed = ['services', 'projects', 'quotes', 'designs', 'companies', 'stats', 'clientLogos', 'homePages', 'headerNav', 'footerNav'];
if (!in_array($key, $allowed, true)) {
  echo '<p class="admin-flash admin-flash--err">Invalid list.</p>';
  return;
}
$defaults = [
  'services' => $services,
  'projects' => $projects,
  'quotes' => $quotes,
  'designs' => $designs,
  'companies' => $companies,
  'stats' => $stats,
  'clientLogos' => $clientLogos,
  'homePages' => $homePages,
  'headerNav' => cms_nav_to_list($headerNav),
  'footerNav' => cms_nav_to_list($footerNav),
];
$data = cms_get_list($pdo, $key, $defaults[$key]);
$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<div class="admin-intro">
  <a href="<?= url('login') ?>?p=lists" class="admin-link">← All lists</a>
  <p class="admin-intro__text mt-2">Valid JSON array for <strong><?= htmlspecialchars($key) ?></strong>. Invalid JSON will be rejected.</p>
</div>

<form class="admin-card max-w-4xl" method="post" action="<?= url('login') ?>">
  <input type="hidden" name="action" value="save_list" />
  <input type="hidden" name="list_key" value="<?= htmlspecialchars($key) ?>" />
  <textarea name="json" rows="20" class="admin-textarea font-mono text-xs min-h-[20rem]"><?= htmlspecialchars($json) ?></textarea>
  <button type="submit" class="admin-btn admin-btn--primary mt-4"><?= admin_icon('save') ?> Save list</button>
</form>
