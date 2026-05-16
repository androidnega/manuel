<?php
if (!empty($skipNewsletterModal)) {
  return;
}
$pdo = cms_db();
$modal = cms_newsletter_modal_config($pdo);
if (!$modal['enabled']) {
  return;
}
$img = trim($modal['image'] ?? '');
if ($img === '') {
  $img = 'assets/images/quote-poster-original.jpg';
}
?>
<link rel="stylesheet" href="<?= asset('assets/css/newsletter-modal.css') ?>" />
<div
  id="newsletterModal"
  class="nl-modal"
  aria-hidden="true"
  role="dialog"
  aria-labelledby="nlModalTitle"
  data-scroll="<?= (int) $modal['scroll_percent'] ?>"
>
  <div class="nl-modal__backdrop" data-nl-close aria-hidden="true"></div>
  <div class="nl-modal__panel">
    <button type="button" class="nl-modal__close" aria-label="Close">&times;</button>
    <div class="nl-modal__visual">
      <img src="<?= asset($img) ?>" alt="" loading="lazy" />
    </div>
    <div class="nl-modal__content">
      <h2 id="nlModalTitle" class="nl-modal__title"><?= htmlspecialchars($modal['title']) ?></h2>
      <?php if (!empty($modal['subtitle'])): ?>
        <p class="nl-modal__subtitle"><?= htmlspecialchars($modal['subtitle']) ?></p>
      <?php endif; ?>
      <form id="newsletterForm" class="nl-modal__form" method="post" action="<?= url('newsletter-subscribe.php') ?>">
        <label class="sr-only" for="nlEmail">Email</label>
        <input id="nlEmail" class="nl-modal__input" type="email" name="email" required autocomplete="email" placeholder="you@example.com" />
        <button type="submit" class="nl-modal__submit"><?= htmlspecialchars($modal['button_text']) ?></button>
        <p class="nl-modal__msg" aria-live="polite"></p>
      </form>
    </div>
  </div>
</div>
