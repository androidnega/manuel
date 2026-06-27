<?php
$classGroups = cms_attachment_class_groups();
$registrationConfig = cms_attachment_registration_config($pdo);
$registrationOpen = cms_attachment_registration_is_open($pdo);
$closesLocal = '';
if (!empty($registrationConfig['closes_at'])) {
  $ts = strtotime($registrationConfig['closes_at']);
  if ($ts !== false) {
    $closesLocal = date('Y-m-d\TH:i', $ts);
  }
}

$tab = preg_replace('/[^a-z]/', '', (string) ($_GET['tab'] ?? 'list'));
if ($tab !== 'settings') {
  $tab = 'list';
}

$filterGroup = preg_replace('/[^a-z_]/', '', (string) ($_GET['group'] ?? ''));
$filterLabel = $filterGroup !== '' && isset($classGroups[$filterGroup]) ? $classGroups[$filterGroup] : '';

$sql = 'SELECT * FROM industrial_attachments';
$params = [];
if ($filterGroup !== '' && isset($classGroups[$filterGroup])) {
  $sql .= ' WHERE class_group = ?';
  $params[] = $filterGroup;
}
$sql .= ' ORDER BY class_group ASC, full_name ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

$counts = [];
foreach ($classGroups as $key => $label) {
  $cStmt = $pdo->prepare('SELECT COUNT(*) FROM industrial_attachments WHERE class_group = ?');
  $cStmt->execute([$key]);
  $counts[$key] = (int) $cStmt->fetchColumn();
}
$totalCount = array_sum($counts);

$listBase = url('login') . '?p=attachments&tab=list';
$settingsBase = url('login') . '?p=attachments&tab=settings';
$exportBase = $listBase;
if ($filterGroup !== '' && isset($classGroups[$filterGroup])) {
  $exportBase .= '&group=' . urlencode($filterGroup);
}

$tabClass = static function (bool $active): string {
  if ($active) {
    return 'inline-flex items-center justify-center rounded-full border border-deep bg-deep px-4 py-2 text-[13px] font-bold text-white no-underline transition-colors';
  }
  return 'inline-flex items-center justify-center rounded-full border border-line bg-white px-4 py-2 text-[13px] font-bold text-body no-underline transition-colors hover:border-gray-300 hover:text-ink';
};

$filterClass = static function (bool $active): string {
  if ($active) {
    return 'inline-flex items-center rounded-xl bg-deep px-3 py-1.5 text-xs font-bold text-white no-underline transition-colors';
  }
  return 'inline-flex items-center rounded-xl border border-line bg-white px-3 py-1.5 text-xs font-bold text-body no-underline transition-colors hover:bg-cloud hover:text-ink';
};

$btnPrimary = 'inline-flex items-center justify-center gap-1.5 rounded-xl bg-deep px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-ink';
$btnGhostSm = 'inline-flex items-center justify-center gap-1.5 rounded-xl border border-line bg-white px-3 py-1.5 text-xs font-bold text-body no-underline transition-colors hover:bg-cloud hover:text-ink';
$btnSuccessSm = 'inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-white px-3 py-1.5 text-xs font-bold text-emerald-700 transition-colors hover:bg-emerald-50';
$btnDangerSm = 'inline-flex items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-700 transition-colors hover:bg-red-50';
$inputClass = 'mt-1.5 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-blue focus:ring-[3px] focus:ring-blue/10';
?>
<div class="mb-6">
  <p class="max-w-2xl text-sm leading-relaxed text-body">Industrial attachment registrations for end of second semester.</p>
</div>

<div class="space-y-4">
  <div class="flex flex-wrap gap-2" role="tablist" aria-label="Industrial attachments">
    <a
      href="<?= htmlspecialchars($listBase) ?>"
      class="<?= $tabClass($tab === 'list') ?> max-sm:flex-1 max-sm:min-w-0 max-sm:justify-center"
      role="tab"
      <?= $tab === 'list' ? 'aria-current="page"' : '' ?>
    >Registrations (<?= $totalCount ?>)</a>
    <a
      href="<?= htmlspecialchars($settingsBase) ?>"
      class="<?= $tabClass($tab === 'settings') ?> max-sm:flex-1 max-sm:min-w-0 max-sm:justify-center"
      role="tab"
      <?= $tab === 'settings' ? 'aria-current="page"' : '' ?>
    >Settings</a>
  </div>

  <?php if ($tab === 'settings'): ?>
    <form method="post" action="<?= url('login') ?>" class="max-w-xl space-y-4 rounded-2xl border border-line bg-white p-5">
      <input type="hidden" name="action" value="save_attachment_registration" />
      <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-blue">Registration link</p>

      <p class="text-sm leading-relaxed text-body">
        <?php if ($registrationOpen): ?>
          <span class="inline-flex items-center gap-1.5 font-bold text-emerald-700">● Open</span>
          <?php if ($closesLocal !== ''): ?>
            — closes <?= htmlspecialchars(date('M j, Y g:i A', strtotime($registrationConfig['closes_at']))) ?>
          <?php else: ?>
            — no closing date set
          <?php endif; ?>
        <?php else: ?>
          <span class="inline-flex items-center gap-1.5 font-bold text-red-600">● Closed</span>
          <?php if ($closesLocal !== ''): ?>
            — closed since <?= htmlspecialchars(date('M j, Y g:i A', strtotime($registrationConfig['closes_at']))) ?>
          <?php endif; ?>
        <?php endif; ?>
      </p>

      <label class="block">
        <span class="text-xs font-bold text-body">Close registration at (optional)</span>
        <input type="datetime-local" name="attachment_closes_at" value="<?= htmlspecialchars($closesLocal) ?>" class="<?= $inputClass ?> normal-case" />
        <span class="mt-1 block text-xs leading-relaxed text-body">Leave empty to keep registration open. After this date, the form and homepage register button are hidden.</span>
      </label>

      <label class="block">
        <span class="text-xs font-bold text-body">Message when closed</span>
        <textarea name="attachment_closed_message" rows="3" class="<?= $inputClass ?> normal-case"><?= htmlspecialchars($registrationConfig['closed_message']) ?></textarea>
      </label>

      <button type="submit" class="<?= $btnPrimary ?>"><?= admin_icon('save') ?> Save registration settings</button>
    </form>

  <?php else: ?>
    <?php include __DIR__ . '/attachments-list.php'; ?>
  <?php endif; ?>
</div>
