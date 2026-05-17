<?php
/** @var array|null $slide @var array $slides @var array $dayNames @var bool $isNew */
$slide = $slide ?? [];
$slideId = $slide['id'] ?? '';
$slideDays = $slide['schedule_days'] ?? [];
if (!is_array($slideDays)) {
  $slideDays = [];
}
?>
<input type="hidden" name="action" value="save_home_hero_slide" />
<input type="hidden" name="id" value="<?= htmlspecialchars($slideId) ?>" />

<?php if (!empty($slide['image'])): ?>
  <img src="<?= asset(seo_resolve_image_path($slide['image'])) ?>" alt="" class="admin-hero-item__preview" />
<?php endif; ?>

<div class="admin-hero-form-section">
  <p class="admin-hero-form-section__title">Image</p>
  <label class="admin-field">
    <span class="admin-field__label">Upload</span>
    <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="admin-input" />
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Path</span>
    <input name="image" class="admin-input font-mono text-xs" value="<?= htmlspecialchars($slide['image'] ?? '') ?>" />
  </label>
  <label class="admin-field">
    <span class="admin-field__label">Alt text</span>
    <input name="alt" class="admin-input" value="<?= htmlspecialchars($slide['alt'] ?? '') ?>" />
  </label>
</div>

<div class="admin-hero-form-section">
  <p class="admin-hero-form-section__title">Visibility</p>
  <label class="admin-field">
    <span class="admin-field__label">Sort order</span>
    <input name="sort_order" type="number" class="admin-input" value="<?= (int) ($slide['sort_order'] ?? count($slides)) ?>" />
  </label>
  <label class="admin-toggle">
    <input type="checkbox" name="published" value="1" <?= $isNew || !empty($slide['published']) ? 'checked' : '' ?> />
    <span class="text-sm font-bold text-ink">Show on homepage</span>
  </label>
</div>

<div class="admin-hero-form-section">
  <p class="admin-hero-form-section__title">Schedule (Ghana)</p>
  <label class="admin-toggle mb-2">
    <input type="checkbox" name="monday_only" value="1" <?= !empty($slide['monday_only']) ? 'checked' : '' ?> />
    <span class="text-sm font-bold text-ink">Mondays only</span>
  </label>
  <div class="grid grid-cols-2 gap-2">
    <label class="admin-field">
      <span class="admin-field__label">From</span>
      <input name="schedule_from" type="datetime-local" class="admin-input text-xs" value="<?= htmlspecialchars(cms_home_hero_format_schedule_datetime($slide['schedule_from'] ?? '')) ?>" />
    </label>
    <label class="admin-field">
      <span class="admin-field__label">Until</span>
      <input name="schedule_until" type="datetime-local" class="admin-input text-xs" value="<?= htmlspecialchars(cms_home_hero_format_schedule_datetime($slide['schedule_until'] ?? '')) ?>" />
    </label>
  </div>
  <div class="admin-hero-days mt-2">
    <?php for ($d = 1; $d <= 7; $d++): ?>
      <label class="admin-hero-day">
        <input type="checkbox" name="schedule_day_<?= $d ?>" value="1" <?= in_array($d, $slideDays, true) || (!empty($slide['monday_only']) && $d === 1) ? 'checked' : '' ?> />
        <?= $dayNames[$d] ?>
      </label>
    <?php endfor; ?>
  </div>
  <div class="grid grid-cols-2 gap-2 mt-2">
    <label class="admin-field">
      <span class="admin-field__label">Daily from</span>
      <input name="schedule_time_from" type="time" class="admin-input text-xs" value="<?= htmlspecialchars($slide['schedule_time_from'] ?? '') ?>" />
    </label>
    <label class="admin-field">
      <span class="admin-field__label">Daily until</span>
      <input name="schedule_time_until" type="time" class="admin-input text-xs" value="<?= htmlspecialchars($slide['schedule_time_until'] ?? '') ?>" />
    </label>
  </div>
  <label class="admin-toggle mt-2">
    <input type="checkbox" name="schedule_exclusive" value="1" <?= !empty($slide['schedule_exclusive']) || !empty($slide['monday_only']) ? 'checked' : '' ?> />
    <span class="text-sm font-bold text-ink">Exclusive (only this slide when active)</span>
  </label>
</div>

<div class="flex flex-wrap gap-2 pt-3 mt-3 border-t border-line">
  <button type="submit" class="admin-btn admin-btn--primary admin-btn--sm"><?= admin_icon('save') ?> Save</button>
  <?php if (!$isNew && $slideId !== ''): ?>
    <button type="submit" form="delete-<?= htmlspecialchars($slideId) ?>" class="admin-btn admin-btn--ghost admin-btn--sm text-red-600 ml-auto">Delete</button>
  <?php endif; ?>
</div>
