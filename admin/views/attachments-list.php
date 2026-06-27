<?php
/** @var bool $registrationOpen @var string $closesLocal @var array $registrationConfig @var array $classGroups @var array $records @var array $counts @var int $totalCount @var string $listBase @var string $exportBase @var string $filterGroup @var string $filterLabel */
?>
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
  <div class="admin-attachments-list">
    <?php foreach ($records as $i => $row): ?>
      <?php
      $groupLabel = $classGroups[$row['class_group']] ?? $row['class_group'];
      $unread = !$row['is_read'];
      $detailId = 'attachment-detail-' . (int) $row['id'];
      $submittedTs = strtotime($row['created_at']);
      ?>
      <article class="admin-attachments-item <?= $unread ? 'is-unread' : '' ?>" data-attachment-item>
        <button
          type="button"
          class="admin-attachments-item__summary"
          aria-expanded="false"
          aria-controls="<?= htmlspecialchars($detailId) ?>"
          data-attachment-toggle
        >
          <span class="admin-attachments-item__num"><?= $i + 1 ?></span>
          <span class="admin-attachments-item__main">
            <span class="admin-attachments-item__name"><?= htmlspecialchars(strtoupper($row['full_name'])) ?></span>
            <span class="admin-attachments-item__meta">
              <span class="admin-attachments-index"><?= htmlspecialchars(strtoupper($row['index_number'])) ?></span>
              <span class="admin-attachments-badge"><?= htmlspecialchars(strtoupper($groupLabel)) ?></span>
            </span>
          </span>
          <span class="admin-attachments-item__date">
            <span class="admin-attachments-date"><?= htmlspecialchars(date('M j, Y', $submittedTs)) ?></span>
            <span class="admin-attachments-time"><?= htmlspecialchars(date('g:i A', $submittedTs)) ?></span>
          </span>
          <span class="admin-attachments-item__chev" aria-hidden="true"></span>
        </button>

        <div class="admin-attachments-item__detail" id="<?= htmlspecialchars($detailId) ?>" hidden>
          <dl class="admin-attachments-detail-grid">
            <div><dt>Contact</dt><dd><?= htmlspecialchars(strtoupper($row['contact'])) ?></dd></div>
            <div><dt>Company</dt><dd><?= htmlspecialchars(strtoupper($row['company_name'])) ?></dd></div>
            <div><dt>Location</dt><dd><?= htmlspecialchars(strtoupper($row['location'])) ?></dd></div>
            <div><dt>Official's position</dt><dd><?= htmlspecialchars(strtoupper($row['official_position'])) ?></dd></div>
          </dl>
          <div class="admin-attachments-item__actions">
            <a href="<?= url('login') ?>?p=attachments&amp;export=csv&amp;id=<?= (int) $row['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Excel</a>
            <a href="<?= url('login') ?>?p=attachments&amp;export=pdf&amp;id=<?= (int) $row['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">PDF</a>
            <?php if ($unread): ?>
              <form method="post" action="<?= url('login') ?>">
                <input type="hidden" name="action" value="attachment_read" />
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm text-mint">Mark read</button>
              </form>
            <?php endif; ?>
            <form method="post" action="<?= url('login') ?>" onsubmit="return confirm('Delete this registration?');">
              <input type="hidden" name="action" value="attachment_delete" />
              <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
              <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm text-red-600">Delete</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
