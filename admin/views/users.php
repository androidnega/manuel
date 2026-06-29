<?php
/** @var PDO $pdo */
$allGroups = cms_attachment_groups($pdo);
$classAdminUsers = cms_class_admin_users($pdo);
$minPassword = cms_class_admin_min_password_length();
?>
<div class="admin-intro">
  <p class="admin-intro__text">Create class rep logins. Each account can view, manage, and download industrial attachment registrations for one class group only.</p>
</div>

<form method="post" action="<?= url('login') ?>" class="admin-card max-w-xl space-y-4" data-no-uppercase="1">
  <input type="hidden" name="action" value="create_class_user" />
  <p class="admin-card__title">New class rep</p>
  <div class="grid gap-3 sm:grid-cols-2">
    <label class="admin-field">
      <span class="admin-field__label">Username</span>
      <input type="text" name="class_username" required pattern="[a-zA-Z0-9._-]{3,32}" autocomplete="off" class="admin-input no-uppercase" placeholder="e.g. NEIZER or neizer" />
      <span class="mt-1 block text-xs text-body">Upper or lower case is kept as you type it.</span>
    </label>
    <label class="admin-field">
      <span class="admin-field__label">Password</span>
      <input type="password" name="class_password" required minlength="<?= $minPassword ?>" autocomplete="new-password" class="admin-input" placeholder="Min <?= $minPassword ?> characters" />
    </label>
  </div>
  <label class="admin-field">
    <span class="admin-field__label">Class group</span>
    <select name="class_group" required class="admin-input">
      <option value="">Select class group</option>
      <?php foreach ($allGroups as $key => $group): ?>
        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars(cms_attachment_group_display($group)) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Create account</button>
</form>

<div class="admin-card max-w-xl mt-6">
  <p class="admin-card__title">Class rep accounts (<?= count($classAdminUsers) ?>)</p>
  <?php if ($classAdminUsers): ?>
    <div class="mt-4 divide-y divide-line rounded-xl border border-line">
      <?php foreach ($classAdminUsers as $classUser): ?>
        <?php
        $groupKey = $classUser['class_group'] ?? '';
        $group = $allGroups[$groupKey] ?? null;
        $groupLabel = $group ? cms_attachment_group_display($group) : strtoupper($groupKey);
        ?>
        <div class="space-y-3 px-4 py-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
              <p class="text-sm font-bold text-ink"><?= htmlspecialchars($classUser['username']) ?></p>
              <p class="text-xs text-body"><?= htmlspecialchars($groupLabel) ?></p>
            </div>
            <form method="post" action="<?= url('login') ?>" class="inline" onsubmit="return confirm('Delete this class rep account?');">
              <input type="hidden" name="action" value="delete_class_user" />
              <input type="hidden" name="user_id" value="<?= (int) $classUser['id'] ?>" />
              <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm text-red-600">Delete</button>
            </form>
          </div>
          <form method="post" action="<?= url('login') ?>" class="flex flex-wrap items-end gap-2" data-no-uppercase="1">
            <input type="hidden" name="action" value="reset_class_user_password" />
            <input type="hidden" name="user_id" value="<?= (int) $classUser['id'] ?>" />
            <label class="min-w-0 flex-1 admin-field mb-0">
              <span class="admin-field__label">Reset password</span>
              <input type="password" name="class_password" required minlength="<?= $minPassword ?>" autocomplete="new-password" class="admin-input" placeholder="Min <?= $minPassword ?> characters" />
            </label>
            <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm">Update</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="mt-3 rounded-xl border border-dashed border-line px-4 py-8 text-center text-sm text-body">No class rep accounts yet.</p>
  <?php endif; ?>
</div>
