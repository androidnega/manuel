<?php
$slides = cms_home_hero_slides($pdo);
$editId = trim($_GET['id'] ?? '');
$isNew = isset($_GET['new']);
$edit = $editId !== '' ? cms_home_hero_by_id($slides, $editId) : null;
$previewSlides = cms_home_hero_public($pdo);
$intervalSec = (int) (cms_home_hero_interval_ms($pdo) / 1000);
$now = cms_home_hero_now();
$dayNames = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
?>
<link rel="stylesheet" href="<?= asset('assets/css/admin-home-hero.css') ?>" />

<div class="admin-intro admin-intro--row">
  <div>
    <p class="admin-intro__text">Click a slide to open its settings. Thumbnails are compact — expand only what you need.</p>
    <p class="text-xs text-body mt-1"><?= htmlspecialchars($now->format('D, j M Y · H:i')) ?> · Africa/Accra</p>
    <a href="<?= page_url('index.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-link text-xs font-bold mt-1 inline-block">Preview homepage</a>
  </div>
  <a href="<?= url('login') ?>?p=homehero&new=1" class="admin-btn admin-btn--primary admin-btn--sm"><?= admin_icon('save') ?> New slide</a>
</div>

<section class="admin-card mt-4">
  <form method="post" action="<?= url('login') ?>" class="admin-hero-timing">
    <input type="hidden" name="action" value="save_home_hero_settings" />
    <label class="admin-field flex-1 min-w-[10rem] max-w-xs">
      <span class="admin-field__label">Seconds between slides</span>
      <input name="slide_interval" type="number" min="3" max="600" class="admin-input" value="<?= $intervalSec ?>" />
    </label>
    <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm"><?= admin_icon('save') ?> Save timing</button>
  </form>
  <?php if ($previewSlides !== []): ?>
    <p class="text-xs font-bold text-ink mt-3">Live now (<?= count($previewSlides) ?>)</p>
    <div class="admin-hero-live-strip">
      <?php foreach ($previewSlides as $slide):
        $resolved = seo_resolve_image_path(trim($slide['image'] ?? ''));
        if ($resolved === '') {
          continue;
        }
      ?>
        <img src="<?= asset($resolved) ?>" alt="" class="admin-hero-live-strip__thumb" title="<?= htmlspecialchars($slide['alt'] ?? '') ?>" />
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-xs text-body mt-2">No slides active on the homepage right now.</p>
  <?php endif; ?>
</section>

<section class="admin-card mt-4">
  <p class="text-xs font-extrabold text-body uppercase tracking-wider mb-3">Slides</p>

  <?php if (!$slides && !$isNew): ?>
    <p class="text-sm text-body">No slides yet. <a href="<?= url('login') ?>?p=homehero&new=1" class="text-blue font-bold">Add one</a>.</p>
  <?php endif; ?>

  <div class="admin-hero-list">
    <?php foreach ($slides as $item):
      $thumb = trim($item['image'] ?? '');
      $resolved = $thumb !== '' ? seo_resolve_image_path($thumb) : '';
      $sid = $item['id'] ?? '';
      $isOpen = $editId === $sid;
      $isLive = cms_home_hero_slide_is_active($item, $now);
      $published = !empty($item['published']);
    ?>
    <details class="admin-hero-item <?= $isLive ? 'is-live' : '' ?>" name="hero-slide" <?= $isOpen ? 'open' : '' ?> id="slide-<?= htmlspecialchars($sid) ?>">
      <summary class="admin-hero-item__summary">
        <?php if ($resolved !== ''): ?>
          <img src="<?= asset($resolved) ?>" alt="" class="admin-hero-item__thumb" />
        <?php else: ?>
          <span class="admin-hero-item__thumb admin-hero-item__thumb--empty">—</span>
        <?php endif; ?>
        <span class="admin-hero-item__meta">
          <span class="admin-hero-item__title"><?= htmlspecialchars($item['alt'] ?? basename($thumb) ?: 'Untitled') ?></span>
          <span class="admin-hero-item__sub"><?= htmlspecialchars(cms_home_hero_schedule_summary($item)) ?></span>
          <span class="admin-hero-item__badges">
            <?php if ($isLive): ?><span class="admin-hero-badge admin-hero-badge--live">Live</span><?php endif; ?>
            <?php if (!$published): ?><span class="admin-hero-badge admin-hero-badge--muted">Hidden</span><?php endif; ?>
            <?php if (!empty($item['monday_only'])): ?><span class="admin-hero-badge admin-hero-badge--warn">Mon</span><?php endif; ?>
            <span class="admin-hero-badge admin-hero-badge--muted">#<?= (int) ($item['sort_order'] ?? 0) ?></span>
          </span>
        </span>
        <svg class="admin-hero-item__chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.24 4.5a.75.75 0 01-1.08 0l-4.24-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
      </summary>
      <div class="admin-hero-item__panel">
        <form method="post" action="<?= url('login') ?>" enctype="multipart/form-data">
          <?php
          $slide = $item;
          $isNew = false;
          include __DIR__ . '/homehero-partials/slide-form.php';
          ?>
        </form>
        <form method="post" action="<?= url('login') ?>" id="delete-<?= htmlspecialchars($sid) ?>" class="hidden" onsubmit="return confirm('Delete this slide?');">
          <input type="hidden" name="action" value="delete_home_hero_slide" />
          <input type="hidden" name="id" value="<?= htmlspecialchars($sid) ?>" />
        </form>
      </div>
    </details>
    <?php endforeach; ?>

    <details class="admin-hero-item" name="hero-slide" <?= $isNew ? 'open' : '' ?> id="slide-new">
      <summary class="admin-hero-item__summary">
        <span class="admin-hero-item__thumb admin-hero-item__thumb--empty">+</span>
        <span class="admin-hero-item__meta">
          <span class="admin-hero-item__title">Add new slide</span>
          <span class="admin-hero-item__sub">Upload an image and set schedule</span>
        </span>
        <svg class="admin-hero-item__chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.24 4.5a.75.75 0 01-1.08 0l-4.24-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
      </summary>
      <div class="admin-hero-item__panel">
        <form method="post" action="<?= url('login') ?>" enctype="multipart/form-data">
          <?php
          $slide = [];
          $isNew = true;
          include __DIR__ . '/homehero-partials/slide-form.php';
          ?>
        </form>
      </div>
    </details>
  </div>
</section>

<?php if ($isOpen && $editId !== ''): ?>
<script>
(function () {
  var el = document.getElementById('slide-<?= htmlspecialchars($editId, ENT_QUOTES) ?>');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
})();
</script>
<?php endif; ?>
<script src="<?= asset('assets/js/admin-home-hero.js') ?>"></script>
