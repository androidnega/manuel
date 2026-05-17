<?php
$slug = $_GET['slug'] ?? '';
$allowed = ['home', 'projects', 'services', 'quotes', 'designs', 'about', 'contact'];
if (!in_array($slug, $allowed, true)) {
  echo '<p class="admin-flash admin-flash--err">Invalid page.</p>';
  return;
}
$row = cms_get_page($pdo, $slug) ?? ['hero_label' => '', 'hero_title' => '', 'hero_desc' => '', 'body' => []];
$body = $row['body'];
$fields = [
  'home' => [
    'badge' => 'Badge text',
    'intro' => 'Intro paragraph',
    'clients_label' => 'Clients section label',
    'clients_title' => 'Clients section title',
    'clients_lead' => 'Clients section lead',
    'pages_label' => 'Pages section label',
    'pages_title' => 'Pages section title',
    'pages_lead' => 'Pages section lead',
    'cta_title' => 'Bottom CTA title',
    'cta_text' => 'Bottom CTA text',
  ],
  'about' => ['paragraph1' => 'First paragraph', 'paragraph2' => 'Second paragraph', 'technical' => 'Technical skills', 'creative' => 'Creative skills', 'experience_title' => 'Experience section title'],
  'projects' => [
    'work_eyebrow' => 'How I work — eyebrow',
    'work_title' => 'How I work — title',
    'work_lead' => 'How I work — intro',
    'work_image' => 'How I work — image path',
    'work_image_alt' => 'How I work — image alt text',
  ],
  'contact' => ['intro_left' => 'Left intro', 'intro_right' => 'Form intro'],
  'services' => ['cta_title' => 'CTA title', 'cta_text' => 'CTA text'],
  'quotes' => [
    'intro_left' => 'Left column intro',
    'intro_right' => 'Form intro',
    'note_1' => 'Bullet note 1',
    'note_2' => 'Bullet note 2',
    'note_3' => 'Bullet note 3',
  ],
];
$extra = $fields[$slug] ?? [];
?>
<div class="admin-intro">
  <a href="<?= url('login') ?>?p=pages" class="admin-link">← All pages</a>
</div>

<form class="max-w-2xl space-y-4" method="post" action="<?= url('login') ?>">
  <input type="hidden" name="action" value="save_page" />
  <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>" />

  <div class="admin-card space-y-4">
    <p class="admin-card__title">Hero</p>
    <label class="admin-field">
      <span class="admin-field__label">Label</span>
      <input name="hero_label" value="<?= htmlspecialchars($row['hero_label'] ?? '') ?>" class="admin-input" />
    </label>
    <label class="admin-field">
      <span class="admin-field__label">Title</span>
      <input name="hero_title" value="<?= htmlspecialchars($row['hero_title'] ?? '') ?>" class="admin-input" />
    </label>
    <label class="admin-field">
      <span class="admin-field__label">Description</span>
      <textarea name="hero_desc" rows="2" class="admin-textarea"><?= htmlspecialchars($row['hero_desc'] ?? '') ?></textarea>
    </label>
  </div>

  <?php if ($extra): ?>
  <div class="admin-card space-y-4">
    <p class="admin-card__title">Page content</p>
    <?php foreach ($extra as $key => $label): ?>
      <label class="admin-field">
        <span class="admin-field__label"><?= htmlspecialchars($label) ?></span>
        <?php if (strlen($body[$key] ?? '') > 120): ?>
          <textarea name="body_<?= htmlspecialchars($key) ?>" rows="3" class="admin-textarea"><?= htmlspecialchars($body[$key] ?? '') ?></textarea>
        <?php else: ?>
          <input name="body_<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($body[$key] ?? '') ?>" class="admin-input" />
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save page</button>
</form>
