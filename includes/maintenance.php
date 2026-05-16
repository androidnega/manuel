<?php

require_once __DIR__ . '/auth.php';

function cms_is_update_script(): bool
{
  if (defined('CMS_UPDATE_RENDER')) {
    return true;
  }
  $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
  return in_array($script, ['update.php', 'login.php'], true);
}

function cms_enforce_maintenance(): void
{
  if (PHP_SAPI === 'cli' || defined('CMS_SKIP_MAINTENANCE') || defined('CMS_UPDATE_RENDER')) {
    return;
  }
  if (cms_is_update_script()) {
    return;
  }

  auth_start();
  if (auth_user()) {
    return;
  }

  $pdo = cms_db();
  $config = cms_maintenance_config($pdo);
  if (!$config['enabled']) {
    return;
  }

  define('CMS_UPDATE_RENDER', true);
  define('CMS_SKIP_MAINTENANCE', true);

  global $site, $brand;
  if (!isset($site) || !is_array($site)) {
    $site = cms_get_list($pdo, 'site', [
      'name' => 'Manuelcode',
      'title' => 'Manuel Kwofie',
      'tagline' => 'Software • Design • Media',
    ]);
  }

  require dirname(__DIR__) . '/update-render.php';
  exit;
}
