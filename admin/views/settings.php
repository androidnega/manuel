<?php
global $site;
$maintenance = cms_maintenance_config($pdo);
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
  <p class="text-sm text-body">When enabled, visitors see a sleek countdown page (logo header only). You stay signed in and can still use the admin.</p>

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

<form method="post" action="<?= url('login') ?>" class="admin-card max-w-xl space-y-3 mt-6">
  <input type="hidden" name="action" value="change_password" />
  <p class="admin-card__title">Change password</p>
  <label class="admin-field">
    <span class="admin-field__label">New password (min 8 chars)</span>
    <input name="password" type="password" required minlength="8" class="admin-input" />
  </label>
  <button type="submit" class="admin-btn admin-btn--ghost">Update password</button>
</form>
