<?php
$all = $pdo->query('SELECT slug, hero_label, hero_title FROM pages ORDER BY slug')->fetchAll();
$labels = ['home' => 'Home', 'projects' => 'Projects', 'services' => 'Services', 'quotes' => 'Quotes', 'designs' => 'Designs', 'about' => 'About', 'contact' => 'Contact'];
?>
<div class="admin-intro">
  <p class="admin-intro__text">Edit hero text and page-specific content.</p>
</div>
<div class="grid gap-3 max-w-3xl">
  <?php foreach ($all as $pg): ?>
    <a href="<?= url('login') ?>?p=page&slug=<?= urlencode($pg['slug']) ?>" class="admin-list-link">
      <div class="min-w-0">
        <p class="font-extrabold"><?= htmlspecialchars($labels[$pg['slug']] ?? ucfirst($pg['slug'])) ?></p>
        <p class="text-xs text-body mt-0.5 truncate"><?= htmlspecialchars($pg['hero_title']) ?></p>
      </div>
      <span class="text-xs font-bold text-blue shrink-0">Edit →</span>
    </a>
  <?php endforeach; ?>
</div>
