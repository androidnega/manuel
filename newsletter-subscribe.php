<?php
require_once __DIR__ . '/includes/data.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
  exit;
}

$email = trim($_POST['email'] ?? '');
if ($email === '') {
  $raw = file_get_contents('php://input');
  if ($raw !== '') {
    $json = json_decode($raw, true);
    if (is_array($json)) {
      $email = trim($json['email'] ?? '');
    }
  }
}

$pdo = cms_db();
$result = cms_newsletter_subscribe($pdo, $email);
$modal = cms_newsletter_modal_config($pdo);
if ($result['ok'] && !empty($modal['success_message'])) {
  $result['message'] = $modal['success_message'];
}
echo json_encode($result);
