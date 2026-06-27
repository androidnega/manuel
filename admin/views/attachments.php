<?php
$classGroups = cms_attachment_class_groups();
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

$exportBase = url('login') . '?p=attachments';
if ($filterGroup !== '' && isset($classGroups[$filterGroup])) {
  $exportBase .= '&group=' . urlencode($filterGroup);
}
?>
<div class="admin-intro">
  <p class="admin-intro__text">Industrial attachment registrations for end of second semester. Filter by class group and download Excel or PDF records.</p>
</div>

<div class="admin-card mb-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <p class="text-sm font-extrabold"><?= $totalCount ?> total registration<?= $totalCount === 1 ? '' : 's' ?></p>
      <p class="text-xs text-body mt-0.5">
        <?php foreach ($classGroups as $key => $label): ?>
          <?= htmlspecialchars($label) ?>: <?= $counts[$key] ?><?= $key === 'group_e' ? '' : ' · ' ?>
        <?php endforeach; ?>
      </p>
    </div>
    <?php if ($records): ?>
    <div class="flex flex-wrap gap-2">
      <a href="<?= htmlspecialchars($exportBase . '&export=csv') ?>" class="admin-btn admin-btn--ghost admin-btn--sm">
        <?= admin_icon('save') ?> Download Excel (CSV)
      </a>
      <a href="<?= htmlspecialchars($exportBase . '&export=pdf') ?>" class="admin-btn admin-btn--ghost admin-btn--sm">
        <?= admin_icon('save') ?> Download PDF
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="flex flex-wrap gap-2 mb-4">
  <a href="<?= url('login') ?>?p=attachments" class="admin-btn admin-btn--sm <?= $filterGroup === '' ? 'admin-btn--primary' : 'admin-btn--ghost' ?>">All groups (<?= $totalCount ?>)</a>
  <?php foreach ($classGroups as $key => $label): ?>
    <a href="<?= url('login') ?>?p=attachments&amp;group=<?= urlencode($key) ?>" class="admin-btn admin-btn--sm <?= $filterGroup === $key ? 'admin-btn--primary' : 'admin-btn--ghost' ?>">
      <?= htmlspecialchars($label) ?> (<?= $counts[$key] ?>)
    </a>
  <?php endforeach; ?>
</div>

<?php if (!$records): ?>
  <p class="admin-card text-sm text-body">
    No registrations yet<?= $filterLabel !== '' ? ' for ' . htmlspecialchars($filterLabel) : '' ?>.
  </p>
<?php else: ?>
  <div class="space-y-3 max-w-3xl">
    <?php foreach ($records as $row): ?>
      <?php $groupLabel = $classGroups[$row['class_group']] ?? $row['class_group']; ?>
      <article class="admin-card <?= $row['is_read'] ? '' : 'ring-2 ring-blue/20' ?>">
        <div class="flex flex-wrap items-start justify-between gap-2">
          <div class="min-w-0">
            <h3 class="font-extrabold text-sm"><?= htmlspecialchars($row['full_name']) ?></h3>
            <p class="text-xs text-body mt-0.5">
              <?= htmlspecialchars($row['index_number']) ?> · <?= htmlspecialchars($groupLabel) ?>
            </p>
          </div>
          <p class="text-[11px] text-body shrink-0"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($row['created_at']))) ?></p>
        </div>
        <dl class="mt-3 grid sm:grid-cols-2 gap-2 text-xs">
          <div><dt class="font-bold text-body">Contact</dt><dd class="text-ink"><?= htmlspecialchars($row['contact']) ?></dd></div>
          <div><dt class="font-bold text-body">Company</dt><dd class="text-ink"><?= htmlspecialchars($row['company_name']) ?></dd></div>
          <div><dt class="font-bold text-body">Location</dt><dd class="text-ink"><?= htmlspecialchars($row['location']) ?></dd></div>
          <div><dt class="font-bold text-body">Official's position</dt><dd class="text-ink"><?= htmlspecialchars($row['official_position']) ?></dd></div>
        </dl>
        <div class="mt-3 flex flex-wrap gap-2 border-t border-line pt-3">
          <a href="<?= url('login') ?>?p=attachments&amp;export=csv&amp;id=<?= (int) $row['id'] ?>" class="admin-link">Download Excel</a>
          <a href="<?= url('login') ?>?p=attachments&amp;export=pdf&amp;id=<?= (int) $row['id'] ?>" class="admin-link">Download PDF</a>
          <?php if (!$row['is_read']): ?>
            <form method="post" action="<?= url('login') ?>">
              <input type="hidden" name="action" value="attachment_read" />
              <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
              <button type="submit" class="admin-link text-mint">Mark read</button>
            </form>
          <?php endif; ?>
          <form method="post" action="<?= url('login') ?>" onsubmit="return confirm('Delete this registration?');">
            <input type="hidden" name="action" value="attachment_delete" />
            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
            <button type="submit" class="admin-link text-red-600">Delete</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
