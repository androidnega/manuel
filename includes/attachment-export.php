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

function cms_attachment_company_export_fields(array $companies): array
{
  $names = [];
  $locations = [];
  $officials = [];

  foreach ($companies as $company) {
    $names[] = strtoupper(trim((string) ($company['name'] ?? '')));
    $locations[] = strtoupper(trim((string) ($company['location'] ?? '')));
    $officials[] = strtoupper(trim((string) ($company['official_position'] ?? '')));
  }

  return [
    'company_name' => implode(', ', $names),
    'location' => implode(', ', $locations),
    'official_position' => implode(', ', $officials),
    'companies_display' => implode(', ', $names),
    'locations_display' => implode(', ', $locations),
    'officials_display' => implode(', ', $officials),
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
  $companies = cms_attachment_companies_from_row($row);

  $formatted = [
    'full_name' => strtoupper($row['full_name'] ?? ''),
    'index_number' => strtoupper($row['index_number'] ?? ''),
    'contact' => strtoupper($row['contact'] ?? ''),
    'company_name' => '',
    'location' => '',
    'official_position' => '',
    'class_group' => strtoupper($groupLabel),
    'level' => strtoupper($level),
    'created_at' => strtoupper($created),
  ];

  if ($companies !== []) {
    $formatted = array_merge($formatted, cms_attachment_company_export_fields($companies));
  }

  for ($i = 1; $i <= cms_attachment_max_companies(); $i++) {
    $company = $companies[$i - 1] ?? null;
    $formatted['company_' . $i] = strtoupper($company['name'] ?? '');
    $formatted['location_' . $i] = strtoupper($company['location'] ?? '');
    $formatted['official_' . $i] = strtoupper($company['official_position'] ?? '');
  }

  $formatted['companies'] = $companies;

  return $formatted;
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

  $labels = array_merge(['num' => '#'], cms_attachment_row_labels());
  fputcsv($out, array_values($labels));

  $keys = array_keys($labels);

  foreach ($rows as $i => $row) {
    $formatted = cms_attachment_format_row($row);
    $line = [];
    foreach ($keys as $key) {
      if ($key === 'num') {
        $line[] = (string) ($i + 1);
      } else {
        $line[] = $formatted[$key] ?? '';
      }
    }
    fputcsv($out, $line);
  }

  fclose($out);
  exit;
}

require_once __DIR__ . '/attachment-pdf-html.php';
