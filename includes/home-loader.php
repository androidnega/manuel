<?php
/** Icon-only brand mark (no Manuelcode / manuelcode.info text). */
$loaderIcon = $brand['favicon'] ?? 'assets/images/favicon.webp';
?>
<div id="homeLoader" class="home-loader" aria-live="polite" aria-busy="true">
  <div class="home-loader__inner">
    <div class="home-loader__logo brand-icon brand-icon--lg" aria-hidden="true">
      <img
        src="<?= asset($loaderIcon) ?>"
        alt=""
        class="brand-icon__img"
        width="124"
        height="124"
        decoding="async"
        fetchpriority="high"
      />
    </div>
    <div class="home-loader__bar-wrap">
      <div class="home-loader__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="homeLoaderBar">
        <span class="home-loader__fill" id="homeLoaderFill"></span>
      </div>
      <p class="home-loader__pct"><span id="homeLoaderPct">0</span>%</p>
    </div>
  </div>
</div>
