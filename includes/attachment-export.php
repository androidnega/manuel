<?php

function cms_attachment_row_labels(): array
{
  return [
    'full_name' => 'Name',
    'index_number' => 'Index Number',
    'contact' => 'Contact',
    'company_name' => 'Company Name',
    'location' => 'Location',
    'official_position' => "Official's Position",
    'class_group' => 'Class Group',
    'level' => 'Level',
    'created_at' => 'Submitted',
  ];
}

function cms_attachment_format_row(array $row): array
{
  $groups = cms_attachment_groups(cms_db());
  $groupKey = $row['class_group'] ?? '';
  $group = $groups[$groupKey] ?? null;
  $groupLabel = $group ? ($group['label'] ?? $groupKey) : $groupKey;
  $level = $group ? trim((string) ($group['level'] ?? '')) : '';
  $created = !empty($row['created_at']) ? date('M j, Y g:i A', strtotime($row['created_at'])) : '';

  return [
    'full_name' => strtoupper($row['full_name'] ?? ''),
    'index_number' => strtoupper($row['index_number'] ?? ''),
    'contact' => strtoupper($row['contact'] ?? ''),
    'company_name' => strtoupper($row['company_name'] ?? ''),
    'location' => strtoupper($row['location'] ?? ''),
    'official_position' => strtoupper($row['official_position'] ?? ''),
    'class_group' => strtoupper($groupLabel),
    'level' => strtoupper($level),
    'created_at' => strtoupper($created),
  ];
}

function cms_attachment_export_csv(array $rows, string $filename, string $title = ''): void
{
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: no-store');

  $out = fopen('php://output', 'w');
  fprintf($out, "\xEF\xBB\xBF");

  if ($title !== '') {
    fputcsv($out, [$title]);
    fputcsv($out, ['Generated ' . date('M j, Y g:i A')]);
    fputcsv($out, []);
  }

  $labels = cms_attachment_row_labels();
  $keys = array_keys($labels);
  fputcsv($out, array_merge(['#'], array_values($labels)));

  foreach ($rows as $i => $row) {
    $formatted = cms_attachment_format_row($row);
    $line = [(string) ($i + 1)];
    foreach ($keys as $key) {
      $line[] = $formatted[$key] ?? '';
    }
    fputcsv($out, $line);
  }

  fclose($out);
  exit;
}

require_once __DIR__ . '/attachment-pdf-html.php';
