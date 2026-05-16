<?php
$logoVariant = $logoVariant ?? 'wordmark';
$logoTheme = $logoTheme ?? ($logoVariant === 'dark' ? 'dark' : 'light');
$logoWrapClass = $logoWrapClass ?? '';
$showTagline = $showTagline ?? true;
$showIcon = $showIcon ?? false;
$logoAlign = $logoAlign ?? 'start';

if ($logoVariant === 'wordmark' || $logoVariant === 'light' || $logoVariant === 'dark') {
  if ($logoVariant === 'dark') {
    $logoTheme = 'dark';
  }
  $isDark = $logoTheme === 'dark';
  $iconSizeClass = $iconSizeClass ?? ($isDark ? 'brand-icon brand-icon--lg' : 'brand-icon');
  $textClass = $textClass ?? ($isDark
    ? 'text-2xl sm:text-3xl font-extrabold tracking-[-0.03em] leading-none'
    : 'text-xl sm:text-2xl lg:text-[1.7rem] font-extrabold tracking-[-0.03em] leading-none');
  $taglineClass = $taglineClass ?? ($isDark
    ? 'text-[9px] sm:text-[10px] font-medium tracking-[0.26em] text-white/80 lowercase leading-none'
    : 'text-[9px] sm:text-[10px] font-medium tracking-[0.26em] text-deep lowercase leading-none');
  $lineClass = 'h-px w-4 sm:w-5 bg-blue shrink-0';
  $isCentered = $logoAlign === 'center';
  $rowClass = $isCentered ? 'flex-col items-center text-center' : 'items-center';
  $colClass = $isCentered ? 'items-center' : 'items-start';
  $taglineRow = $isCentered ? 'justify-center' : '';
  ?>
  <span class="inline-flex gap-2.5 sm:gap-3 <?= $rowClass ?> <?= htmlspecialchars(trim($logoWrapClass)) ?>">
    <?php if ($showIcon): ?>
      <span class="<?= htmlspecialchars(trim($iconSizeClass)) ?>" aria-hidden="true">
        <img
          src="<?= asset($brand['favicon']) ?>"
          alt=""
          class="brand-icon__img"
          width="52"
          height="52"
          decoding="async"
        />
      </span>
    <?php endif; ?>
    <span class="inline-flex flex-col min-w-0 gap-0 <?= $colClass ?>">
      <span class="<?= htmlspecialchars(trim($textClass)) ?> leading-none" aria-hidden="true">
        <span class="brand-manuel <?= $isDark ? 'text-white' : 'text-deep' ?>">Manuel</span><span class="text-blue">code</span>
      </span>
      <?php if ($showTagline): ?>
        <span class="brand-tagline flex items-center gap-1.5 <?= $taglineRow ?>" aria-hidden="true">
          <span class="<?= $lineClass ?>"></span>
          <span class="brand-tagline-site <?= htmlspecialchars(trim($taglineClass)) ?>"><?= htmlspecialchars($site['website']) ?></span>
          <span class="<?= $lineClass ?>"></span>
        </span>
      <?php endif; ?>
    </span>
    <span class="sr-only"><?= htmlspecialchars($site['name']) ?> — <?= htmlspecialchars($site['website']) ?></span>
  </span>
  <?php
  return;
}

if ($logoVariant === 'icon') {
  $src = $brand['favicon'];
  $logoClass = $logoClass ?? 'h-12 w-12 rounded-xl object-cover shrink-0 ring-1 ring-line';
} elseif ($logoVariant === 'dark') {
  $src = $brand['logo_dark'];
  $logoClass = $logoClass ?? 'h-11 sm:h-14 w-auto max-w-[300px] object-contain object-left';
} else {
  $src = $brand['logo'];
  $logoClass = $logoClass ?? 'h-11 sm:h-14 w-auto max-w-[300px] object-contain object-left';
}
?>
<img src="<?= asset($src) ?>" alt="<?= htmlspecialchars($site['name']) ?> — <?= htmlspecialchars($site['website']) ?>" class="<?= htmlspecialchars(trim($logoClass)) ?>" decoding="async" />
