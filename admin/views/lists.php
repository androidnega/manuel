<?php
$keys = [
  'services' => 'Services',
  'projects' => 'Projects',
  'quotes' => 'Quotes (legacy JSON — unused)',
  'designs' => 'Designs',
  'companies' => 'Organizations (About)',
  'stats' => 'Home stats',
  'clientLogos' => 'Client logos (Home)',
  'homePages' => 'Home page cards',
  'headerNav' => 'Header navigation',
  'footerNav' => 'Footer navigation',
];
?>
<div class="admin-intro">
  <p class="admin-intro__text">Edit structured content as JSON. Projects and client logos include Kuukuacares, Go Ahanta, and KTI.</p>
  <p class="mt-2 text-xs text-body max-w-2xl">Navigation format: <code class="bg-cloud px-1 rounded border border-line">[{"label":"Home","href":"index.php"}]</code></p>
</div>
<div class="admin-grid sm:grid-cols-2 max-w-4xl">
  <?php foreach ($keys as $key => $label): ?>
    <a href="<?= url('login') ?>?p=list&key=<?= urlencode($key) ?>" class="admin-list-link">
      <span class="font-extrabold text-sm"><?= htmlspecialchars($label) ?></span>
      <span class="text-xs font-bold text-blue">Edit →</span>
    </a>
  <?php endforeach; ?>
</div>
