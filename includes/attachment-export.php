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

function cms_attachment_pdf_rect(float $x, float $y, float $w, float $h, array $rgb): string
{
  [$r, $g, $b] = $rgb;
  return "{$r} {$g} {$b} rg\n{$x} {$y} {$w} {$h} re\nf\n";
}

function cms_attachment_pdf_hline(float $x1, float $y, float $x2): string
{
  return "0.5 w 0.82 0.84 0.86 RG\n{$x1} {$y} m\n{$x2} {$y} l\nS\n";
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

function cms_attachment_export_pdf_single(array $row, string $filename, string $title, string $subtitle = ''): void
{
  $labels = cms_attachment_row_labels();
  $formatted = cms_attachment_format_row($row);
  $stream = cms_attachment_pdf_rect(36, 720, 540, 56, [0.04, 0.12, 0.23]);
  $stream .= cms_attachment_pdf_cell(48, 752, strtoupper($title), 13, true);
  if ($subtitle !== '') {
    $stream .= cms_attachment_pdf_cell(48, 734, strtoupper($subtitle), 10, false);
  }
  $stream .= cms_attachment_pdf_cell(48, 700, 'Generated ' . date('M j, Y g:i A'), 9, false);

  $y = 670;
  foreach ($labels as $key => $label) {
    $value = $formatted[$key] ?? '';
    $stream .= cms_attachment_pdf_hline(48, $y + 10, 564);
    $stream .= cms_attachment_pdf_cell(48, $y - 4, strtoupper($label), 9, true);
    $stream .= cms_attachment_pdf_cell(190, $y - 4, $value, 9, false);
    $y -= 28;
  }
  $stream .= cms_attachment_pdf_hline(48, $y + 10, 564);

  $pdf = cms_attachment_pdf_build([
    ['stream' => $stream, 'width' => 612, 'height' => 792],
  ]);

  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: no-store');
  echo $pdf;
  exit;
}

function cms_attachment_export_pdf_table(array $rows, string $filename, string $title, string $subtitle = ''): void
{
  $labels = cms_attachment_row_labels();
  $keys = array_keys($labels);
  $headings = array_merge(['#'], array_values($labels));
  $widths = [28, 82, 76, 64, 92, 76, 72, 76, 88];
  $startX = 22;
  $tableWidth = array_sum($widths);
  $rowHeight = 15;
  $pageWidth = 842;
  $pageHeight = 595;
  $topY = 548;
  $rowsPerPage = 28;

  $tableRows = [];
  foreach ($rows as $i => $row) {
    $formatted = cms_attachment_format_row($row);
    $cells = [(string) ($i + 1)];
    foreach ($keys as $key) {
      $idx = count($cells);
      $max = (int) floor(($widths[$idx] ?? 60) / 3.8);
      $cells[] = cms_attachment_pdf_truncate($formatted[$key] ?? '', max(6, $max));
    }
    $tableRows[] = $cells;
  }

  $pages = [];
  if ($tableRows === []) {
    $stream = cms_attachment_pdf_cell($startX, $topY, strtoupper($title), 14, true);
    $pages[] = ['stream' => $stream, 'width' => $pageWidth, 'height' => $pageHeight];
  } else {
    $chunks = array_chunk($tableRows, $rowsPerPage);
    foreach ($chunks as $pageIndex => $chunk) {
      $stream = cms_attachment_pdf_rect(22, 520, 798, 58, [0.04, 0.12, 0.23]);
      $stream .= cms_attachment_pdf_cell(32, 552, strtoupper($title), 14, true);
      if ($subtitle !== '') {
        $stream .= cms_attachment_pdf_cell(32, 534, strtoupper($subtitle), 10, false);
      }
      $stream .= cms_attachment_pdf_cell(32, 508, 'Generated ' . date('M j, Y g:i A') . '  |  Page ' . ($pageIndex + 1), 8, false);

      $headerY = 488;
      $stream .= cms_attachment_pdf_rect($startX, $headerY - 2, $tableWidth, $rowHeight + 4, [0.95, 0.96, 0.98]);
      $x = $startX;
      foreach ($headings as $ci => $heading) {
        $stream .= cms_attachment_pdf_cell($x + 2, $headerY, strtoupper($heading), 6, true);
        $x += $widths[$ci] ?? 60;
      }
      $stream .= cms_attachment_pdf_hline($startX, $headerY - 4, $startX + $tableWidth);

      $y = $headerY - $rowHeight - 4;
      foreach ($chunk as $rowIndex => $cells) {
        if ($rowIndex % 2 === 1) {
          $stream .= cms_attachment_pdf_rect($startX, $y - 2, $tableWidth, $rowHeight, [0.98, 0.99, 1.0]);
        }
        $stream .= cms_attachment_pdf_hline($startX, $y + $rowHeight - 2, $startX + $tableWidth);
        $x = $startX;
        foreach ($cells as $ci => $cell) {
          $stream .= cms_attachment_pdf_cell($x + 2, $y, $cell, 6, false);
          $x += $widths[$ci] ?? 60;
        }
        $y -= $rowHeight;
      }
      $stream .= cms_attachment_pdf_hline($startX, $y + $rowHeight - 2, $startX + $tableWidth);

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

function cms_attachment_export_pdf(array $rows, string $filename, string $title, string $subtitle = ''): void
{
  if (count($rows) === 1) {
    $formatted = cms_attachment_format_row($rows[0]);
    $singleSubtitle = $subtitle !== '' ? $subtitle : ($formatted['class_group'] ?? '');
    cms_attachment_export_pdf_single($rows[0], $filename, $title, $singleSubtitle);
    return;
  }
  cms_attachment_export_pdf_table($rows, $filename, $title, $subtitle);
}
