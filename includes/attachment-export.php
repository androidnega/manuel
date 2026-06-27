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

function cms_attachment_pdf_escape(string $text): string
{
  $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $text) ?? '';
  return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function cms_attachment_pdf_truncate(string $text, int $max): string
{
  $text = trim($text);
  if (strlen($text) <= $max) {
    return $text;
  }
  return substr($text, 0, max(0, $max - 3)) . '...';
}

function cms_attachment_pdf_cell(float $x, float $y, string $text, int $size, bool $bold = false): string
{
  $font = $bold ? 'F2' : 'F1';
  return "BT\n/{$font} {$size} Tf\n{$x} {$y} Td\n(" . cms_attachment_pdf_escape($text) . ") Tj\nET\n";
}

/** @param array<int, array{stream: string, width: int, height: int}> $pages */
function cms_attachment_pdf_build(array $pages): string
{
  $objects = [];
  $pageObjectIds = [];
  $nextId = 3;

  foreach ($pages as $page) {
    $contentId = $nextId++;
    $pageId = $nextId++;
    $pageObjectIds[] = $pageId;
    $stream = $page['stream'];
    $width = $page['width'];
    $height = $page['height'];
    $objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
    $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$width} {$height}] /Contents {$contentId} 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>";
  }

  $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
  $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', array_map(static fn(int $id): string => "{$id} 0 R", $pageObjectIds)) . '] /Count ' . count($pageObjectIds) . ' >>';
  $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
  $objects[6] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

  ksort($objects);
  $pdf = "%PDF-1.4\n";
  $offsets = [0];
  foreach ($objects as $id => $body) {
    $offsets[$id] = strlen($pdf);
    $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
  }

  $xrefPos = strlen($pdf);
  $maxId = max(array_keys($objects));
  $pdf .= "xref\n0 " . ($maxId + 1) . "\n";
  $pdf .= "0000000000 65535 f \n";
  for ($i = 1; $i <= $maxId; $i++) {
    $pdf .= isset($offsets[$i])
      ? sprintf("%010d 00000 n \n", $offsets[$i])
      : "0000000000 00000 n \n";
  }
  $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\n";
  $pdf .= "startxref\n{$xrefPos}\n%%EOF";

  return $pdf;
}

function cms_attachment_export_pdf_single(array $row, string $filename, string $title): void
{
  $labels = cms_attachment_row_labels();
  $formatted = cms_attachment_format_row($row);
  $stream = cms_attachment_pdf_cell(50, 750, $title, 14, true);
  $stream .= cms_attachment_pdf_cell(50, 730, 'Generated ' . date('M j, Y g:i A'), 9, false);
  $stream .= cms_attachment_pdf_cell(50, 712, str_repeat('-', 72), 9, false);

  $y = 692;
  foreach ($labels as $key => $label) {
    $value = $formatted[$key] ?? '';
    $stream .= cms_attachment_pdf_cell(50, $y, $label . ':', 10, true);
    $stream .= cms_attachment_pdf_cell(190, $y, $value, 10, false);
    $y -= 22;
  }

  $pdf = cms_attachment_pdf_build([
    ['stream' => $stream, 'width' => 612, 'height' => 792],
  ]);

  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: no-store');
  echo $pdf;
  exit;
}

function cms_attachment_export_pdf_table(array $rows, string $filename, string $title): void
{
  $labels = cms_attachment_row_labels();
  $keys = array_keys($labels);
  $columns = array_merge(['#'], array_values($labels));
  $widths = [24, 74, 80, 64, 82, 70, 74, 92, 74];
  $startX = 24;
  $rowHeight = 14;
  $pageWidth = 792;
  $pageHeight = 612;
  $topY = 568;
  $rowsPerPage = 34;

  $tableRows = [];
  foreach ($rows as $i => $row) {
    $formatted = cms_attachment_format_row($row);
    $cells = [(string) ($i + 1)];
    foreach ($keys as $key) {
      $idx = count($cells);
      $max = (int) floor(($widths[$idx] ?? 60) / 4.2);
      $cells[] = cms_attachment_pdf_truncate($formatted[$key] ?? '', max(8, $max));
    }
    $tableRows[] = $cells;
  }

  if ($tableRows === []) {
    $stream = cms_attachment_pdf_cell($startX, $topY, $title, 13, true);
    $stream .= cms_attachment_pdf_cell($startX, $topY - 20, 'No records found.', 10, false);
    $pages = [['stream' => $stream, 'width' => $pageWidth, 'height' => $pageHeight]];
  } else {
    $pages = [];
    $chunks = array_chunk($tableRows, $rowsPerPage);
    foreach ($chunks as $pageIndex => $chunk) {
      $stream = cms_attachment_pdf_cell($startX, $topY, $title, 13, true);
      $stream .= cms_attachment_pdf_cell(
        $startX,
        $topY - 16,
        'Generated ' . date('M j, Y g:i A') . ' — Page ' . ($pageIndex + 1),
        8,
        false
      );

      $headerY = $topY - 36;
      $x = $startX;
      foreach ($columns as $ci => $heading) {
        $stream .= cms_attachment_pdf_cell($x, $headerY, $heading, 7, true);
        $x += $widths[$ci] ?? 60;
      }

      $y = $headerY - $rowHeight;
      foreach ($chunk as $cells) {
        $x = $startX;
        foreach ($cells as $ci => $cell) {
          $stream .= cms_attachment_pdf_cell($x, $y, $cell, 7, false);
          $x += $widths[$ci] ?? 60;
        }
        $y -= $rowHeight;
      }

      $pages[] = ['stream' => $stream, 'width' => $pageWidth, 'height' => $pageHeight];
    }
  }

  $pdf = cms_attachment_pdf_build($pages);
  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: no-store');
  echo $pdf;
  exit;
}

function cms_attachment_export_pdf(array $rows, string $filename, string $title): void
{
  if (count($rows) === 1) {
    cms_attachment_export_pdf_single($rows[0], $filename, $title);
    return;
  }
  cms_attachment_export_pdf_table($rows, $filename, $title);
}
