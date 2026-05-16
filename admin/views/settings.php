<?php
global $site;
$maintenance = cms_maintenance_config($pdo);
$mail = cms_mail_config($pdo);
$modal = cms_newsletter_modal_config($pdo);
$subscribers = cms_newsletter_subscribers($pdo);
$endsLocal = '';
if (!empty($maintenance['ends_at'])) {
  $ts = strtotime($maintenance['ends_at']);
  if ($ts !== false) {
    $endsLocal = date('Y-m-d\TH:i', $ts);
  }
}
?>
<div class="admin-intro">
  <p class="admin-intro__text">Site contact details, update mode, and admin password.</p>
</div>

<form method="post" action="<?= url('login') ?>" class="admin-card max-w-xl space-y-4">
  <input type="hidden" name="action" value="save_maintenance" />
  <p class="admin-card__title">Site update mode</p>
  <p class="text-sm text-body">When enabled, visitors see a white countdown page (no site header). You stay signed in and can still use the admin.</p>

  <label class="admin-toggle">
    <input type="checkbox" name="maintenance_enabled" value="1" <?= $maintenance['enabled'] ? 'checked' : '' ?> />
    <span class="text-sm font-bold text-ink">Put site on update</span>
  </label>

  <label class="admin-field">
    <span class="admin-field__label">Back online at (optional)</span>
    <input type="datetime-local" name="maintenance_ends_at" value="<?= htmlspecialchars($endsLocal) ?>" class="admin-input" />
  </label>

  <label class="admin-field">
    <span class="admin-field__label">Headline</span>
    <input name="maintenance_title" value="<?= htmlspecialchars($maintenance['title']) ?>" class="admin-input" placeholder="We're updating the site" />
  </label>

  <label class="admin-field">
    <span class="admin-field__label">Caption</span>
    <textarea name="maintenance_caption" rows="2" class="admin-textarea" placeholder="Short message for visitors"><?= htmlspecialchars($maintenance['caption']) ?></textarea>
  </label>

  <div class="flex flex-wrap gap-2 pt-1">
    <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save update mode</button>
    <a href="<?= page_url('update.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn--ghost admin-btn--sm">Preview update page</a>
  </div>
</form>

<form method="post" action="<?= url('login') ?>" class="admin-card max-w-xl space-y-3 mt-6">
  <input type="hidden" name="action" value="save_site" />
  <p class="admin-card__title">Contact info</p>
  <?php foreach (['name' => 'Site name', 'title' => 'Your name', 'tagline' => 'Tagline', 'email' => 'Email', 'phone' => 'Phone', 'website' => 'Website', 'whatsapp' => 'WhatsApp URL'] as $key => $label): ?>
  <label class="admin-field">
    <span class="admin-field__label"><?= htmlspecialchars($label) ?></span>
    <input name="<?= $key ?>" value="<?= htmlspecialchars($site[$key] ?? '') ?>" class="admin-input" />
  </label>
  <?php endforeach; ?>
  <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save contact info</button>
</form>

<form method="post" action="<?= url('login') ?>" class="admin-card max-w-xl space-y-4 mt-6">
  <input type="hidden" name="action" value="save_mail" />
  <p class="admin-card__title">Newsletter email</p>
  <p class="text-sm text-body">Uses PHP <code class="text-xs">mail()</code> on your server. Set a valid From address so subscribers receive new posts.</p>
  <label class="admin-field">
    <span class="admin-field__label">From email</span>
    <input name="from_email" type="email" class="admin-input" value="<?= htmlspecialchars($mail['from_email']) ?>" placeholder="<?= htmlspecialchars($site['email'] ?? '') ?>" />
  </label>
  <label class="admin-field">
    <span class="admin-field__label">From name</span>
    <input name="from_name" class="admin-input" value="<?= htmlspecialchars($mail['from_name']) ?>" />
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Reply-to (optional)</span>
    <input name="reply_to" type="email" class="admin-input" value="<?= htmlspecialchars($mail['reply_to']) ?>" />
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Email subject prefix</span>
    <input name="newsletter_subject" class="admin-input" value="<?= htmlspecialchars($mail['newsletter_subject']) ?>" />
  </label>
  <label class="admin-toggle">
    <input type="checkbox" name="notify_on_news" value="1" <?= !empty($mail['notify_on_news']) ? 'checked' : '' ?> />
    <span class="text-sm font-bold text-ink">Allow emailing subscribers when publishing news</span>
  </label>
  <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save mail settings</button>
</form>

<form method="post" action="<?= url('login') ?>" class="admin-card max-w-xl space-y-4 mt-6">
  <input type="hidden" name="action" value="save_newsletter_modal" />
  <p class="admin-card__title">Subscribe modal</p>
  <p class="text-sm text-body">Shown when visitors scroll near the bottom of a page (poster + email form).</p>
  <label class="admin-toggle">
    <input type="checkbox" name="modal_enabled" value="1" <?= $modal['enabled'] ? 'checked' : '' ?> />
    <span class="text-sm font-bold text-ink">Enable modal</span>
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Show after scroll (%)</span>
    <input name="scroll_percent" type="number" min="50" max="98" class="admin-input" value="<?= (int) $modal['scroll_percent'] ?>" />
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Title</span>
    <input name="modal_title" class="admin-input" value="<?= htmlspecialchars($modal['title']) ?>" />
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Subtitle</span>
    <textarea name="modal_subtitle" rows="2" class="admin-textarea"><?= htmlspecialchars($modal['subtitle']) ?></textarea>
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Button text</span>
    <input name="modal_button_text" class="admin-input" value="<?= htmlspecialchars($modal['button_text']) ?>" />
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Poster image path</span>
    <input name="modal_image" class="admin-input" value="<?= htmlspecialchars($modal['image']) ?>" />
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Success message</span>
    <input name="modal_success_message" class="admin-input" value="<?= htmlspecialchars($modal['success_message']) ?>" />
  </label>
  <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save modal</button>
</form>

<?php if ($subscribers): ?>
<div class="admin-card max-w-xl mt-6">
  <p class="admin-card__title">Subscribers (<?= count($subscribers) ?>)</p>
  <ul class="mt-3 space-y-2 max-h-64 overflow-y-auto text-sm">
    <?php foreach ($subscribers as $sub): ?>
    <li class="flex items-center justify-between gap-2 border-b border-line pb-2">
      <span><?= htmlspecialchars($sub['email']) ?></span>
      <form method="post" action="<?= url('login') ?>" onsubmit="return confirm('Remove subscriber?');">
        <input type="hidden" name="action" value="delete_subscriber" />
        <input type="hidden" name="id" value="<?= (int) $sub['id'] ?>" />
        <button type="submit" class="text-xs font-bold text-red-600">Remove</button>
      </form>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="post" action="<?= url('login') ?>" class="admin-card max-w-xl space-y-3 mt-6">
  <input type="hidden" name="action" value="change_password" />
  <p class="admin-card__title">Change password</p>
  <label class="admin-field">
    <span class="admin-field__label">New password (min 8 chars)</span>
    <input name="password" type="password" required minlength="8" class="admin-input" />
  </label>
  <button type="submit" class="admin-btn admin-btn--ghost">Update password</button>
</form>
