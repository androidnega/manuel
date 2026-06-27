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

function cms_attachment_pdf_chars_per_line(float $columnWidth, int $fontSize = 6): int
{
  return max(4, (int) floor(($columnWidth - 6) / ($fontSize * 0.52)));
}

/** @return list<string> */
function cms_attachment_pdf_wrap_text(string $text, int $maxChars): array
{
  $text = trim($text);
  if ($text === '') {
    return [''];
  }

  $lines = [];
  $words = preg_split('/\s+/', $text) ?: [];
  $current = '';

  $pushCurrent = static function () use (&$lines, &$current): void {
    if ($current !== '') {
      $lines[] = $current;
      $current = '';
    }
  };

  foreach ($words as $word) {
    while (strlen($word) > $maxChars) {
      $pushCurrent();
      $lines[] = substr($word, 0, $maxChars);
      $word = substr($word, $maxChars);
    }
    if ($word === '') {
      continue;
    }
    $candidate = $current === '' ? $word : $current . ' ' . $word;
    if (strlen($candidate) <= $maxChars) {
      $current = $candidate;
    } else {
      $pushCurrent();
      $current = $word;
    }
  }
  $pushCurrent();

  return $lines === [] ? [''] : $lines;
}

function cms_attachment_pdf_cell_lines(float $x, float $y, array $lines, int $size, bool $bold = false, array $rgb = [0.07, 0.09, 0.16], float $lineHeight = 8): string
{
  $stream = '';
  foreach ($lines as $index => $line) {
    $stream .= cms_attachment_pdf_cell($x, $y - ($index * $lineHeight), $line, $size, $bold, $rgb);
  }
  return $stream;
}

function cms_attachment_pdf_cell(float $x, float $y, string $text, int $size, bool $bold = false, array $rgb = [0.07, 0.09, 0.16]): string
{
  $font = $bold ? 'F2' : 'F1';
  [$r, $g, $b] = $rgb;
  return sprintf("%.3f %.3f %.3f rg\nBT\n/{$font} %d Tf\n%.2f %.2f Td\n(%s) Tj\nET\n", $r, $g, $b, $size, $x, $y, cms_attachment_pdf_escape($text));
}

function cms_attachment_pdf_cell_white(float $x, float $y, string $text, int $size, bool $bold = false): string
{
  return cms_attachment_pdf_cell($x, $y, $text, $size, $bold, [1, 1, 1]);
}

function cms_attachment_pdf_cell_muted(float $x, float $y, string $text, int $size, bool $bold = false): string
{
  return cms_attachment_pdf_cell($x, $y, $text, $size, $bold, [0.82, 0.86, 0.92]);
}

function cms_attachment_pdf_rect(float $x, float $y, float $w, float $h, array $rgb): string
{
  [$r, $g, $b] = $rgb;
  return sprintf("%.3f %.3f %.3f rg\n%.2f %.2f %.2f %.2f re\nf\n", $r, $g, $b, $x, $y, $w, $h);
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
  $stream .= cms_attachment_pdf_cell_white(48, 752, strtoupper($title), 13, true);
  if ($subtitle !== '') {
    $stream .= cms_attachment_pdf_cell_muted(48, 734, strtoupper($subtitle), 10, false);
  }
  $stream .= cms_attachment_pdf_cell_muted(48, 700, 'Generated ' . date('M j, Y g:i A'), 9, false);

  $y = 670;
  foreach ($labels as $key => $label) {
    $value = $formatted[$key] ?? '';
    $stream .= cms_attachment_pdf_hline(48, $y + 10, 564);
    $stream .= cms_attachment_pdf_cell(48, $y - 4, strtoupper($label), 9, true, [0.45, 0.48, 0.55]);
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
    ['key' => '_num', 'label' => '#', 'width' => 24],
    ['key' => 'full_name', 'label' => 'NAME', 'width' => 110],
    ['key' => 'index_number', 'label' => 'INDEX NO.', 'width' => 82],
    ['key' => 'contact', 'label' => 'CONTACT', 'width' => 66],
    ['key' => 'company_name', 'label' => 'COMPANY', 'width' => 118],
    ['key' => 'location', 'label' => 'LOCATION', 'width' => 86],
    ['key' => 'official_position', 'label' => 'OFFICIAL', 'width' => 74],
    ['key' => 'class_group', 'label' => 'GROUP', 'width' => 88],
    ['key' => 'level', 'label' => 'LVL', 'width' => 38],
  ];

  $pageWidth = 842;
  $pageHeight = 595;
  $marginX = 28;
  $startX = $marginX;
  $tableWidth = array_sum(array_column($columns, 'width'));
  $fontSize = 6;
  $lineHeight = 8;
  $rowPadding = 4;
  $minRowHeight = 14;
  $headerBandHeight = 52;
  $tableTopY = 498;
  $tableBottomY = 36;
  $headerBoxHeight = $minRowHeight + 6;
  $availableHeight = $tableTopY - $tableBottomY - $headerBoxHeight - 16;

  $tableRows = [];
  foreach ($rows as $i => $row) {
    $formatted = cms_attachment_format_row($row);
    $formatted['_num'] = (string) ($i + 1);
    $tableRows[] = $formatted;
  }

  $preparedRows = [];
  foreach ($tableRows as $formatted) {
    $wrapped = [];
    $maxLines = 1;
    foreach ($columns as $column) {
      $maxChars = cms_attachment_pdf_chars_per_line((float) $column['width'], $fontSize);
      $lines = cms_attachment_pdf_wrap_text($formatted[$column['key']] ?? '', $maxChars);
      $wrapped[$column['key']] = $lines;
      $maxLines = max($maxLines, count($lines));
    }
    $preparedRows[] = [
      'wrapped' => $wrapped,
      'height' => max($minRowHeight, ($maxLines * $lineHeight) + $rowPadding),
    ];
  }

  $pageChunks = [];
  $currentChunk = [];
  $usedHeight = 0;
  foreach ($preparedRows as $preparedRow) {
    if ($currentChunk !== [] && ($usedHeight + $preparedRow['height']) > $availableHeight) {
      $pageChunks[] = $currentChunk;
      $currentChunk = [];
      $usedHeight = 0;
    }
    $currentChunk[] = $preparedRow;
    $usedHeight += $preparedRow['height'];
  }
  if ($currentChunk !== [] || $pageChunks === []) {
    $pageChunks[] = $currentChunk;
  }

  $totalRecords = count($tableRows);
  $pageCount = count($pageChunks);

  $pages = [];
  foreach ($pageChunks as $pageIndex => $chunk) {
    $headerTopY = $pageHeight - $marginX - $headerBandHeight;
    $stream = cms_attachment_pdf_rect($startX, $headerTopY, $tableWidth, $headerBandHeight, [0.04, 0.12, 0.23]);
    $stream .= cms_attachment_pdf_cell_white($startX + 8, $pageHeight - 36, strtoupper($title), 13, true);
    if ($subtitle !== '') {
      $stream .= cms_attachment_pdf_cell_muted($startX + 8, $pageHeight - 52, strtoupper($subtitle), 9, false);
    }
    $meta = 'Generated ' . date('M j, Y g:i A') . '  |  ' . $totalRecords . ' record' . ($totalRecords === 1 ? '' : 's') . '  |  Page ' . ($pageIndex + 1) . ' of ' . $pageCount;
    $stream .= cms_attachment_pdf_cell_muted($startX + 8, $pageHeight - 68, $meta, 7, false);

    $headerY = $tableTopY;
    $stream .= cms_attachment_pdf_rect($startX, $headerY - 4, $tableWidth, $headerBoxHeight, [0.93, 0.94, 0.96]);

    $x = $startX;
    foreach ($columns as $column) {
      $stream .= cms_attachment_pdf_cell($x + 3, $headerY, $column['label'], $fontSize, true, [0.16, 0.20, 0.28]);
      $x += $column['width'];
    }

    $gridTop = $headerY + $headerBoxHeight - 4;
    $stream .= cms_attachment_pdf_hline($startX, $gridTop, $startX + $tableWidth);
    $stream .= cms_attachment_pdf_hline($startX, $headerY - 4, $startX + $tableWidth);

    if ($chunk === []) {
      $emptyY = $headerY - $minRowHeight - 8;
      $gridBottom = $emptyY - 6;
      $stream .= cms_attachment_pdf_cell($startX + 8, $emptyY, 'No registrations in this list yet.', 9, false);
      $stream .= cms_attachment_pdf_hline($startX, $gridBottom, $startX + $tableWidth);
    } else {
      $y = $headerY - $headerBoxHeight - 8;
      $gridBottom = $y;

      foreach ($chunk as $rowIndex => $preparedRow) {
        $rowHeight = $preparedRow['height'];
        $rowTop = $y;
        $rowBottom = $y - $rowHeight;
        $textY = $rowTop - 2;

        if ($rowIndex % 2 === 1) {
          $stream .= cms_attachment_pdf_rect($startX, $rowBottom, $tableWidth, $rowHeight, [0.98, 0.99, 1.0]);
        }

        $x = $startX;
        foreach ($columns as $column) {
          $stream .= cms_attachment_pdf_cell_lines(
            $x + 3,
            $textY,
            $preparedRow['wrapped'][$column['key']],
            $fontSize,
            false,
            [0.07, 0.09, 0.16],
            $lineHeight
          );
          $x += $column['width'];
        }

        $gridBottom = $rowBottom;
        $stream .= cms_attachment_pdf_hline($startX, $gridBottom, $startX + $tableWidth);
        $y = $rowBottom - 2;
      }
    }

    $x = $startX;
    foreach ($columns as $column) {
      $stream .= cms_attachment_pdf_vline($x, $headerY - 4, $gridBottom);
      $x += $column['width'];
    }
    $stream .= cms_attachment_pdf_vline($startX + $tableWidth, $headerY - 4, $gridBottom);

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
