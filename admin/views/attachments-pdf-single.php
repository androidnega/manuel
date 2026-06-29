<?php
/** @var array<string, mixed> $row @var string $docTitle @var string $docSubtitle @var string $docLevel */
$formatted = cms_attachment_format_row($row);
$companies = $formatted['companies'] ?? cms_attachment_companies_from_row($row);
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
    <th>Submitted</th>
    <td class="uppercase"><?= htmlspecialchars($formatted['created_at']) ?></td>
  </tr>
</table>

<?php foreach ($companies as $i => $company): ?>
  <table class="detail-grid" style="margin-top: 12px;">
    <tr>
      <th colspan="2" style="color: #ff7a00; padding-top: 14px;">Company <?= $i + 1 ?></th>
    </tr>
    <tr>
      <th>Name</th>
      <td class="uppercase"><?= htmlspecialchars(strtoupper($company['name'])) ?></td>
    </tr>
    <tr>
      <th>Location</th>
      <td class="uppercase"><?= htmlspecialchars(strtoupper($company['location'])) ?></td>
    </tr>
    <tr>
      <th>Official</th>
      <td class="uppercase"><?= htmlspecialchars(strtoupper($company['official_position'])) ?></td>
    </tr>
  </table>
<?php endforeach; ?>
