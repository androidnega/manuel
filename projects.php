<?php
require_once __DIR__ . '/includes/data.php';
$cms = cms_page('projects', [
  'label' => 'Selected work',
  'title' => 'Projects and systems I’ve worked on.',
  'desc' => 'Practical systems for schools, students, businesses and organizations.',
  'body' => [
    'work_eyebrow' => 'How I work',
    'work_title' => 'From brief to launch — clear, practical and shipped.',
    'work_lead' => 'Every project gets the same focus: understand the real problem, design something people can use, build it properly, and deliver on time with room to grow.',
    'work_image' => 'assets/images/manuelcode-how-i-work-on-projects-laptop-ghana.jpg',
    'work_image_alt' => 'Manuel Kwofie working on Manuelcode projects — web and software development in Ghana',
  ],
]);
$pageTitle = 'Projects | Manuelcode.info';
$pageStyles = ['assets/css/work-process.css'];
$heroLabel = $cms['label'];
$heroTitle = $cms['title'];
$heroDesc = $cms['desc'];
$pageBody = $cms['body'];
$workProcess = [
  'eyebrow' => $pageBody['work_eyebrow'] ?? 'How I work',
  'title' => $pageBody['work_title'] ?? 'From brief to launch — clear, practical and shipped.',
  'lead' => $pageBody['work_lead'] ?? '',
  'image' => $pageBody['work_image'] ?? 'assets/images/manuelcode-how-i-work-on-projects-laptop-ghana.jpg',
  'image_alt' => $pageBody['work_image_alt'] ?? '',
];
$others = array_values(array_filter($projects, fn($p) => empty($p['featured'])));
include 'includes/header.php';
include 'includes/page-hero.php';
?>
<main>
  <section class="py-10 sm:py-12 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <?php include __DIR__ . '/includes/work-process-section.php'; ?>

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
