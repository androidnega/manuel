<?php
/** @var array $classGroups @var array $records @var array $counts @var int $totalCount @var string $listBase @var string $exportBase @var string $filterGroup @var string $filterLabel @var callable $filterClass @var string $btnGhostSm @var string $btnSuccessSm @var string $btnDangerSm */
?>
<div class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-line bg-white p-5">
  <div>
    <p class="text-sm font-extrabold text-ink"><?= $totalCount ?> total registration<?= $totalCount === 1 ? '' : 's' ?></p>
    <p class="mt-0.5 text-xs text-body">
      <?php foreach ($classGroups as $key => $label): ?>
        <?= htmlspecialchars($label) ?>: <?= $counts[$key] ?><?= $key === 'group_e' ? '' : ' · ' ?>
      <?php endforeach; ?>
    </p>
  </div>
  <?php if ($records): ?>
  <div class="flex flex-wrap gap-2 max-sm:w-full">
    <a href="<?= htmlspecialchars($exportBase . '&export=csv') ?>" class="<?= $btnGhostSm ?> max-sm:flex-1 max-sm:justify-center">
      <?= admin_icon('save') ?> Download Excel
    </a>
    <a href="<?= htmlspecialchars($exportBase . '&export=pdf') ?>" class="<?= $btnGhostSm ?> max-sm:flex-1 max-sm:justify-center">
      <?= admin_icon('save') ?> Download PDF
    </a>
  </div>
  <?php endif; ?>
</div>

<div class="flex flex-wrap gap-2">
  <a href="<?= htmlspecialchars($listBase) ?>" class="<?= $filterClass($filterGroup === '') ?>">All groups (<?= $totalCount ?>)</a>
  <?php foreach ($classGroups as $key => $label): ?>
    <a href="<?= htmlspecialchars($listBase . '&group=' . urlencode($key)) ?>" class="<?= $filterClass($filterGroup === $key) ?>">
      <?= htmlspecialchars($label) ?> (<?= $counts[$key] ?>)
    </a>
  <?php endforeach; ?>
</div>

<?php if (!$records): ?>
  <div class="rounded-2xl border border-dashed border-line bg-cloud px-6 py-12 text-center">
    <p class="text-sm font-extrabold text-ink">
      No registrations yet<?= $filterLabel !== '' ? ' for ' . htmlspecialchars($filterLabel) : '' ?>.
    </p>
    <p class="mt-1 text-xs text-body">New student submissions will appear here once they register.</p>
  </div>
<?php else: ?>
  <div class="space-y-2.5">
    <?php foreach ($records as $i => $row): ?>
      <?php
      $groupLabel = $classGroups[$row['class_group']] ?? $row['class_group'];
      $unread = !$row['is_read'];
      $detailId = 'attachment-detail-' . (int) $row['id'];
      $submittedTs = strtotime($row['created_at']);
      $itemClass = 'group overflow-hidden rounded-2xl border bg-white transition [&.is-open]:border-slate-300 [&.is-open]:shadow-[0_8px_24px_-18px_rgba(11,30,58,0.35)]';
      if ($unread) {
        $itemClass .= ' border-blue/35 shadow-[inset_3px_0_0_0_#FF7A00]';
      } else {
        $itemClass .= ' border-line';
      }
      ?>
      <article class="<?= $itemClass ?>" data-attachment-item>
        <button
          type="button"
          class="grid w-full grid-cols-[auto_1fr_auto_auto] items-center gap-x-3 gap-y-1 px-4 py-3.5 text-left text-ink transition hover:bg-cloud max-md:grid-cols-[auto_1fr_auto]"
          aria-expanded="false"
          aria-controls="<?= htmlspecialchars($detailId) ?>"
          data-attachment-toggle
        >
          <span class="flex h-7 min-w-[1.75rem] items-center justify-center rounded-full bg-gray-100 text-xs font-extrabold text-body">
            <?= $i + 1 ?>
          </span>

          <span class="min-w-0">
            <span class="block break-words text-sm font-extrabold text-ink"><?= htmlspecialchars(strtoupper($row['full_name'])) ?></span>
            <span class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
              <span class="font-mono text-[11px] font-bold text-deep"><?= htmlspecialchars(strtoupper($row['index_number'])) ?></span>
              <span class="inline-block rounded-full bg-blue/10 px-2 py-0.5 text-[10px] font-extrabold leading-snug text-deep">
                <?= htmlspecialchars(strtoupper($groupLabel)) ?>
              </span>
            </span>
          </span>

          <span class="whitespace-nowrap text-right max-md:col-start-2 max-md:row-start-2 max-md:text-left max-md:whitespace-normal">
            <span class="block text-xs font-bold text-ink"><?= htmlspecialchars(date('M j, Y', $submittedTs)) ?></span>
            <span class="mt-0.5 block text-[11px] text-body"><?= htmlspecialchars(date('g:i A', $submittedTs)) ?></span>
          </span>

          <span class="flex items-center justify-center max-md:col-start-3 max-md:row-span-2 max-md:row-start-1 max-md:self-center" aria-hidden="true">
            <i class="fa-solid fa-chevron-down text-[10px] text-body transition-transform group-[.is-open]:rotate-180"></i>
          </span>
        </button>

        <div class="hidden border-t border-line bg-[#fcfcfd] px-4 pb-4" id="<?= htmlspecialchars($detailId) ?>" data-attachment-panel>
          <dl class="mt-3.5 grid gap-3 sm:grid-cols-2">
            <div>
              <dt class="text-[11px] font-extrabold uppercase tracking-wide text-body">Contact</dt>
              <dd class="mt-0.5 break-words text-[13px] font-semibold text-ink"><?= htmlspecialchars(strtoupper($row['contact'])) ?></dd>
            </div>
            <div>
              <dt class="text-[11px] font-extrabold uppercase tracking-wide text-body">Company</dt>
              <dd class="mt-0.5 break-words text-[13px] font-semibold text-ink"><?= htmlspecialchars(strtoupper($row['company_name'])) ?></dd>
            </div>
            <div>
              <dt class="text-[11px] font-extrabold uppercase tracking-wide text-body">Location</dt>
              <dd class="mt-0.5 break-words text-[13px] font-semibold text-ink"><?= htmlspecialchars(strtoupper($row['location'])) ?></dd>
            </div>
            <div>
              <dt class="text-[11px] font-extrabold uppercase tracking-wide text-body">Official's position</dt>
              <dd class="mt-0.5 break-words text-[13px] font-semibold text-ink"><?= htmlspecialchars(strtoupper($row['official_position'])) ?></dd>
            </div>
          </dl>

          <div class="mt-4 flex flex-wrap gap-2 border-t border-line pt-3.5">
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
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
