<?php
$members = cms_team_members($pdo);
$editId = (int) ($_GET['id'] ?? 0);
$edit = null;
foreach ($members as $m) {
  if ((int) $m['id'] === $editId) {
    $edit = $m;
    break;
  }
}
$currentPhoto = $edit['photo_path'] ?? '';
?>
<div class="admin-intro">
  <p class="admin-intro__text">Shown on the About page.</p>
</div>

<div class="admin-grid admin-grid--2-lg">
  <div class="space-y-3">
    <?php if (!$members): ?>
      <p class="admin-card text-sm text-body">No team members yet. Add one on the right.</p>
    <?php endif; ?>
    <?php foreach ($members as $m): ?>
      <article class="admin-card flex gap-4 items-start">
        <?php if (!empty($m['photo_path'])): ?>
          <img src="<?= asset($m['photo_path']) ?>?v=<?= (int) @filemtime(dirname(__DIR__, 2) . '/' . $m['photo_path']) ?>" alt="" class="w-14 h-14 rounded-xl object-cover shrink-0" />
        <?php else: ?>
          <div class="w-14 h-14 rounded-xl bg-cloud border border-line shrink-0 flex items-center justify-center text-xs font-bold text-body">?</div>
        <?php endif; ?>
        <div class="flex-1 min-w-0">
          <h3 class="font-extrabold text-sm"><?= htmlspecialchars($m['name']) ?></h3>
          <p class="text-xs text-body"><?= htmlspecialchars($m['role']) ?></p>
          <?php if ($m['bio']): ?><p class="text-xs text-body mt-1 line-clamp-2"><?= htmlspecialchars($m['bio']) ?></p><?php endif; ?>
          <div class="mt-2 flex gap-2">
            <a href="<?= url('login') ?>?p=team&id=<?= (int) $m['id'] ?>" class="text-xs font-bold text-blue">Edit</a>
            <form method="post" action="<?= url('login') ?>" class="inline" onsubmit="return confirm('Remove this member?');">
              <input type="hidden" name="action" value="delete_team" />
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>" />
              <button type="submit" class="text-xs font-bold text-red-600">Delete</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <form id="teamMemberForm" class="admin-card space-y-4 h-fit lg:sticky lg:top-24" method="post" action="<?= url('login') ?>" enctype="multipart/form-data">
    <p class="admin-card__title"><?= $edit ? 'Edit member' : 'Add member' ?></p>
    <input type="hidden" name="action" value="save_team" />
    <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>" />
    <input type="hidden" name="photo_path" value="<?= htmlspecialchars($currentPhoto) ?>" data-original="<?= htmlspecialchars($currentPhoto) ?>" />
    <div>
      <label class="admin-field">
        <span class="admin-field__label">Name</span>
        <input name="name" required value="<?= htmlspecialchars($edit['name'] ?? '') ?>" class="admin-input" />
      </label>
    </div>
    <div>
      <label class="text-xs font-bold text-body">Role</label>
      <input name="role" required value="<?= htmlspecialchars($edit['role'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-line px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="text-xs font-bold text-body">Bio</label>
      <textarea name="bio" rows="3" class="mt-1 w-full rounded-xl border border-line px-3 py-2 text-sm"><?= htmlspecialchars($edit['bio'] ?? '') ?></textarea>
    </div>
    <div>
      <label class="text-xs font-bold text-body">Sort order</label>
      <input name="sort_order" type="number" value="<?= (int) ($edit['sort_order'] ?? 0) ?>" class="mt-1 w-full rounded-xl border border-line px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="text-xs font-bold text-body">Photo</label>
      <p class="mt-1 text-[11px] text-body">Choose an image, then crop to a square before saving.</p>
      <?php if ($currentPhoto): ?>
        <div id="teamCurrentPhotoWrap">
          <p class="mt-2 text-[11px] text-body">Current photo (upload a new one to replace):</p>
          <img src="<?= asset($currentPhoto) ?>?v=<?= (int) @filemtime(__DIR__ . '/../../' . $currentPhoto) ?>" alt="" class="mt-1 w-20 h-20 rounded-xl object-cover border border-line" id="teamCurrentPhoto" />
        </div>
      <?php endif; ?>
      <div id="teamPhotoPreview" class="mt-3 hidden">
        <p class="text-[11px] font-bold text-body mb-1">New photo preview</p>
        <img id="teamPhotoPreviewImg" src="" alt="Crop preview" class="w-24 h-24 rounded-xl object-cover border border-line" />
      </div>
      <input type="file" id="teamPhotoInput" name="photo" accept="image/jpeg,image/png,image/webp" class="mt-2 w-full text-sm" />
      <button type="button" id="teamPhotoClear" class="mt-2 hidden text-xs font-bold text-body hover:text-ink">Remove selected photo</button>
    </div>
    <button type="submit" class="admin-btn admin-btn--primary w-full sm:w-auto">
      <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save
    </button>
    <?php if ($edit): ?>
      <a href="<?= url('login') ?>?p=team" class="block text-center text-xs font-bold text-body">Cancel edit</a>
    <?php endif; ?>
  </form>
</div>

<div id="teamCropModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-ink/60" aria-hidden="true" role="dialog" aria-labelledby="teamCropTitle">
  <div class="w-full max-w-lg rounded-2xl bg-white border border-line shadow-sleek overflow-hidden">
    <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3">
      <h2 id="teamCropTitle" class="text-sm font-extrabold text-ink">Crop photo</h2>
      <button type="button" id="teamCropCancel" class="text-xs font-bold text-body hover:text-ink" aria-label="Cancel">Cancel</button>
    </div>
    <div class="p-4 bg-cloud">
      <div class="w-full h-[min(60vh,400px)] bg-deep/5 rounded-xl overflow-hidden">
        <img id="teamCropImage" src="" alt="Crop preview" class="block max-w-full" />
      </div>
    </div>
    <div class="flex flex-wrap gap-2 justify-end border-t border-line px-4 py-3">
      <button type="button" id="teamCropCancelFooter" class="rounded-lg border border-line px-4 py-2 text-xs font-bold text-body hover:bg-cloud">Cancel</button>
      <button type="button" id="teamCropApply" class="rounded-lg bg-deep px-4 py-2 text-xs font-bold text-white hover:bg-ink">
        <i class="fa-solid fa-crop-simple mr-1" aria-hidden="true"></i> Use cropped photo
      </button>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= asset('assets/js/team-photo-crop.js') ?>"></script>
