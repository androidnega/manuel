<?php
define('CMS_SKIP_ANALYTICS', true);
require_once __DIR__ . '/includes/data.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

$classGroup = trim((string) ($_GET['class_group'] ?? ''));
$indexNumber = trim((string) ($_GET['index_number'] ?? ''));
$pdo = cms_db();
$classGroups = cms_attachment_class_groups($pdo);

if ($indexNumber === '' || $classGroup === '' || !isset($classGroups[$classGroup])) {
  echo json_encode(['ok' => true, 'found' => false]);
  exit;
}

$row = cms_attachment_find_by_index($pdo, $indexNumber, $classGroup);
if (!$row) {
  echo json_encode(['ok' => true, 'found' => false]);
  exit;
}

$companies = cms_attachment_companies_from_row($row);
$max = cms_attachment_max_companies();
$count = count($companies);

echo json_encode([
  'ok' => true,
  'found' => true,
  'id' => (int) $row['id'],
  'full_name' => strtoupper($row['full_name'] ?? ''),
  'index_number' => strtoupper($row['index_number'] ?? ''),
  'contact' => strtoupper($row['contact'] ?? ''),
  'class_group' => $row['class_group'],
  'companies' => $companies,
  'company_count' => $count,
  'slots_remaining' => max(0, $max - $count),
  'max_companies' => $max,
  'can_add' => $count < $max,
], JSON_UNESCAPED_UNICODE);
