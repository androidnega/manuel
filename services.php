<?php
require_once __DIR__ . '/includes/data.php';
$cms = cms_page('services', [
  'label' => 'What I do',
  'title' => 'Services built around real needs.',
  'desc' => 'Software systems and creative production for real organizations.',
  'body' => ['cta_title' => 'Need a custom solution?', 'cta_text' => 'Websites, campus platforms, inventory, posters, photo or video.'],
]);
$pageTitle = 'Services | Manuelcode.info';
$heroLabel = $cms['label'];
$heroTitle = $cms['title'];
$heroDesc = $cms['desc'];
$pageBody = $cms['body'];
include 'includes/header.php';
include 'includes/page-hero.php';
?>
<main>
  <section class="py-10 sm:py-12 bg-cloud">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($services as $i => $s):
          $c = serviceColorClasses($s['color']); ?>
          <article class="reveal reveal-scale reveal-delay-<?= min(($i % 5) + 1, 5) ?> rounded-2xl bg-white border border-line p-5 sm:p-6 hover:border-blue/30 hover:shadow-sleek hover:-translate-y-1 transition-all duration-300 <?= $c['hover'] ?>">
            <div class="h-11 w-11 rounded-xl <?= $c['bg'] ?> <?= $c['text'] ?> grid place-items-center"><?= icon($s['icon'], 'w-5 h-5') ?></div>
            <h3 class="mt-4 text-base font-extrabold"><?= htmlspecialchars($s['title']) ?></h3>
            <p class="mt-2 text-xs sm:text-sm text-body leading-relaxed"><?= htmlspecialchars($s['desc']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="reveal mt-6 rounded-2xl bg-white border border-line p-5 sm:p-6 text-center">
        <h2 class="text-xl font-extrabold"><?= htmlspecialchars($pageBody['cta_title'] ?? 'Need a custom solution?') ?></h2>
        <p class="text-[0.9375rem] leading-relaxed mt-2 text-body max-w-lg mx-auto"><?= htmlspecialchars($pageBody['cta_text'] ?? '') ?></p>
        <a href="<?= page_url('contact.php') ?>" class="mt-4 inline-flex items-center gap-2 rounded-full bg-blue text-white px-6 py-3 text-sm font-extrabold hover:bg-blue/90 shadow-sleek-sm transition-all hover:-translate-y-0.5">Start a project <?= icon('arrow-right', 'w-4 h-4') ?></a>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
