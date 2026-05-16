<?php
/** @var string $src */
/** @var string $alt */
/** @var string $fit */
$fit = $fit ?? 'cover';
$frameClass = $frameClass ?? '';
$imgClass = $imgClass ?? '';

$frames = [
  'portrait' => 'aspect-[4/5] max-h-[min(380px,62vh)]',
  'poster' => 'aspect-[3/4] max-h-[min(340px,52vh)]',
  'wide' => 'aspect-[16/10] max-h-[min(240px,38vh)]',
  'square' => 'aspect-square max-h-[min(280px,42vh)]',
  'card' => 'aspect-[5/4] max-h-[200px]',
  'natural' => '',
];
$isNatural = $fit === 'natural';
$frameSize = $frames[$fit] ?? $frames['portrait'];
$objectFit = in_array($fit, ['poster', 'wide', 'square', 'natural'], true) ? 'object-contain' : 'object-cover object-[center_12%]';
$boxPadding = (!$isNatural && in_array($fit, ['poster', 'wide', 'square'], true)) ? ' p-1' : '';
?>
<?php if ($isNatural): ?>
<figure class="w-fit max-w-full <?= $frameClass ?>">
  <img
    src="<?= asset($src) ?>"
    alt="<?= htmlspecialchars($alt) ?>"
    class="block w-auto max-w-full h-auto rounded-2xl border border-line shadow-sleek-sm <?= $imgClass ?>"
    loading="lazy"
    decoding="async"
  />
</figure>
<?php else: ?>
<div class="relative overflow-hidden flex items-center justify-center w-full bg-deep <?= $frameSize ?> <?= $frameClass ?>">
  <img
    src="<?= asset($src) ?>"
    alt="<?= htmlspecialchars($alt) ?>"
    class="h-full w-full block <?= $objectFit ?><?= $boxPadding ?> <?= $imgClass ?>"
    loading="lazy"
    decoding="async"
  />
</div>
<?php endif; ?>
