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

function cms_attachment_pdf_vline(float $x, float $y1, float $y2): string
{
  return "0.5 w 0.82 0.84 0.86 RG\n{$x} {$y1} m\n{$x} {$y2} l\nS\n";
}

function cms_attachment_pdf_short_date(string $iso): string
{
  if ($iso === '') {
    return '';
  }
  $ts = strtotime($iso);
  if ($ts === false) {
    return '';
  }
  return date('d M Y', $ts);
}

/** @param array<int, array{stream: string, width: int, height: int}> $pages */
function cms_attachment_pdf_build(array $pages): string
{
  $objects = [];
  $pageRefs = [];
  $nextId = 3;

  foreach ($pages as $page) {
    $contentId = $nextId++;
    $pageId = $nextId++;
    $pageRefs[] = ['pageId' => $pageId, 'contentId' => $contentId, 'width' => $page['width'], 'height' => $page['height']];
    $stream = $page['stream'];
    $objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
  }

  $fontRegularId = $nextId++;
  $fontBoldId = $nextId++;
  $objects[$fontRegularId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
  $objects[$fontBoldId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

  foreach ($pageRefs as $ref) {
    $width = $ref['width'];
    $height = $ref['height'];
    $objects[$ref['pageId']] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$width} {$height}] /Contents {$ref['contentId']} 0 R /Resources << /Font << /F1 {$fontRegularId} 0 R /F2 {$fontBoldId} 0 R >> >> >>";
  }

  $pageObjectIds = array_map(static fn(array $ref): int => $ref['pageId'], $pageRefs);
  $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
  $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', array_map(static fn(int $id): string => "{$id} 0 R", $pageObjectIds)) . '] /Count ' . count($pageObjectIds) . ' >>';

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
  $columns = [
    ['key' => '_num', 'label' => '#', 'width' => 22],
    ['key' => 'full_name', 'label' => 'NAME', 'width' => 92],
    ['key' => 'index_number', 'label' => 'INDEX NO.', 'width' => 78],
    ['key' => 'contact', 'label' => 'CONTACT', 'width' => 62],
    ['key' => 'company_name', 'label' => 'COMPANY', 'width' => 96],
    ['key' => 'location', 'label' => 'LOCATION', 'width' => 68],
    ['key' => 'official_position', 'label' => 'OFFICIAL', 'width' => 62],
    ['key' => 'class_group', 'label' => 'GROUP', 'width' => 72],
    ['key' => 'level', 'label' => 'LVL', 'width' => 28],
    ['key' => 'created_at', 'label' => 'SUBMITTED', 'width' => 68],
  ];

  $pageWidth = 842;
  $pageHeight = 595;
  $marginX = 28;
  $startX = $marginX;
  $tableWidth = array_sum(array_column($columns, 'width'));
  $rowHeight = 14;
  $headerBandHeight = 52;
  $tableTopY = 498;
  $tableBottomY = 36;
  $rowsPerPage = (int) floor(($tableTopY - $tableBottomY - 20) / $rowHeight);

  $tableRows = [];
  foreach ($rows as $i => $row) {
    $formatted = cms_attachment_format_row($row);
    $formatted['_num'] = (string) ($i + 1);
    $formatted['created_at'] = cms_attachment_pdf_short_date($row['created_at'] ?? '');
    $tableRows[] = $formatted;
  }

  $totalRecords = count($tableRows);
  $pageCount = max(1, (int) ceil($totalRecords / max(1, $rowsPerPage)));

  $pages = [];
  $chunks = $tableRows === [] ? [[]] : array_chunk($tableRows, max(1, $rowsPerPage));

  foreach ($chunks as $pageIndex => $chunk) {
    $stream = cms_attachment_pdf_rect($marginX, $pageHeight - $marginX - $headerBandHeight, $pageWidth - ($marginX * 2), $headerBandHeight, [0.04, 0.12, 0.23]);
    $stream .= cms_attachment_pdf_cell($marginX + 8, $pageHeight - 36, strtoupper($title), 13, true);
    if ($subtitle !== '') {
      $stream .= cms_attachment_pdf_cell($marginX + 8, $pageHeight - 52, strtoupper($subtitle), 9, false);
    }
    $meta = 'Generated ' . date('M j, Y g:i A') . '  |  ' . $totalRecords . ' record' . ($totalRecords === 1 ? '' : 's') . '  |  Page ' . ($pageIndex + 1) . ' of ' . $pageCount;
    $stream .= cms_attachment_pdf_cell($marginX + 8, $pageHeight - 68, $meta, 7, false);

    $headerY = $tableTopY;
    $headerBoxHeight = $rowHeight + 6;
    $stream .= cms_attachment_pdf_rect($startX, $headerY - 4, $tableWidth, $headerBoxHeight, [0.93, 0.94, 0.96]);

    $x = $startX;
    foreach ($columns as $column) {
      $stream .= cms_attachment_pdf_cell($x + 3, $headerY, $column['label'], 6, true);
      $x += $column['width'];
    }

    $gridTop = $headerY + $headerBoxHeight - 4;
    $gridBottom = $tableBottomY;
    $stream .= cms_attachment_pdf_hline($startX, $gridTop, $startX + $tableWidth);
    $stream .= cms_attachment_pdf_hline($startX, $headerY - 4, $startX + $tableWidth);

    $x = $startX;
    foreach ($columns as $column) {
      $stream .= cms_attachment_pdf_vline($x, $headerY - 4, $gridBottom);
      $x += $column['width'];
    }
    $stream .= cms_attachment_pdf_vline($startX + $tableWidth, $headerY - 4, $gridBottom);

    if ($chunk === []) {
      $emptyY = $headerY - $rowHeight - 8;
      $stream .= cms_attachment_pdf_cell($startX + 8, $emptyY, 'No registrations in this list yet.', 9, false);
      $stream .= cms_attachment_pdf_hline($startX, $emptyY - 6, $startX + $tableWidth);
    } else {
      $y = $headerY - $rowHeight - 6;
      foreach ($chunk as $rowIndex => $formatted) {
        if ($rowIndex % 2 === 1) {
          $stream .= cms_attachment_pdf_rect($startX, $y - 3, $tableWidth, $rowHeight, [0.98, 0.99, 1.0]);
        }
        $stream .= cms_attachment_pdf_hline($startX, $y - 3, $startX + $tableWidth);

        $x = $startX;
        foreach ($columns as $column) {
          $maxChars = max(4, (int) floor($column['width'] / 3.6));
          $value = cms_attachment_pdf_truncate($formatted[$column['key']] ?? '', $maxChars);
          $stream .= cms_attachment_pdf_cell($x + 3, $y, $value, 6, false);
          $x += $column['width'];
        }
        $y -= $rowHeight;
      }
      $stream .= cms_attachment_pdf_hline($startX, $y - 3, $startX + $tableWidth);
    }

    $pages[] = ['stream' => $stream, 'width' => $pageWidth, 'height' => $pageHeight];
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
