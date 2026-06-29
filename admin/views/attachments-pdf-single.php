<?php
/** @var array<string, mixed> $row @var string $docTitle @var string $docSubtitle @var string $docLevel */
$formatted = cms_attachment_format_row($row);
$generatedAt = date('M j, Y g:i A');
$groupLine = trim($formatted['class_group']);
?>
<div class="doc-header">
  <h1 class="doc-title"><?= htmlspecialchars(strtoupper($docTitle)) ?></h1>
  <?php if ($docSubtitle !== '' || $docLevel !== ''): ?>
    <p class="doc-subtitle doc-subtitle--group"><?= htmlspecialchars(cms_attachment_pdf_group_header_line($docSubtitle, $docLevel)) ?></p>
  <?php endif; ?>
  <p class="doc-meta">Generated <?= htmlspecialchars($generatedAt) ?></p>
</div>

<?php if ($groupLine !== ''): ?>
  <p style="margin: 0 0 12px;"><span class="badge uppercase"><?= htmlspecialchars(cms_attachment_pdf_group_header_line($groupLine, $formatted['level'] ?? '')) ?></span></p>
<?php endif; ?>

<table class="detail-grid">
  <tr>
    <th>Name</th>
    <td class="uppercase"><?= htmlspecialchars($formatted['full_name']) ?></td>
  </tr>
  <tr>
    <th>Index number</th>
    <td class="font-mono uppercase"><?= htmlspecialchars($formatted['index_number']) ?></td>
  </tr>
  <tr>
    <th>Contact</th>
    <td class="uppercase"><?= htmlspecialchars($formatted['contact']) ?></td>
  </tr>
  <tr>
    <th>Class group</th>
    <td class="uppercase"><?= htmlspecialchars($formatted['class_group']) ?></td>
  </tr>
  <tr>
    <th>Companies</th>
    <td><?= $formatted['companies_tags_html'] ?? cms_attachment_export_tags_html($formatted['companies_list'] ?? [], 'company') ?></td>
  </tr>
  <tr>
    <th>Locations</th>
    <td><?= $formatted['locations_tags_html'] ?? cms_attachment_export_tags_html($formatted['locations_list'] ?? [], 'location') ?></td>
  </tr>
  <tr>
    <th>Letter recipients</th>
    <td><?= $formatted['officials_tags_html'] ?? cms_attachment_export_tags_html($formatted['officials_list'] ?? [], 'official') ?></td>
  </tr>
  <tr>
    <th>Submitted</th>
    <td class="uppercase"><?= htmlspecialchars($formatted['created_at']) ?></td>
  </tr>
</table>
