<?php
/** @var array $attachmentGroups @var array $classAdminUsers @var string $btnPrimary @var string $btnGhostSm @var string $btnDangerSm @var string $inputClass @var PDO $pdo */
$allGroups = cms_attachment_groups($pdo);
?>
<div class="max-w-xl space-y-4 rounded-2xl border border-line bg-white p-5">
  <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-blue">Class rep accounts</p>
  <p class="text-sm leading-relaxed text-body">Create login accounts for class reps. Each account can view, manage, and download registrations for one class group only.</p>

  <form method="post" action="<?= url('login') ?>" class="space-y-3 rounded-xl border border-line bg-cloud/40 p-4">
    <input type="hidden" name="action" value="create_class_user" />
    <p class="text-xs font-bold uppercase tracking-wide text-body">New class rep</p>
    <div class="grid gap-3 sm:grid-cols-2">
      <label class="block">
        <span class="text-xs font-bold text-body">Username</span>
        <input type="text" name="class_username" required pattern="[a-zA-Z0-9._-]{3,32}" autocomplete="off" class="<?= $inputClass ?> normal-case" placeholder="e.g. group_a_rep" />
      </label>
      <label class="block">
        <span class="text-xs font-bold text-body">Password</span>
        <input type="password" name="class_password" required minlength="8" autocomplete="new-password" class="<?= $inputClass ?> normal-case" placeholder="Min 8 characters" />
      </label>
    </div>
    <label class="block">
      <span class="text-xs font-bold text-body">Class group</span>
      <select name="class_group" required class="<?= $inputClass ?> normal-case">
        <option value="">Select class group</option>
        <?php foreach ($allGroups as $key => $group): ?>
          <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars(cms_attachment_group_display($group)) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit" class="<?= $btnPrimary ?>"><?= admin_icon('save') ?> Create account</button>
  </form>

  <?php if ($classAdminUsers): ?>
    <div class="divide-y divide-line rounded-xl border border-line">
      <?php foreach ($classAdminUsers as $classUser): ?>
        <?php
        $groupKey = $classUser['class_group'] ?? '';
        $group = $allGroups[$groupKey] ?? null;
        $groupLabel = $group ? cms_attachment_group_display($group) : strtoupper($groupKey);
        ?>
        <div class="space-y-2 px-3 py-3">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
              <p class="text-sm font-bold text-ink"><?= htmlspecialchars($classUser['username']) ?></p>
              <p class="text-xs text-body"><?= htmlspecialchars($groupLabel) ?></p>
            </div>
            <form method="post" action="<?= url('login') ?>" class="inline" onsubmit="return confirm('Delete this class rep account?');">
              <input type="hidden" name="action" value="delete_class_user" />
              <input type="hidden" name="user_id" value="<?= (int) $classUser['id'] ?>" />
              <button type="submit" class="<?= $btnDangerSm ?>">Delete</button>
            </form>
          </div>
          <form method="post" action="<?= url('login') ?>" class="flex flex-wrap items-end gap-2">
            <input type="hidden" name="action" value="reset_class_user_password" />
            <input type="hidden" name="user_id" value="<?= (int) $classUser['id'] ?>" />
            <label class="min-w-0 flex-1">
              <span class="text-[11px] font-bold text-body">Reset password</span>
              <input type="password" name="class_password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-line bg-white px-2.5 py-1.5 text-sm normal-case outline-none focus:border-blue focus:ring-[3px] focus:ring-blue/10" placeholder="New password" />
            </label>
            <button type="submit" class="<?= $btnGhostSm ?>">Update</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="rounded-xl border border-dashed border-line px-4 py-6 text-center text-sm text-body">No class rep accounts yet.</p>
  <?php endif; ?>
</div>
