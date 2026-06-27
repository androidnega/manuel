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
    'created_at' => 'Submitted',
  ];
}

function cms_attachment_format_row(array $row): array
{
  $groups = cms_attachment_class_groups();
  $groupKey = $row['class_group'] ?? '';
  $groupLabel = $groups[$groupKey] ?? $groupKey;
  $created = !empty($row['created_at']) ? date('M j, Y g:i A', strtotime($row['created_at'])) : '';

  return [
    'full_name' => $row['full_name'] ?? '',
    'index_number' => $row['index_number'] ?? '',
    'contact' => $row['contact'] ?? '',
    'company_name' => $row['company_name'] ?? '',
    'location' => $row['location'] ?? '',
    'official_position' => $row['official_position'] ?? '',
    'class_group' => $groupLabel,
    'created_at' => $created,
  ];
}

function cms_attachment_export_csv(array $rows, string $filename): void
{
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: no-store');

  $out = fopen('php://output', 'w');
  fprintf($out, "\xEF\xBB\xBF");

  $labels = cms_attachment_row_labels();
  fputcsv($out, array_values($labels));

  foreach ($rows as $row) {
    fputcsv($out, array_values(cms_attachment_format_row($row)));
  }

  fclose($out);
  exit;
}

function cms_attachment_pdf_escape(string $text): string
{
  return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function cms_attachment_export_pdf(array $rows, string $filename, string $title): void
{
  $labels = cms_attachment_row_labels();
  $lines = [$title, str_repeat('-', 72)];

  if (!$rows) {
    $lines[] = 'No records found.';
  } else {
    foreach ($rows as $i => $row) {
      $formatted = cms_attachment_format_row($row);
      if ($i > 0) {
        $lines[] = str_repeat('-', 72);
      }
      foreach ($labels as $key => $label) {
        $lines[] = $label . ': ' . ($formatted[$key] ?? '');
      }
    }
  }

  $stream = "BT\n/F1 10 Tf\n14 TL\n";
  $y = 770;
  foreach ($lines as $line) {
    if ($y < 40) {
      break;
    }
    $stream .= '50 ' . $y . " Td\n(" . cms_attachment_pdf_escape($line) . ") Tj\nT*\n";
    $y -= 14;
  }
  $stream .= "ET\n";

  $objects = [];
  $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
  $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
  $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
  $objects[] = '4 0 obj\n<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "endstream\nendobj\n";
  $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

  $pdf = "%PDF-1.4\n";
  $offsets = [0];
  foreach ($objects as $obj) {
    $offsets[] = strlen($pdf);
    $pdf .= $obj;
  }

  $xrefPos = strlen($pdf);
  $pdf .= "xref\n0 " . count($offsets) . "\n";
  $pdf .= "0000000000 65535 f \n";
  for ($i = 1; $i < count($offsets); $i++) {
    $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
  }
  $pdf .= "trailer\n<< /Size " . count($offsets) . " /Root 1 0 R >>\n";
  $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: no-store');
  echo $pdf;
  exit;
}
