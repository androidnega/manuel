<?php
/** @var array<int, array<string, mixed>> $rows @var string $docTitle @var string $docSubtitle @var string $docLevel */
$formattedRows = [];
foreach ($rows as $i => $row) {
  $formatted = cms_attachment_format_row($row);
  $formatted['_num'] = (string) ($i + 1);
  $formattedRows[] = $formatted;
}
$totalRecords = count($formattedRows);
$generatedAt = date('M j, Y g:i A');
?>
<div class="doc-header">
  <h1 class="doc-title"><?= htmlspecialchars(strtoupper($docTitle)) ?></h1>
  <?php if ($docSubtitle !== '' || $docLevel !== ''): ?>
    <p class="doc-subtitle doc-subtitle--group"><?= htmlspecialchars(cms_attachment_pdf_group_header_line($docSubtitle, $docLevel)) ?></p>
  <?php endif; ?>
  <p class="doc-meta">Generated <?= htmlspecialchars($generatedAt) ?> · <?= $totalRecords ?> record<?= $totalRecords === 1 ? '' : 's' ?></p>
</div>

<?php if ($formattedRows === []): ?>
  <div class="empty-state">
    <p class="font-bold text-ink">No registrations in this list yet.</p>
  </div>
<?php else: ?>
  <table class="pdf-table">
    <thead>
      <tr>
        <th class="col-num">#</th>
        <th class="col-name">Name</th>
        <th class="col-index">Index no.</th>
        <th class="col-contact">Contact</th>
        <th class="col-company">Company</th>
        <th class="col-location">Location</th>
        <th class="col-official">Letter recipient</th>
        <th class="col-group">Group</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($formattedRows as $formatted): ?>
        <tr>
          <td class="col-num font-bold"><?= htmlspecialchars($formatted['_num']) ?></td>
          <td class="col-name font-bold uppercase"><?= htmlspecialchars($formatted['full_name']) ?></td>
          <td class="col-index font-mono uppercase"><?= htmlspecialchars($formatted['index_number']) ?></td>
          <td class="col-contact uppercase"><?= htmlspecialchars($formatted['contact']) ?></td>
          <td class="col-company"><?= $formatted['companies_tags_html'] ?? cms_attachment_export_tags_html($formatted['companies_list'] ?? [], 'company') ?></td>
          <td class="col-location"><?= $formatted['locations_tags_html'] ?? cms_attachment_export_tags_html($formatted['locations_list'] ?? [], 'location') ?></td>
          <td class="col-official"><?= $formatted['officials_tags_html'] ?? cms_attachment_export_tags_html($formatted['officials_list'] ?? [], 'official') ?></td>
          <td class="col-group uppercase"><?= htmlspecialchars($formatted['class_group']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
