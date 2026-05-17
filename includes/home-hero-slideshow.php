<?php
/** Homepage hero slideshow (right column). Requires $homeHeroSlides array. */
$slides = $homeHeroSlides ?? [];
if ($slides === []) {
  return;
}
$interval = (int) ($homeHeroInterval ?? 180000);
if ($interval < 3000) {
  $interval = 180000;
}
$slideCount = 0;
foreach ($slides as $slide) {
  if (seo_resolve_image_path($slide['image'] ?? '') !== '') {
    $slideCount++;
  }
}
$isSlideshow = $slideCount >= 2;
$isMondayOnly = cms_home_hero_is_monday() && $slideCount === 1;
?>
<figure
  class="home-hero-slideshow is-visible<?= $isMondayOnly ? ' home-hero-slideshow--monday' : '' ?>"
  <?php if ($isSlideshow): ?>
  data-home-hero-slideshow
  data-interval="<?= $interval ?>"
  <?php endif; ?>
  aria-label="<?= $isMondayOnly ? 'Monday promo' : 'Featured work' ?>"
>
  <div class="home-hero-slideshow__frame">
    <div class="home-hero-slideshow__track">
      <?php foreach ($slides as $i => $slide):
        $path = seo_resolve_image_path($slide['image'] ?? '');
        if ($path === '') {
          continue;
        }
        $alt = trim($slide['alt'] ?? '') ?: 'Manuelcode — web developer and software engineer';
      ?>
      <img
        src="<?= asset($path) ?>"
        alt="<?= htmlspecialchars($alt) ?>"
        class="home-hero-slideshow__slide<?= $i === 0 ? ' is-active' : '' ?>"
        width="1024"
        height="1024"
        <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
        decoding="async"
        draggable="false"
      />
      <?php endforeach; ?>
    </div>
  </div>
</figure>
