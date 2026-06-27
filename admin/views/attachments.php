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
  <form method="post" action="<?= url('login') ?>" class="admin-card admin-attachments-settings space-y-4">
    <input type="hidden" name="action" value="save_attachment_registration" />
    <p class="admin-card__title">Registration link</p>
    <p class="text-sm text-body">
      <?php if ($registrationOpen): ?>
        <span class="inline-flex items-center gap-1.5 text-mint font-bold">● Open</span>
        <?php if ($closesLocal !== ''): ?>
          — closes <?= htmlspecialchars(date('M j, Y g:i A', strtotime($registrationConfig['closes_at']))) ?>
        <?php else: ?>
          — no closing date set
        <?php endif; ?>
      <?php else: ?>
        <span class="inline-flex items-center gap-1.5 text-red-600 font-bold">● Closed</span>
        <?php if ($closesLocal !== ''): ?>
          — closed since <?= htmlspecialchars(date('M j, Y g:i A', strtotime($registrationConfig['closes_at']))) ?>
        <?php endif; ?>
      <?php endif; ?>
    </p>

    <label class="admin-field">
      <span class="admin-field__label">Close registration at (optional)</span>
      <input type="datetime-local" name="attachment_closes_at" value="<?= htmlspecialchars($closesLocal) ?>" class="admin-input no-uppercase" />
      <span class="mt-1 block text-xs text-body">Leave empty to keep registration open. After this date, the form and homepage register button are hidden.</span>
    </label>

    <label class="admin-field">
      <span class="admin-field__label">Message when closed</span>
      <textarea name="attachment_closed_message" rows="3" class="admin-textarea"><?= htmlspecialchars($registrationConfig['closed_message']) ?></textarea>
    </label>

    <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save registration settings</button>
  </form>

<?php else: ?>
  <div class="admin-card admin-attachments-toolbar">
    <div class="admin-attachments-toolbar__stats">
      <p class="text-sm font-extrabold"><?= $totalCount ?> total registration<?= $totalCount === 1 ? '' : 's' ?></p>
      <p class="text-xs text-body mt-0.5">
        <?php foreach ($classGroups as $key => $label): ?>
          <?= htmlspecialchars($label) ?>: <?= $counts[$key] ?><?= $key === 'group_e' ? '' : ' · ' ?>
        <?php endforeach; ?>
      </p>
    </div>
    <?php if ($records): ?>
    <div class="admin-attachments-toolbar__actions">
      <a href="<?= htmlspecialchars($exportBase . '&export=csv') ?>" class="admin-btn admin-btn--ghost admin-btn--sm">
        <?= admin_icon('save') ?> Download Excel
      </a>
      <a href="<?= htmlspecialchars($exportBase . '&export=pdf') ?>" class="admin-btn admin-btn--ghost admin-btn--sm">
        <?= admin_icon('save') ?> Download PDF
      </a>
    </div>
    <?php endif; ?>
  </div>

  <div class="admin-attachments-filters">
    <a href="<?= htmlspecialchars($listBase) ?>" class="admin-btn admin-btn--sm <?= $filterGroup === '' ? 'admin-btn--primary' : 'admin-btn--ghost' ?>">All groups (<?= $totalCount ?>)</a>
    <?php foreach ($classGroups as $key => $label): ?>
      <a href="<?= htmlspecialchars($listBase . '&group=' . urlencode($key)) ?>" class="admin-btn admin-btn--sm <?= $filterGroup === $key ? 'admin-btn--primary' : 'admin-btn--ghost' ?>">
        <?= htmlspecialchars($label) ?> (<?= $counts[$key] ?>)
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (!$records): ?>
    <p class="admin-card text-sm text-body">
      No registrations yet<?= $filterLabel !== '' ? ' for ' . htmlspecialchars($filterLabel) : '' ?>.
    </p>
  <?php else: ?>
    <div class="admin-card admin-attachments-table-wrap">
      <div class="admin-table-scroll">
        <table class="admin-table admin-attachments-table">
          <colgroup>
            <col class="col-num" />
            <col class="col-name" />
            <col class="col-index" />
            <col class="col-group" />
            <col class="col-contact" />
            <col class="col-company" />
            <col class="col-location" />
            <col class="col-official" />
            <col class="col-submitted" />
            <col class="col-actions" />
          </colgroup>
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Index number</th>
              <th scope="col">Class group</th>
              <th scope="col">Contact</th>
              <th scope="col">Company</th>
              <th scope="col">Location</th>
              <th scope="col">Official's position</th>
              <th scope="col">Submitted</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($records as $i => $row): ?>
              <?php
              $groupLabel = $classGroups[$row['class_group']] ?? $row['class_group'];
              $unread = !$row['is_read'];
              $submittedTs = strtotime($row['created_at']);
              ?>
              <tr class="<?= $unread ? 'is-unread' : '' ?>">
                <td class="cell-num"><?= $i + 1 ?></td>
                <td class="cell-name"><span class="admin-attachments-name"><?= htmlspecialchars(strtoupper($row['full_name'])) ?></span></td>
                <td class="cell-index"><span class="admin-attachments-index"><?= htmlspecialchars(strtoupper($row['index_number'])) ?></span></td>
                <td class="cell-group"><span class="admin-attachments-badge"><?= htmlspecialchars(strtoupper($groupLabel)) ?></span></td>
                <td class="cell-contact"><?= htmlspecialchars(strtoupper($row['contact'])) ?></td>
                <td class="cell-company"><?= htmlspecialchars(strtoupper($row['company_name'])) ?></td>
                <td class="cell-location"><?= htmlspecialchars(strtoupper($row['location'])) ?></td>
                <td class="cell-official"><?= htmlspecialchars(strtoupper($row['official_position'])) ?></td>
                <td class="cell-submitted">
                  <span class="admin-attachments-date"><?= htmlspecialchars(date('M j, Y', $submittedTs)) ?></span>
                  <span class="admin-attachments-time"><?= htmlspecialchars(date('g:i A', $submittedTs)) ?></span>
                </td>
                <td class="cell-actions">
                  <div class="admin-attachments-actions">
                    <a href="<?= url('login') ?>?p=attachments&amp;export=csv&amp;id=<?= (int) $row['id'] ?>" class="admin-attachments-action admin-attachments-action--export">Excel</a>
                    <a href="<?= url('login') ?>?p=attachments&amp;export=pdf&amp;id=<?= (int) $row['id'] ?>" class="admin-attachments-action admin-attachments-action--export">PDF</a>
                    <?php if ($unread): ?>
                      <form method="post" action="<?= url('login') ?>">
                        <input type="hidden" name="action" value="attachment_read" />
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                        <button type="submit" class="admin-attachments-action admin-attachments-action--read">Read</button>
                      </form>
                    <?php endif; ?>
                    <form method="post" action="<?= url('login') ?>" onsubmit="return confirm('Delete this registration?');">
                      <input type="hidden" name="action" value="attachment_delete" />
                      <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                      <button type="submit" class="admin-attachments-action admin-attachments-action--delete">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="admin-attachments-cards">
      <?php foreach ($records as $row): ?>
        <?php $groupLabel = $classGroups[$row['class_group']] ?? $row['class_group']; ?>
        <article class="admin-card <?= $row['is_read'] ? '' : 'ring-2 ring-blue/20' ?>">
          <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0">
              <h3 class="font-extrabold text-sm"><?= htmlspecialchars(strtoupper($row['full_name'])) ?></h3>
              <p class="text-xs text-body mt-0.5"><?= htmlspecialchars(strtoupper($row['index_number'])) ?> · <?= htmlspecialchars(strtoupper($groupLabel)) ?></p>
            </div>
            <p class="text-[11px] text-body shrink-0"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($row['created_at']))) ?></p>
          </div>
          <dl class="mt-3 grid grid-cols-1 xs:grid-cols-2 gap-2 text-xs">
            <div><dt class="font-bold text-body">Contact</dt><dd class="text-ink break-words"><?= htmlspecialchars(strtoupper($row['contact'])) ?></dd></div>
            <div><dt class="font-bold text-body">Company</dt><dd class="text-ink break-words"><?= htmlspecialchars(strtoupper($row['company_name'])) ?></dd></div>
            <div><dt class="font-bold text-body">Location</dt><dd class="text-ink break-words"><?= htmlspecialchars(strtoupper($row['location'])) ?></dd></div>
            <div><dt class="font-bold text-body">Official</dt><dd class="text-ink break-words"><?= htmlspecialchars(strtoupper($row['official_position'])) ?></dd></div>
          </dl>
          <div class="mt-3 flex flex-wrap gap-2 border-t border-line pt-3">
            <a href="<?= url('login') ?>?p=attachments&amp;export=csv&amp;id=<?= (int) $row['id'] ?>" class="admin-link">Excel</a>
            <a href="<?= url('login') ?>?p=attachments&amp;export=pdf&amp;id=<?= (int) $row['id'] ?>" class="admin-link">PDF</a>
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
<?php endif; ?>
