<?php
/** @var string $label */
/** @var string $title */
/** @var string|null $ctaHref */
/** @var string|null $ctaLabel */
?>
<div class="reveal flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
  <div class="max-w-2xl">
    <?php if (!empty($label)): ?>
      <p class="text-xs font-extrabold text-blue uppercase tracking-[0.2em]"><?= htmlspecialchars($label) ?></p>
    <?php endif; ?>
    <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-ink"><?= htmlspecialchars($title) ?></h2>
    <?php if (!empty($lead)): ?>
      <p class="mt-3 text-[0.9375rem] leading-relaxed text-body"><?= htmlspecialchars($lead) ?></p>
    <?php endif; ?>
  </div>
  <?php if (!empty($ctaHref) && !empty($ctaLabel)): ?>
    <a href="<?= page_url($ctaHref) ?>" class="inline-flex w-fit items-center gap-2 rounded-full border border-line px-4 py-2.5 text-sm font-extrabold hover:border-ink hover:shadow-sleek-sm transition-all shrink-0">
      <?= htmlspecialchars($ctaLabel) ?> <?= icon('arrow-right', 'w-4 h-4') ?>
    </a>
  <?php endif; ?>
</div>
