<?php
/** @var array $page */
$c = serviceColorClasses($page['color'] ?? 'blue');
?>
<a href="<?= page_url($page['href']) ?>" class="reveal reveal-scale <?= $cardRevealClass ?? '' ?> group rounded-2xl bg-white border border-line p-5 sm:p-6 flex flex-col h-full hover:border-blue/40 hover:shadow-sleek hover:-translate-y-1 transition-all duration-300 ease-out <?= $c['hover'] ?>">
  <div class="h-10 w-10 rounded-xl <?= $c['bg'] ?> <?= $c['text'] ?> grid place-items-center group-hover:scale-105 transition-transform duration-300">
    <?= icon($page['icon'] ?? 'layers', 'w-5 h-5') ?>
  </div>
  <h3 class="mt-4 text-base font-extrabold text-ink group-hover:text-blue transition-colors"><?= htmlspecialchars($page['title']) ?></h3>
  <p class="mt-2 text-xs text-body leading-relaxed flex-grow"><?= htmlspecialchars($page['desc']) ?></p>
  <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-extrabold text-blue">
    <?= htmlspecialchars($page['cta'] ?? 'Open page') ?> <?= icon('arrow-right', 'w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform') ?>
  </span>
</a>
