<?php
$slides = cms_home_hero_slides($pdo);
$editId = trim($_GET['id'] ?? '');
$edit = $editId !== '' ? cms_home_hero_by_id($slides, $editId) : null;
?>
<div class="admin-intro admin-intro--row">
  <div>
    <p class="admin-intro__text">Images for the homepage hero slideshow (right side). Smooth fade between slides — no arrows or dots on the site.</p>
    <a href="<?= page_url('index.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-link text-xs font-bold mt-1 inline-block">Preview homepage</a>
  </div>
  <a href="<?= url('login') ?>?p=homehero" class="admin-btn admin-btn--primary admin-btn--sm"><?= admin_icon('save') ?> Add slide</a>
</div>

<div class="admin-grid admin-grid--2-lg mt-4">
  <div class="space-y-3">
    <?php if (!$slides): ?>
      <p class="admin-card text-sm text-body">No slides yet.</p>
    <?php endif; ?>
    <?php foreach ($slides as $item):
      $thumb = trim($item['image'] ?? '');
      $published = !empty($item['published']);
    ?>
    <article class="admin-card flex gap-3 items-start">
      <?php if ($thumb !== ''): ?>
        <img src="<?= asset(seo_resolve_image_path($thumb)) ?>" alt="" class="w-20 h-20 rounded-lg object-cover shrink-0 border border-line" />
      <?php else: ?>
        <div class="w-20 h-20 rounded-lg bg-cloud border border-line shrink-0"></div>
      <?php endif; ?>
      <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-[10px] font-extrabold text-body">#<?= (int) ($item['sort_order'] ?? 0) ?></span>
          <?php if (!$published): ?><span class="text-[10px] font-extrabold bg-cloud px-2 py-0.5 rounded-full">Hidden</span><?php endif; ?>
        </div>
        <p class="text-xs text-body mt-1 truncate"><?= htmlspecialchars($item['alt'] ?? basename($thumb)) ?></p>
        <p class="text-[10px] text-body font-mono truncate mt-0.5"><?= htmlspecialchars($thumb) ?></p>
        <div class="mt-2 flex gap-2">
          <a href="<?= url('login') ?>?p=homehero&id=<?= urlencode($item['id']) ?>" class="text-xs font-bold text-blue">Edit</a>
          <form method="post" action="<?= url('login') ?>" class="inline" onsubmit="return confirm('Remove this slide?');">
            <input type="hidden" name="action" value="delete_home_hero_slide" />
            <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>" />
            <button type="submit" class="text-xs font-bold text-red-600">Delete</button>
          </form>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <form class="admin-card space-y-4 h-fit lg:sticky lg:top-24" method="post" action="<?= url('login') ?>" enctype="multipart/form-data">
    <p class="admin-card__title"><?= $edit ? 'Edit slide' : 'Add slide' ?></p>
    <input type="hidden" name="action" value="save_home_hero_slide" />
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit['id'] ?? '') ?>" />

    <label class="admin-field">
      <span class="admin-field__label">Alt text (accessibility & SEO)</span>
      <input name="alt" class="admin-input" value="<?= htmlspecialchars($edit['alt'] ?? '') ?>" placeholder="Describe the image" />
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Sort order (lower = first)</span>
      <input name="sort_order" type="number" class="admin-input" value="<?= (int) ($edit['sort_order'] ?? count($slides)) ?>" />
    </label>

    <label class="admin-toggle">
      <input type="checkbox" name="published" value="1" <?= !$edit || !empty($edit['published']) ? 'checked' : '' ?> />
      <span class="text-sm font-bold text-ink">Show on homepage</span>
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Upload image</span>
      <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="admin-input" />
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Or image path</span>
      <input name="image" class="admin-input" placeholder="assets/images/home-hero/…" value="<?= htmlspecialchars($edit['image'] ?? '') ?>" />
    </label>

    <?php if ($edit && !empty($edit['image'])): ?>
      <img src="<?= asset(seo_resolve_image_path($edit['image'])) ?>" alt="" class="rounded-lg border border-line max-h-48 w-full object-contain" />
    <?php endif; ?>

    <label class="admin-field">
      <span class="admin-field__label">Seconds per slide (homepage)</span>
      <input name="slide_interval" type="number" min="3" max="600" class="admin-input" value="<?= (int) (cms_get_setting($pdo, 'home_hero_interval', '180000') / 1000) ?>" />
      <span class="text-xs text-body">Default 180 = 3 minutes between slides (loops continuously).</span>
    </label>

    <div class="flex flex-wrap gap-2 pt-1">
      <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save slide</button>
      <?php if ($edit): ?>
        <a href="<?= url('login') ?>?p=homehero" class="admin-btn admin-btn--ghost">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</div>
