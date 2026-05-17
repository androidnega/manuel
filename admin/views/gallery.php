<?php
global $designs;
$allDesigns = cms_designs_all($pdo, $designs);
$editId = trim($_GET['id'] ?? '');
$edit = $editId !== '' ? cms_design_by_id($allDesigns, $editId) : null;
?>
<div class="admin-intro admin-intro--row">
  <div>
    <p class="admin-intro__text">Add posters and graphics for the public <a href="<?= page_url('designs.php') ?>" target="_blank" rel="noopener" class="text-blue font-bold">designs gallery</a>. Each item can be shared on WhatsApp.</p>
    <a href="<?= url('login') ?>?p=list&key=designs" class="admin-link text-xs font-bold mt-1 inline-block">Advanced: edit raw JSON</a>
  </div>
  <a href="<?= url('login') ?>?p=gallery" class="admin-btn admin-btn--primary admin-btn--sm"><?= admin_icon('save') ?> New design</a>
</div>

<div class="admin-grid admin-grid--2-lg mt-4">
  <div class="space-y-3">
    <?php if (!$allDesigns): ?>
      <p class="admin-card text-sm text-body">No designs yet. Add one on the right.</p>
    <?php endif; ?>
    <?php foreach ($allDesigns as $item):
      $thumb = trim($item['image'] ?? '');
      $published = !empty($item['published']);
    ?>
    <article class="admin-card flex gap-3 items-start">
      <?php if ($thumb !== ''): ?>
        <img src="<?= asset(seo_resolve_image_path($thumb)) ?>" alt="" class="w-16 h-16 rounded-lg object-cover shrink-0 border border-line" />
      <?php else: ?>
        <div class="w-16 h-16 rounded-lg bg-cloud border border-line shrink-0 flex items-center justify-center text-[10px] font-bold text-body">No img</div>
      <?php endif; ?>
      <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-2">
          <h3 class="font-extrabold text-sm truncate"><?= htmlspecialchars($item['title'] ?? '') ?></h3>
          <?php if (!$published): ?><span class="text-[10px] font-extrabold text-body bg-cloud px-2 py-0.5 rounded-full">Hidden</span><?php endif; ?>
        </div>
        <p class="text-xs text-body mt-0.5"><?= htmlspecialchars($item['type'] ?? '') ?></p>
        <div class="mt-2 flex flex-wrap gap-2">
          <a href="<?= url('login') ?>?p=gallery&id=<?= urlencode($item['id']) ?>" class="text-xs font-bold text-blue">Edit</a>
          <form method="post" action="<?= url('login') ?>" class="inline" onsubmit="return confirm('Delete this design?');">
            <input type="hidden" name="action" value="delete_design" />
            <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>" />
            <button type="submit" class="text-xs font-bold text-red-600">Delete</button>
          </form>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <form class="admin-card space-y-4 h-fit lg:sticky lg:top-24" method="post" action="<?= url('login') ?>" enctype="multipart/form-data">
    <p class="admin-card__title"><?= $edit ? 'Edit design' : 'Add design' ?></p>
    <input type="hidden" name="action" value="save_design" />
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit['id'] ?? '') ?>" />

    <label class="admin-field">
      <span class="admin-field__label">Title</span>
      <input name="title" required class="admin-input" value="<?= htmlspecialchars($edit['title'] ?? '') ?>" />
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Type / category</span>
      <input name="type" required class="admin-input" placeholder="Quote design, Promo, Brand…" value="<?= htmlspecialchars($edit['type'] ?? '') ?>" />
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Image alt text (SEO)</span>
      <input name="alt" class="admin-input" value="<?= htmlspecialchars($edit['alt'] ?? '') ?>" />
    </label>

    <label class="admin-field">
      <span class="admin-field__label">WhatsApp share message (optional)</span>
      <textarea name="share_text" rows="3" class="admin-textarea" placeholder="Leave blank to auto-generate title, image link and gallery URL."><?= htmlspecialchars($edit['share_text'] ?? '') ?></textarea>
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Layout</span>
      <select name="variant" class="admin-input">
        <?php
        $variants = ['' => 'Image post', 'campaign' => 'Campaign placeholder', 'ui' => 'UI placeholder', 'brand' => 'Brand placeholder'];
        $cur = $edit['variant'] ?? '';
        foreach ($variants as $val => $label):
        ?>
        <option value="<?= htmlspecialchars($val) ?>" <?= $cur === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Sort order (lower = first)</span>
      <input name="sort_order" type="number" class="admin-input" value="<?= (int) ($edit['sort_order'] ?? 0) ?>" />
    </label>

    <label class="admin-toggle">
      <input type="checkbox" name="published" value="1" <?= !$edit || !empty($edit['published']) ? 'checked' : '' ?> />
      <span class="text-sm font-bold text-ink">Published on site</span>
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Image file</span>
      <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="admin-input" />
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Or image path</span>
      <input name="image" class="admin-input" placeholder="assets/images/gallery/…" value="<?= htmlspecialchars($edit['image'] ?? '') ?>" />
    </label>

    <?php if ($edit && !empty($edit['image'])): ?>
      <img src="<?= asset(seo_resolve_image_path($edit['image'])) ?>" alt="" class="rounded-lg border border-line max-h-40 object-contain" />
    <?php endif; ?>

    <div class="flex flex-wrap gap-2 pt-1">
      <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save</button>
      <?php if ($edit): ?>
        <a href="<?= url('login') ?>?p=gallery" class="admin-btn admin-btn--ghost">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</div>
