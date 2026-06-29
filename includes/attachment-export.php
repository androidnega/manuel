<?php

function cms_attachment_row_labels(): array
{
  return [
    'full_name' => 'Name',
    'index_number' => 'Index Number',
    'contact' => 'Contact',
    'company_name' => 'Company Name',
    'location' => 'Location',
    'official_position' => "Letter Recipient's Position",
    'class_group' => 'Class Group',
    'level' => 'Level',
    'created_at' => 'Submitted',
  ];
}

function cms_attachment_company_values(array $companies): array
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
    'names' => $names,
    'locations' => $locations,
    'officials' => $officials,
  ];
}

function cms_attachment_export_cell_text(array $items): string
{
  $items = array_values(array_filter(array_map(static function ($value): string {
    return strtoupper(trim((string) $value));
  }, $items), static function (string $value): bool {
    return $value !== '';
  }));

  if ($items === []) {
    return '';
  }
  if (count($items) === 1) {
    return $items[0];
  }

  return implode(', ', $items);
}

function cms_attachment_export_tags_html(array $items, string $variant = 'company'): string
{
  $variant = preg_replace('/[^a-z]/', '', strtolower($variant));
  if ($variant === '') {
    $variant = 'company';
  }

  $tags = [];
  foreach ($items as $item) {
    $item = strtoupper(trim((string) $item));
    if ($item === '') {
      continue;
    }
    $tags[] = '<span class="pdf-tag pdf-tag--' . $variant . '">' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</span>';
  }

  if ($tags === []) {
    return '<span class="pdf-tag pdf-tag--empty">—</span>';
  }

  return '<span class="pdf-tag-wrap">' . implode('', $tags) . '</span>';
}

function cms_attachment_company_export_fields(array $companies): array
{
  $values = cms_attachment_company_values($companies);

  return [
    'companies_list' => $values['names'],
    'locations_list' => $values['locations'],
    'officials_list' => $values['officials'],
    'company_name' => cms_attachment_export_cell_text($values['names']),
    'location' => cms_attachment_export_cell_text($values['locations']),
    'official_position' => cms_attachment_export_cell_text($values['officials']),
    'companies_display' => cms_attachment_export_cell_text($values['names']),
    'locations_display' => cms_attachment_export_cell_text($values['locations']),
    'officials_display' => cms_attachment_export_cell_text($values['officials']),
    'companies_tags_html' => cms_attachment_export_tags_html($values['names'], 'company'),
    'locations_tags_html' => cms_attachment_export_tags_html($values['locations'], 'location'),
    'officials_tags_html' => cms_attachment_export_tags_html($values['officials'], 'official'),
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
