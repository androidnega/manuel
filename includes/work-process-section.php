<?php
/** How I work on projects — two-column block (copy left, image right). */
global $site;
$work = $workProcess ?? [];
$imagePath = $work['image'] ?? 'assets/images/manuelcode-how-i-work-on-projects-laptop-ghana.jpg';
if (!is_file(dirname(__DIR__) . '/' . ltrim($imagePath, '/')) && is_file(dirname(__DIR__) . '/assets/images/manuelcode-how-i-work-on-projects-laptop-ghana.png')) {
  $imagePath = 'assets/images/manuelcode-how-i-work-on-projects-laptop-ghana.png';
}
$imagePath = seo_resolve_image_path($imagePath);
$eyebrow = $work['eyebrow'] ?? 'How I work';
$title = $work['title'] ?? 'From brief to launch — clear, practical and shipped.';
$lead = $work['lead'] ?? 'Every project gets the same focus: understand the real problem, design something people can use, build it properly, and deliver on time with room to grow.';
$steps = $work['steps'] ?? [
  ['title' => 'Listen & scope', 'text' => 'We align on goals, users, budget and timeline before any build starts.'],
  ['title' => 'Design & prototype', 'text' => 'UI, flows and structure — so you see direction early, not at the end.'],
  ['title' => 'Build & test', 'text' => 'Clean code, real devices, and fixes before anything goes live.'],
  ['title' => 'Launch & support', 'text' => 'Deploy, handover, and iterate when you need changes or new features.'],
];
$ctaLabel = $work['cta_label'] ?? 'Start a project';
$ctaHref = $work['cta_href'] ?? page_url('contact.php');
$imageAlt = $work['image_alt'] ?? 'Manuel Kwofie working on a laptop — Manuelcode web and software projects in Ghana';
?>
<section class="work-process reveal mb-8 sm:mb-10" aria-labelledby="work-process-title">
  <div class="work-process__copy">
    <p class="work-process__eyebrow"><?= htmlspecialchars($eyebrow) ?></p>
    <h2 id="work-process-title" class="work-process__title"><?= htmlspecialchars($title) ?></h2>
    <p class="work-process__lead"><?= htmlspecialchars($lead) ?></p>
    <ol class="work-process__steps list-none p-0 m-0">
      <?php foreach ($steps as $i => $step): ?>
        <li class="work-process__step">
          <span class="work-process__step-num"><?= (int) $i + 1 ?></span>
          <div>
            <p class="work-process__step-title"><?= htmlspecialchars($step['title'] ?? '') ?></p>
            <p class="work-process__step-text"><?= htmlspecialchars($step['text'] ?? '') ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
    <a href="<?= htmlspecialchars($ctaHref) ?>" class="work-process__cta">
      <?= htmlspecialchars($ctaLabel) ?> <?= icon('arrow-right', 'w-4 h-4') ?>
    </a>
  </div>
  <figure class="work-process__visual reveal reveal-right reveal-delay-2">
    <img
      src="<?= asset($imagePath) ?>"
      alt="<?= htmlspecialchars($imageAlt) ?>"
      class="work-process__img lazy-img"
      width="1024"
      height="1024"
      loading="lazy"
      decoding="async"
    />
  </figure>
</section>
