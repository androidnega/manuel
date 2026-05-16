<?php
/**
 * cPanel: copy to public_html/index.php when the site code is in public_html/manuelcode/
 * Also copy deploy/public_html.htaccess → public_html/.htaccess
 */
declare(strict_types=1);

$appFolder = 'manuelcode';
$appRoot = __DIR__ . '/' . $appFolder;

if (!is_dir($appRoot)) {
  http_response_code(500);
  header('Content-Type: text/plain; charset=UTF-8');
  echo 'Missing app folder: ' . $appFolder;
  exit;
}

require $appRoot . '/includes/front-router.php';
front_router_dispatch($appRoot, $appFolder);
