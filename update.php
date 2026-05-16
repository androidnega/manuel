<?php
define('CMS_SKIP_MAINTENANCE', true);
define('CMS_SKIP_ANALYTICS', true);

require_once __DIR__ . '/includes/cms-db.php';
require_once __DIR__ . '/includes/data.php';

$pdo = cms_db();
$config = cms_maintenance_config($pdo);

if (!$config['enabled']) {
  header('Location: ' . page_url('index.php'));
  exit;
}

define('CMS_UPDATE_RENDER', true);
require __DIR__ . '/update-render.php';
