<?php

auth_require();
$pdo = cms_db();
$action = $_POST['action'] ?? '';
$redirect = url('login');

function admin_redirect(string $path, string $flash = '', string $type = 'ok'): void
{
  if ($flash !== '') {
    $sep = str_contains($path, '?') ? '&' : '?';
    $path .= $sep . 'flash=' . urlencode($flash) . '&t=' . urlencode($type);
  }
  header('Location: ' . $path);
  exit;
}

if ($action === 'save_page') {
  $slug = $_POST['slug'] ?? '';
  $allowed = ['home', 'projects', 'services', 'quotes', 'designs', 'about', 'contact'];
  if (!in_array($slug, $allowed, true)) {
    admin_redirect(url('login') . '?p=pages', 'Invalid page.', 'err');
  }
  $body = [];
  foreach ($_POST as $k => $v) {
    if (str_starts_with($k, 'body_')) {
      $body[substr($k, 5)] = trim((string) $v);
    }
  }
  cms_save_page(
    $pdo,
    $slug,
    trim($_POST['hero_label'] ?? ''),
    trim($_POST['hero_title'] ?? ''),
    trim($_POST['hero_desc'] ?? ''),
    $body
  );
  admin_redirect(url('login') . '?p=page&slug=' . urlencode($slug), 'Page saved.');
}

if ($action === 'save_list') {
  $key = $_POST['list_key'] ?? '';
  $allowed = ['services', 'projects', 'quotes', 'designs', 'companies', 'stats', 'clientLogos', 'homePages', 'headerNav', 'footerNav'];
  if (!in_array($key, $allowed, true)) {
    admin_redirect(url('login') . '?p=lists', 'Invalid list.', 'err');
  }
  $json = trim($_POST['json'] ?? '');
  $data = json_decode($json, true);
  if (!is_array($data)) {
    admin_redirect(url('login') . '?p=list&key=' . urlencode($key), 'Invalid JSON.', 'err');
  }
  cms_save_list($pdo, $key, $data);
  admin_redirect(url('login') . '?p=list&key=' . urlencode($key), 'List saved.');
}

if ($action === 'save_site') {
  $site = [
    'name' => trim($_POST['name'] ?? ''),
    'title' => trim($_POST['title'] ?? ''),
    'tagline' => trim($_POST['tagline'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'website' => trim($_POST['website'] ?? ''),
    'whatsapp' => trim($_POST['whatsapp'] ?? ''),
  ];
  cms_save_list($pdo, 'site', $site);
  admin_redirect(url('login') . '?p=settings', 'Site settings saved.');
}

if ($action === 'save_team') {
  $id = (int) ($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $role = trim($_POST['role'] ?? '');
  $bio = trim($_POST['bio'] ?? '');
  $sort = (int) ($_POST['sort_order'] ?? 0);
  if ($name === '' || $role === '') {
    admin_redirect(url('login') . '?p=team', 'Name and role are required.', 'err');
  }
  $photo = null;
  $oldPhoto = null;
  if ($id > 0) {
    $cur = $pdo->prepare('SELECT photo_path FROM team_members WHERE id = ?');
    $cur->execute([$id]);
    $oldPhoto = $cur->fetchColumn() ?: null;
  }

  if (!empty($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
    $dir = dirname(__DIR__) . '/assets/images/team';
    if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
    }
    $mime = mime_content_type($_FILES['photo']['tmp_name']) ?: '';
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowed, true)) {
      admin_redirect(url('login') . '?p=team' . ($id ? '&id=' . $id : ''), 'Photo must be JPG, PNG or WebP.', 'err');
    }
    $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
    $fname = 'team-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . '/' . $fname)) {
      $photo = 'assets/images/team/' . $fname;
      if ($oldPhoto && $oldPhoto !== $photo) {
        $oldFile = dirname(__DIR__) . '/' . ltrim($oldPhoto, '/');
        if (is_file($oldFile)) {
          @unlink($oldFile);
        }
      }
    }
  }

  if ($photo === null) {
    $photo = $oldPhoto;
  }

  if ($id > 0) {
    $stmt = $pdo->prepare('UPDATE team_members SET name = ?, role = ?, bio = ?, photo_path = ?, sort_order = ? WHERE id = ?');
    $stmt->execute([$name, $role, $bio, $photo ?: null, $sort, $id]);
  } else {
    $stmt = $pdo->prepare('INSERT INTO team_members (name, role, bio, photo_path, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $role, $bio, $photo ?: null, $sort, date('c')]);
  }
  admin_redirect(url('login') . '?p=team', 'Team member saved.');
}

if ($action === 'delete_team') {
  $id = (int) ($_POST['id'] ?? 0);
  if ($id > 0) {
    $pdo->prepare('DELETE FROM team_members WHERE id = ?')->execute([$id]);
  }
  admin_redirect(url('login') . '?p=team', 'Team member removed.');
}

if ($action === 'message_read') {
  $id = (int) ($_POST['id'] ?? 0);
  $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
  admin_redirect(url('login') . '?p=messages', 'Marked as read.');
}

if ($action === 'message_delete') {
  $id = (int) ($_POST['id'] ?? 0);
  $pdo->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
  admin_redirect(url('login') . '?p=messages', 'Message deleted.');
}

if ($action === 'quote_read') {
  $id = (int) ($_POST['id'] ?? 0);
  $pdo->prepare('UPDATE quote_requests SET is_read = 1 WHERE id = ?')->execute([$id]);
  admin_redirect(url('login') . '?p=quoterequests', 'Marked as read.');
}

if ($action === 'quote_delete') {
  $id = (int) ($_POST['id'] ?? 0);
  $pdo->prepare('DELETE FROM quote_requests WHERE id = ?')->execute([$id]);
  admin_redirect(url('login') . '?p=quoterequests', 'Quote request deleted.');
}

if ($action === 'save_maintenance') {
  $enabled = !empty($_POST['maintenance_enabled']);
  $endsRaw = trim($_POST['maintenance_ends_at'] ?? '');
  $endsAt = '';
  if ($endsRaw !== '') {
    $ts = strtotime($endsRaw);
    if ($ts !== false) {
      $endsAt = date('c', $ts);
    }
  }
  $config = [
    'enabled' => $enabled,
    'ends_at' => $endsAt,
    'title' => trim($_POST['maintenance_title'] ?? '') ?: cms_maintenance_defaults()['title'],
    'caption' => trim($_POST['maintenance_caption'] ?? '') ?: cms_maintenance_defaults()['caption'],
  ];
  cms_save_maintenance_config($pdo, $config);
  $msg = $enabled ? 'Update mode is on — visitors see the countdown page.' : 'Update mode is off — site is live.';
  admin_redirect(url('login') . '?p=settings', $msg);
}

if ($action === 'change_password') {
  $user = auth_user();
  $pass = $_POST['password'] ?? '';
  if ($user && strlen($pass) >= 8) {
    auth_change_password((int) $user['id'], $pass);
    admin_redirect(url('login') . '?p=settings', 'Password updated.');
  }
  admin_redirect(url('login') . '?p=settings', 'Password must be at least 8 characters.', 'err');
}

admin_redirect(url('login'));
