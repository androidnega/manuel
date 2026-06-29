<?php
/** @var array $classGroups @var array $attachmentGroups @var array $records @var array $counts @var int $totalCount @var string $listBase @var string $exportBase @var string $filterGroup @var string $filterLabel @var callable $filterClass @var string $btnGhostSm @var string $btnSuccessSm @var string $btnDangerSm @var bool $isClassUser */
?>
<div class="flex flex-wrap items-center justify-between gap-3 text-xs text-body">
  <p><span class="font-bold text-ink"><?= $totalCount ?></span><?= $isClassUser ? ' in your class' : ' total' ?><?php if (!$isClassUser && $totalCount > 0): ?> · <?php
    $parts = [];
    foreach ($attachmentGroups as $key => $group) {
      $parts[] = htmlspecialchars(cms_attachment_group_display($group)) . ' ' . $counts[$key];
    }
    echo implode(' · ', $parts);
  ?><?php endif; ?></p>
  <?php if ($records): ?>
  <div class="flex flex-wrap gap-2">
    <a href="<?= htmlspecialchars($exportBase . '&export=csv') ?>" class="<?= $btnGhostSm ?>">Excel</a>
    <a href="<?= htmlspecialchars($exportBase . '&export=pdf') ?>" class="<?= $btnGhostSm ?>">PDF</a>
  </div>
  <?php endif; ?>
</div>

<?php if (!$isClassUser): ?>
<div class="flex flex-wrap gap-1.5">
  <a href="<?= htmlspecialchars($listBase) ?>" class="<?= $filterClass($filterGroup === '') ?>">All (<?= $totalCount ?>)</a>
  <?php foreach ($attachmentGroups as $key => $group): ?>
    <a href="<?= htmlspecialchars($listBase . '&group=' . urlencode($key)) ?>" class="<?= $filterClass($filterGroup === $key) ?>">
      <?= htmlspecialchars(cms_attachment_group_display($group)) ?> (<?= $counts[$key] ?>)
    </a>
  <?php endforeach; ?>
</div>
<?php elseif ($filterLabel !== ''): ?>
  <p class="text-xs font-bold uppercase tracking-wide text-body"><?= htmlspecialchars($filterLabel) ?></p>
<?php endif; ?>

<?php if (!$records): ?>
  <p class="rounded-lg border border-dashed border-line px-4 py-8 text-center text-sm text-body">
    No registrations yet<?= $filterLabel !== '' ? ' for ' . htmlspecialchars($filterLabel) : '' ?>.
  </p>
<?php else: ?>
  <div class="overflow-hidden rounded-lg border border-line bg-white">
    <?php foreach ($records as $i => $row): ?>
      <?php
      $group = $attachmentGroups[$row['class_group']] ?? null;
      $groupLabel = $group ? cms_attachment_group_display($group) : strtoupper($row['class_group']);
      $unread = !$row['is_read'];
      $detailId = 'attachment-detail-' . (int) $row['id'];
      $submittedTs = strtotime($row['created_at']);
      $rowCompanies = cms_attachment_companies_from_row($row);
      $companyPreview = $rowCompanies[0]['name'] ?? strtoupper($row['company_name']);
      if (count($rowCompanies) > 1) {
        $companyPreview .= ' (+' . (count($rowCompanies) - 1) . ')';
      }
      ?>
      <details
        id="<?= htmlspecialchars($detailId) ?>"
        class="group border-b border-line last:border-b-0 <?= $unread ? 'bg-orange-50/40' : '' ?>"
      >
        <summary class="flex cursor-pointer list-none items-center gap-2 px-3 py-2 text-left text-sm hover:bg-cloud [&::-webkit-details-marker]:hidden">
          <span class="w-5 shrink-0 text-[11px] font-bold text-body"><?= $i + 1 ?></span>
          <span class="min-w-0 flex-1 truncate font-bold text-ink"><?= htmlspecialchars(strtoupper($row['full_name'])) ?></span>
          <span class="hidden min-w-0 flex-1 truncate text-[11px] text-body lg:inline"><?= htmlspecialchars(strtoupper($companyPreview)) ?></span>
          <span class="hidden shrink-0 font-mono text-[11px] text-deep sm:inline"><?= htmlspecialchars(strtoupper($row['index_number'])) ?></span>
          <span class="hidden shrink-0 text-[10px] font-bold uppercase text-body md:inline"><?= htmlspecialchars($groupLabel) ?></span>
          <span class="shrink-0 text-[11px] text-body"><?= htmlspecialchars(date('M j', $submittedTs)) ?></span>
          <i class="fa-solid fa-chevron-down shrink-0 text-[9px] text-body transition-transform duration-200 group-open:rotate-180" aria-hidden="true"></i>
        </summary>

        <div class="border-t border-line bg-cloud/60 px-3 py-2.5 text-xs">
          <dl class="grid gap-1.5 sm:grid-cols-2">
            <div><dt class="font-bold uppercase tracking-wide text-body">Index</dt><dd class="font-mono text-ink"><?= htmlspecialchars(strtoupper($row['index_number'])) ?></dd></div>
            <div><dt class="font-bold uppercase tracking-wide text-body">Group</dt><dd class="text-ink"><?= htmlspecialchars($groupLabel) ?></dd></div>
            <div><dt class="font-bold uppercase tracking-wide text-body">Contact</dt><dd class="text-ink"><?= htmlspecialchars(strtoupper($row['contact'])) ?></dd></div>
            <?php foreach ($rowCompanies as $ci => $company): ?>
              <div class="sm:col-span-2 rounded-lg border border-line bg-white px-3 py-2">
                <p class="text-[10px] font-extrabold uppercase tracking-wide text-blue">Company <?= $ci + 1 ?></p>
                <dl class="mt-2 grid gap-1.5 sm:grid-cols-3">
                  <div><dt class="font-bold uppercase tracking-wide text-body">Name</dt><dd class="text-ink"><?= htmlspecialchars(strtoupper($company['name'])) ?></dd></div>
                  <div><dt class="font-bold uppercase tracking-wide text-body">Location</dt><dd class="text-ink"><?= htmlspecialchars(strtoupper($company['location'])) ?></dd></div>
                  <div><dt class="font-bold uppercase tracking-wide text-body">Letter recipient</dt><dd class="text-ink"><?= htmlspecialchars(strtoupper($company['official_position'])) ?></dd></div>
                </dl>
              </div>
            <?php endforeach; ?>
            <div class="sm:col-span-2"><dt class="font-bold uppercase tracking-wide text-body">Submitted</dt><dd class="text-ink"><?= htmlspecialchars(date('M j, Y g:i A', $submittedTs)) ?></dd></div>
          </dl>
          <div class="mt-2 flex flex-wrap gap-1.5 border-t border-line pt-2">
            <a href="<?= url('login') ?>?p=attachments&amp;export=csv&amp;id=<?= (int) $row['id'] ?>" class="<?= $btnGhostSm ?>">Excel</a>
            <a href="<?= url('login') ?>?p=attachments&amp;export=pdf&amp;id=<?= (int) $row['id'] ?>" class="<?= $btnGhostSm ?>">PDF</a>
            <?php if ($unread): ?>
              <form method="post" action="<?= url('login') ?>" class="inline">
                <input type="hidden" name="action" value="attachment_read" />
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                <button type="submit" class="<?= $btnSuccessSm ?>">Mark read</button>
              </form>
            <?php endif; ?>
            <form method="post" action="<?= url('login') ?>" class="inline" onsubmit="return confirm('Delete this registration?');">
              <input type="hidden" name="action" value="attachment_delete" />
              <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
              <button type="submit" class="<?= $btnDangerSm ?>">Delete</button>
            </form>
          </div>
        </div>
      </details>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
