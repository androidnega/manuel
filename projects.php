<?php
require_once __DIR__ . '/includes/data.php';
$cms = cms_page('projects', [
  'label' => 'Selected work',
  'title' => 'Projects and systems I’ve worked on.',
  'desc' => 'Practical systems for schools, students, businesses and organizations.',
  'body' => [],
]);
$pageTitle = 'Projects | Manuelcode.info';
$heroLabel = $cms['label'];
$heroTitle = $cms['title'];
$heroDesc = $cms['desc'];
include 'includes/header.php';
include 'includes/page-hero.php';
$featured = array_values(array_filter($projects, fn($p) => !empty($p['featured'])))[0] ?? $projects[0];
$others = array_values(array_filter($projects, fn($p) => empty($p['featured'])));
?>
<main>
  <section class="py-10 sm:py-12 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <article class="reveal rounded-2xl bg-deep text-white p-5 sm:p-6 overflow-hidden relative mb-4">
        <div class="absolute -right-12 -top-12 h-36 w-36 rounded-full bg-blue/25"></div>
        <div class="relative z-10">
          <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-[10px] font-extrabold"><?= htmlspecialchars($featured['category']) ?></span>
          <h2 class="mt-3 text-2xl sm:text-3xl font-extrabold"><?= htmlspecialchars($featured['title']) ?></h2>
          <p class="mt-2 text-sm text-white/70 leading-relaxed max-w-lg"><?= htmlspecialchars($featured['desc']) ?></p>
          <?php if (!empty($featured['tags'])): ?>
            <div class="mt-3 flex flex-wrap gap-1.5">
              <?php foreach ($featured['tags'] as $tag): ?>
                <span class="rounded-full bg-white/10 px-2.5 py-0.5 text-[10px] font-bold"><?= htmlspecialchars($tag) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if ($featured['link'] !== '#'): ?>
            <a href="<?= htmlspecialchars($featured['link']) ?>" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 rounded-full bg-white text-deep px-5 py-2 text-xs font-extrabold hover:shadow-sleek-sm transition-all">Visit project <?= icon('external-link', 'w-4 h-4') ?></a>
          <?php endif; ?>
        </div>
      </article>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($others as $i => $p): ?>
          <article class="reveal reveal-scale reveal-delay-<?= min(($i % 5) + 1, 5) ?> rounded-2xl bg-cloud border border-line p-5 sm:p-6 flex flex-col">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue/10 text-blue"><?= icon($p['icon'], 'w-5 h-5') ?></span>
            <p class="mt-3 text-[10px] font-extrabold text-blue uppercase tracking-[0.18em]"><?= htmlspecialchars($p['category']) ?></p>
            <h3 class="mt-1 text-lg font-extrabold"><?= htmlspecialchars($p['title']) ?></h3>
            <p class="mt-2 text-xs text-body leading-relaxed flex-grow"><?= htmlspecialchars($p['desc']) ?></p>
            <?php if ($p['link'] !== '#'): ?>
              <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center gap-1.5 text-xs font-extrabold text-blue hover:gap-2 transition-all">View <?= icon('external-link', 'w-3.5 h-3.5') ?></a>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
