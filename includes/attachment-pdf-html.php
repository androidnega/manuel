<?php

use Dompdf\Dompdf;
use Dompdf\Options;

function cms_attachment_pdf_styles(): string
{
  $path = dirname(__DIR__) . '/assets/css/attachment-export-pdf.css';
  if (!is_file($path)) {
    return '';
  }
  return file_get_contents($path) ?: '';
}

function cms_attachment_render_pdf_html(array $rows, string $docTitle, string $docSubtitle, bool $landscape = true, string $docLevel = ''): string
{
  $isSingle = count($rows) === 1;
  $bodyClass = 'font-sans text-ink' . ($isSingle ? ' page-portrait' : '');

  ob_start();
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($docTitle) ?></title>
  <style><?= cms_attachment_pdf_styles() ?></style>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
  <?php
  if ($isSingle) {
    $row = $rows[0];
    include dirname(__DIR__) . '/admin/views/attachments-pdf-single.php';
  } else {
    include dirname(__DIR__) . '/admin/views/attachments-pdf-list.php';
  }
  ?>
</body>
</html>
  <?php
  return (string) ob_get_clean();
}

function cms_attachment_dompdf_available(): bool
{
  static $available = null;
  if ($available !== null) {
    return $available;
  }
  $autoload = dirname(__DIR__) . '/vendor/autoload.php';
  if (is_file($autoload)) {
    require_once $autoload;
  }
  $available = class_exists(Dompdf::class);
  return $available;
}

function cms_attachment_export_pdf(array $rows, string $filename, string $title, string $subtitle = '', string $level = ''): void
{
  $isSingle = count($rows) === 1;
  $html = cms_attachment_render_pdf_html($rows, $title, $subtitle, !$isSingle, $level);

  if (!cms_attachment_dompdf_available()) {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8" /><title>PDF export unavailable</title>';
    echo '<script src="' . htmlspecialchars(asset('assets/js/suppress-tailwind-cdn-warn.js')) . '"></script>';
    echo '<script src="https://cdn.tailwindcss.com"></script></head>';
    echo '<body class="bg-cloud p-8 font-sans text-ink"><div class="mx-auto max-w-lg rounded-2xl border border-line bg-white p-6">';
    echo '<p class="text-sm font-bold text-red-600">PDF library not installed.</p>';
    echo '<p class="mt-2 text-sm text-body">Run <code class="rounded bg-cloud px-1.5 py-0.5 text-xs">composer install --no-dev</code> on the server, then try again.</p>';
    echo '</div></body></html>';
    exit;
  }

  $options = new Options();
  $options->set('isHtml5ParserEnabled', true);
  $options->set('isRemoteEnabled', false);
  $options->set('defaultFont', 'DejaVu Sans');

  $dompdf = new Dompdf($options);
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', $isSingle ? 'portrait' : 'landscape');
  $dompdf->render();
  $dompdf->stream($filename, ['Attachment' => true]);
  exit;
}
