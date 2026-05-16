<?php
require_once __DIR__ . '/includes/data.php';
$cms = cms_page('designs', [
  'label' => 'Design gallery',
  'title' => 'Posters, graphics and visual content.',
  'desc' => 'Quote graphics, brand posts and campaign visuals.',
  'body' => [],
]);
$pageTitle = 'Designs | Manuelcode.info';
$heroLabel = $cms['label'];
$heroTitle = $cms['title'];
$heroDesc = $cms['desc'];
include 'includes/header.php';
include 'includes/page-hero.php';
?>
<main>
  <section class="py-10 sm:py-12 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($designs as $i => $d): ?>
          <div class="reveal reveal-scale reveal-delay-<?= min(($i % 5) + 1, 5) ?>">
            <?php include __DIR__ . '/includes/design-card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
