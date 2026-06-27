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
?>
<div class="admin-intro">
  <p class="admin-intro__text">Industrial attachment registrations for end of second semester.</p>
</div>

<div class="admin-attachments-page">
  <div class="admin-tabs" role="tablist" aria-label="Industrial attachments">
    <a
      href="<?= htmlspecialchars($listBase) ?>"
      class="admin-tabs__btn <?= $tab === 'list' ? 'is-active' : '' ?>"
      role="tab"
      <?= $tab === 'list' ? 'aria-current="page"' : '' ?>
    >Registrations (<?= $totalCount ?>)</a>
    <a
      href="<?= htmlspecialchars($settingsBase) ?>"
      class="admin-tabs__btn <?= $tab === 'settings' ? 'is-active' : '' ?>"
      role="tab"
      <?= $tab === 'settings' ? 'aria-current="page"' : '' ?>
    >Settings</a>
  </div>

  <?php if ($tab === 'settings'): ?>
    <form method="post" action="<?= url('login') ?>" class="admin-card admin-attachments-settings">
      <input type="hidden" name="action" value="save_attachment_registration" />
      <p class="admin-card__title">Registration link</p>
      <p class="admin-attachments-settings__status">
        <?php if ($registrationOpen): ?>
          <span class="admin-status admin-status--open">● Open</span>
          <?php if ($closesLocal !== ''): ?>
            — closes <?= htmlspecialchars(date('M j, Y g:i A', strtotime($registrationConfig['closes_at']))) ?>
          <?php else: ?>
            — no closing date set
          <?php endif; ?>
        <?php else: ?>
          <span class="admin-status admin-status--closed">● Closed</span>
          <?php if ($closesLocal !== ''): ?>
            — closed since <?= htmlspecialchars(date('M j, Y g:i A', strtotime($registrationConfig['closes_at']))) ?>
          <?php endif; ?>
        <?php endif; ?>
      </p>

      <label class="admin-field">
        <span class="admin-field__label">Close registration at (optional)</span>
        <input type="datetime-local" name="attachment_closes_at" value="<?= htmlspecialchars($closesLocal) ?>" class="admin-input no-uppercase" />
        <span class="admin-field__hint">Leave empty to keep registration open. After this date, the form and homepage register button are hidden.</span>
      </label>

      <label class="admin-field">
        <span class="admin-field__label">Message when closed</span>
        <textarea name="attachment_closed_message" rows="3" class="admin-textarea"><?= htmlspecialchars($registrationConfig['closed_message']) ?></textarea>
      </label>

      <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save registration settings</button>
    </form>

  <?php else: ?>
    <?php include __DIR__ . '/attachments-list.php'; ?>
  <?php endif; ?>
</div>
