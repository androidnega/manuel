<?php
/** @var array<string, mixed> $row @var string $docTitle @var string $docSubtitle @var string $docLevel */
$formatted = cms_attachment_format_row($row);
$generatedAt = date('M j, Y g:i A');
$groupLine = trim($formatted['class_group']);
?>
<div class="doc-header">
  <h1 class="doc-title"><?= htmlspecialchars(strtoupper($docTitle)) ?></h1>
  <?php if ($docSubtitle !== ''): ?>
    <p class="doc-subtitle"><?= htmlspecialchars(strtoupper($docSubtitle)) ?></p>
  <?php endif; ?>
  <?php if ($docLevel !== ''): ?>
    <p class="doc-level"><?= htmlspecialchars(strtoupper($docLevel)) ?></p>
  <?php endif; ?>
  <p class="doc-meta">Generated <?= htmlspecialchars($generatedAt) ?></p>
</div>

<?php if ($groupLine !== ''): ?>
  <p style="margin: 0 0 12px;"><span class="badge uppercase"><?= htmlspecialchars($groupLine) ?></span></p>
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
    <td class="uppercase"><?= htmlspecialchars($formatted['companies_display']) ?></td>
  </tr>
  <tr>
    <th>Locations</th>
    <td class="uppercase"><?= htmlspecialchars($formatted['locations_display']) ?></td>
  </tr>
  <tr>
    <th>Officials</th>
    <td class="uppercase"><?= htmlspecialchars($formatted['officials_display']) ?></td>
  </tr>
  <tr>
    <th>Submitted</th>
    <td class="uppercase"><?= htmlspecialchars($formatted['created_at']) ?></td>
  </tr>
</table>
