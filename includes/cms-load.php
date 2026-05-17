<?php

require_once __DIR__ . '/cms-db.php';
require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/cms-designs.php';
require_once __DIR__ . '/cms-home-hero.php';
require_once __DIR__ . '/analytics.php';

function cms_bootstrap(): void
{
  global $site, $services, $projects, $quotes, $designs, $companies, $stats, $clientLogos, $homePages, $teamMembers, $headerNav, $footerNav;

  $pdo = cms_db();

  $defaults = [
    'services' => $services,
    'projects' => $projects,
    'quotes' => $quotes,
    'designs' => $designs,
    'companies' => $companies,
    'stats' => $stats,
    'clientLogos' => $clientLogos,
    'homePages' => $homePages,
    'site' => $site,
    'headerNav' => cms_nav_to_list($headerNav),
    'footerNav' => cms_nav_to_list($footerNav),
  ];

  cms_seed_from_globals($pdo, $defaults);

  cms_sync_list_defaults($pdo, 'projects', $projects);
  cms_sync_list_defaults($pdo, 'clientLogos', $clientLogos);
  cms_sync_quotes_page_copy($pdo);
  cms_sync_image_paths($pdo);
  cms_sync_seo_image_paths($pdo);
  cms_sync_design_ids($pdo, $designs);
  cms_sync_home_hero_slides($pdo);
  cms_sync_home_hero_monday_slide($pdo);
  cms_sync_home_hero_digital_presence_slide($pdo);
  cms_sync_home_hero_interval($pdo);

  $site = cms_get_list($pdo, 'site', $site);
  $services = cms_get_list($pdo, 'services', $services);
  $projects = cms_get_list($pdo, 'projects', $projects);
  $quotes = cms_get_list($pdo, 'quotes', $quotes);
  $designs = cms_designs_all($pdo, $designs);
  $companies = cms_get_list($pdo, 'companies', $companies);
  $stats = cms_get_list($pdo, 'stats', $stats);
  $clientLogos = cms_get_list($pdo, 'clientLogos', $clientLogos);
  $homePages = cms_get_list($pdo, 'homePages', $homePages);
  $headerNav = cms_nav_from_list(cms_get_list($pdo, 'headerNav', cms_nav_to_list($headerNav)));
  $footerNav = cms_nav_from_list(cms_get_list($pdo, 'footerNav', cms_nav_to_list($footerNav)));
  $teamMembers = cms_team_members($pdo);
}

/** Merge CMS page hero + body over page defaults. */
function cms_page(string $slug, array $defaults): array
{
  $pdo = cms_db();
  $row = cms_get_page($pdo, $slug);
  $body = $defaults['body'] ?? [];
  if ($row) {
    return [
      'label' => $row['hero_label'] !== '' ? $row['hero_label'] : ($defaults['label'] ?? ''),
      'title' => $row['hero_title'] !== '' ? $row['hero_title'] : ($defaults['title'] ?? ''),
      'desc' => $row['hero_desc'] !== '' ? $row['hero_desc'] : ($defaults['desc'] ?? ''),
      'body' => array_merge($body, $row['body'] ?? []),
    ];
  }
  return [
    'label' => $defaults['label'] ?? '',
    'title' => $defaults['title'] ?? '',
    'desc' => $defaults['desc'] ?? '',
    'body' => $body,
  ];
}

function cms_save_contact_message(string $name, string $email, string $subject, string $message): void
{
  $pdo = cms_db();
  $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, ?)');
  $stmt->execute([$name, $email, $subject, $message, date('c')]);
}

function cms_save_quote_request(array $data): void
{
  $pdo = cms_db();
  $stmt = $pdo->prepare('INSERT INTO quote_requests (
    name, email, phone, organization, project_name, project_type, budget_range, timeline, description, referral, created_at
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->execute([
    $data['name'],
    $data['email'],
    $data['phone'] ?: null,
    $data['organization'] ?: null,
    $data['project_name'],
    $data['project_type'],
    $data['budget_range'],
    $data['timeline'],
    $data['description'],
    $data['referral'] ?: null,
    date('c'),
  ]);
}

cms_bootstrap();

if (!defined('CMS_SKIP_ANALYTICS')) {
  analytics_track();
}

require_once __DIR__ . '/maintenance.php';
cms_enforce_maintenance();
