<?php require_once __DIR__ . '/icon.php'; ?>
<button
  type="button"
  id="backToTop"
  class="btt"
  aria-label="Back to top"
  aria-hidden="true"
  tabindex="-1"
>
  <span class="btt__shine" aria-hidden="true"></span>
  <span class="btt__waves" aria-hidden="true">
    <span class="btt__wave"></span>
    <span class="btt__wave btt__wave--2"></span>
    <span class="btt__wave btt__wave--3"></span>
  </span>
  <span class="btt__ripple" aria-hidden="true"></span>
  <span class="btt__icon"><?= icon('chevron-up', 'w-5 h-5') ?></span>
</button>
