<?php
/** @var array<string, mixed> $row @var string $docTitle @var string $docSubtitle @var string $docLevel */
$formatted = cms_attachment_format_row($row);
$labels = cms_attachment_row_labels();
unset($labels['level']);
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
  <?php foreach ($labels as $key => $label): ?>
    <tr>
      <th><?= htmlspecialchars(strtoupper($label)) ?></th>
      <td class="<?= $key === 'index_number' ? 'font-mono uppercase' : 'uppercase' ?>"><?= htmlspecialchars($formatted[$key] ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
</table>
