<?php
$slides = cms_home_hero_slides($pdo);
$editId = trim($_GET['id'] ?? '');
$edit = $editId !== '' ? cms_home_hero_by_id($slides, $editId) : null;
$previewSlides = cms_home_hero_public($pdo);
$intervalSec = (int) (cms_home_hero_interval_ms($pdo) / 1000);
$now = cms_home_hero_now();
$dayNames = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$editDays = $edit['schedule_days'] ?? [];
if (!is_array($editDays)) {
  $editDays = [];
}
?>
<link rel="stylesheet" href="<?= asset('assets/css/admin-home-hero.css') ?>" />

<div class="admin-intro admin-intro--row">
  <div>
    <p class="admin-intro__text">Manage homepage hero images. Schedule when each slide appears (Ghana time). <strong class="text-ink">Exclusive</strong> slides replace the whole slideshow while active.</p>
    <p class="text-xs text-body mt-1">Now: <?= htmlspecialchars($now->format('D, j M Y · H:i')) ?> (Africa/Accra)</p>
    <a href="<?= page_url('index.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-link text-xs font-bold mt-1 inline-block">Preview homepage</a>
  </div>
  <a href="<?= url('login') ?>?p=homehero" class="admin-btn admin-btn--primary admin-btn--sm"><?= admin_icon('save') ?> New slide</a>
</div>

<section class="admin-card mt-4">
  <form method="post" action="<?= url('login') ?>" class="flex flex-wrap items-end gap-4">
    <input type="hidden" name="action" value="save_home_hero_settings" />
    <label class="admin-field flex-1 min-w-[12rem]">
      <span class="admin-field__label">Seconds between slides</span>
      <input name="slide_interval" type="number" min="3" max="600" class="admin-input" value="<?= $intervalSec ?>" />
    </label>
    <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm"><?= admin_icon('save') ?> Save timing</button>
  </form>
</section>

<?php if ($previewSlides !== []): ?>
<section class="admin-card mt-4" aria-labelledby="hero-preview-heading">
  <h2 id="hero-preview-heading" class="font-extrabold text-sm">Live on homepage right now</h2>
  <p class="text-xs text-body mt-0.5">
    <?= count($previewSlides) ?> slide<?= count($previewSlides) === 1 ? '' : 's' ?>
    <?php if (count($previewSlides) > 1): ?> · <?= $intervalSec ?>s fade · loops<?php endif; ?>
  </p>
  <div class="admin-hero-preview-grid mt-4">
    <?php foreach ($previewSlides as $i => $slide):
      $path = trim($slide['image'] ?? '');
      $resolved = $path !== '' ? seo_resolve_image_path($path) : '';
      if ($resolved === '') {
        continue;
      }
    ?>
    <figure class="rounded-xl border border-line overflow-hidden bg-cloud">
      <div class="relative aspect-square bg-[#eeedea]">
        <img src="<?= asset($resolved) ?>" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy" />
        <span class="absolute top-1.5 left-1.5 text-[10px] font-extrabold bg-white/95 text-ink px-1.5 py-0.5 rounded-full border border-line">#<?= $i + 1 ?></span>
      </div>
    </figure>
    <?php endforeach; ?>
  </div>
</section>
<?php else: ?>
<p class="admin-card mt-4 text-sm text-body">No slides are active right now. Check schedules and “Show on homepage”.</p>
<?php endif; ?>

<div class="admin-hero-layout mt-4">
  <div>
    <p class="text-xs font-extrabold text-body uppercase tracking-wider mb-3">All slides (<?= count($slides) ?>)</p>
    <?php if (!$slides): ?>
      <p class="admin-card text-sm text-body">No slides yet. Use the form to add one.</p>
    <?php else: ?>
    <div class="admin-hero-slides-grid">
      <?php foreach ($slides as $item):
        $thumb = trim($item['image'] ?? '');
        $resolved = $thumb !== '' ? seo_resolve_image_path($thumb) : '';
        $isEditing = ($edit['id'] ?? '') === ($item['id'] ?? '');
        $isLive = cms_home_hero_slide_is_active($item, $now);
        $published = !empty($item['published']);
      ?>
      <a
        href="<?= url('login') ?>?p=homehero&id=<?= urlencode($item['id']) ?>"
        class="admin-hero-slide-card <?= $isEditing ? 'is-editing' : '' ?> <?= $isLive ? 'is-live' : '' ?>"
      >
        <?php if ($resolved !== ''): ?>
          <img src="<?= asset($resolved) ?>" alt="" class="admin-hero-slide-card__img" />
        <?php else: ?>
          <div class="admin-hero-slide-card__img grid place-items-center text-xs font-bold text-body">No image</div>
        <?php endif; ?>
        <div class="p-3">
          <div class="flex flex-wrap gap-1">
            <?php if ($isLive): ?><span class="text-[9px] font-extrabold bg-blue text-white px-1.5 py-0.5 rounded-full">Live</span><?php endif; ?>
            <?php if (!$published): ?><span class="text-[9px] font-extrabold bg-cloud px-1.5 py-0.5 rounded-full">Hidden</span><?php endif; ?>
            <span class="text-[9px] font-extrabold text-body">#<?= (int) ($item['sort_order'] ?? 0) ?></span>
          </div>
          <p class="text-xs font-bold text-ink mt-1.5 line-clamp-2"><?= htmlspecialchars($item['alt'] ?? basename($thumb)) ?></p>
          <p class="text-[10px] text-body mt-1 line-clamp-2"><?= htmlspecialchars(cms_home_hero_schedule_summary($item)) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <form class="admin-card space-y-0 lg:sticky lg:top-24" method="post" action="<?= url('login') ?>" enctype="multipart/form-data">
    <p class="admin-card__title mb-4"><?= $edit ? 'Edit slide' : 'Add slide' ?></p>
    <input type="hidden" name="action" value="save_home_hero_slide" />
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit['id'] ?? '') ?>" />

    <?php if ($edit && !empty($edit['image'])): ?>
      <img src="<?= asset(seo_resolve_image_path($edit['image'])) ?>" alt="" class="rounded-xl border border-line w-full aspect-square object-cover bg-[#eeedea] mb-4" />
    <?php endif; ?>

    <div class="admin-hero-form-section">
      <p class="admin-hero-form-section__title">Image</p>
      <label class="admin-field">
        <span class="admin-field__label">Upload</span>
        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="admin-input" />
      </label>
      <label class="admin-field">
        <span class="admin-field__label">Or path</span>
        <input name="image" class="admin-input font-mono text-xs" placeholder="assets/images/…" value="<?= htmlspecialchars($edit['image'] ?? '') ?>" />
      </label>
      <label class="admin-field">
        <span class="admin-field__label">Alt text</span>
        <input name="alt" class="admin-input" value="<?= htmlspecialchars($edit['alt'] ?? '') ?>" placeholder="Describe the image" />
      </label>
    </div>

    <div class="admin-hero-form-section">
      <p class="admin-hero-form-section__title">Order & visibility</p>
      <label class="admin-field">
        <span class="admin-field__label">Sort order (lower = first)</span>
        <input name="sort_order" type="number" class="admin-input" value="<?= (int) ($edit['sort_order'] ?? count($slides)) ?>" />
      </label>
      <label class="admin-toggle">
        <input type="checkbox" name="published" value="1" <?= !$edit || !empty($edit['published']) ? 'checked' : '' ?> />
        <span class="text-sm font-bold text-ink">Show on homepage (when schedule allows)</span>
      </label>
    </div>

    <div class="admin-hero-form-section">
      <p class="admin-hero-form-section__title">Schedule <span class="font-normal normal-case tracking-normal text-body">(Africa/Accra)</span></p>
      <p class="text-xs text-body mb-3">Leave empty for no limit. Combine date range, weekdays, and daily hours.</p>

      <label class="admin-toggle mb-3">
        <input type="checkbox" name="monday_only" value="1" <?= !empty($edit['monday_only']) ? 'checked' : '' ?> />
        <span class="text-sm font-bold text-ink">Mondays only (shortcut)</span>
      </label>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <label class="admin-field">
          <span class="admin-field__label">Show from</span>
          <input name="schedule_from" type="datetime-local" class="admin-input" value="<?= htmlspecialchars(cms_home_hero_format_schedule_datetime($edit['schedule_from'] ?? '')) ?>" />
        </label>
        <label class="admin-field">
          <span class="admin-field__label">Show until</span>
          <input name="schedule_until" type="datetime-local" class="admin-input" value="<?= htmlspecialchars(cms_home_hero_format_schedule_datetime($edit['schedule_until'] ?? '')) ?>" />
        </label>
      </div>

      <p class="text-xs font-bold text-ink mt-3 mb-2">Repeat on days</p>
      <div class="admin-hero-days">
        <?php for ($d = 1; $d <= 7; $d++): ?>
          <label class="admin-hero-day">
            <input type="checkbox" name="schedule_day_<?= $d ?>" value="1" <?= in_array($d, $editDays, true) || (!empty($edit['monday_only']) && $d === 1) ? 'checked' : '' ?> />
            <?= $dayNames[$d] ?>
          </label>
        <?php endfor; ?>
      </div>
      <p class="text-[10px] text-body mt-1">No days selected = every day (unless Mondays-only is checked).</p>

      <div class="grid grid-cols-2 gap-3 mt-3">
        <label class="admin-field">
          <span class="admin-field__label">Daily from</span>
          <input name="schedule_time_from" type="time" class="admin-input" value="<?= htmlspecialchars($edit['schedule_time_from'] ?? '') ?>" />
        </label>
        <label class="admin-field">
          <span class="admin-field__label">Daily until</span>
          <input name="schedule_time_until" type="time" class="admin-input" value="<?= htmlspecialchars($edit['schedule_time_until'] ?? '') ?>" />
        </label>
      </div>

      <label class="admin-toggle mt-3">
        <input type="checkbox" name="schedule_exclusive" value="1" <?= !empty($edit['schedule_exclusive']) || !empty($edit['monday_only']) ? 'checked' : '' ?> />
        <span class="text-sm font-bold text-ink">Exclusive — hide other slides while this one is active</span>
      </label>
    </div>

    <div class="flex flex-wrap gap-2 pt-4 mt-4 border-t border-line">
      <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save slide</button>
      <?php if ($edit): ?>
        <a href="<?= url('login') ?>?p=homehero" class="admin-btn admin-btn--ghost">Cancel</a>
      <?php endif; ?>
    </div>
  </form>

  <?php if ($edit): ?>
  <form method="post" action="<?= url('login') ?>" class="mt-3" onsubmit="return confirm('Remove this slide permanently?');">
    <input type="hidden" name="action" value="delete_home_hero_slide" />
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit['id']) ?>" />
    <button type="submit" class="admin-btn admin-btn--ghost w-full text-red-600 text-sm">Delete this slide</button>
  </form>
  <?php endif; ?>
</div>
