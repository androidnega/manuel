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

if ($action === 'save_home_hero_settings') {
  $sec = max(3, min(600, (int) ($_POST['slide_interval'] ?? 6)));
  cms_set_setting($pdo, 'home_hero_interval', (string) ($sec * 1000));
  admin_redirect(url('login') . '?p=homehero', 'Slideshow timing saved.');
}

if ($action === 'save_home_hero_slide') {
  $all = cms_home_hero_slides($pdo);
  $id = trim($_POST['id'] ?? '');
  $existing = $id !== '' ? cms_home_hero_by_id($all, $id) : null;
  $alt = trim($_POST['alt'] ?? '');

  if (!empty($_FILES['image_file']['tmp_name'])) {
    $uploaded = cms_upload_home_hero_image($_FILES['image_file'], $alt ?: 'home-hero');
    if ($uploaded === null) {
      admin_redirect(url('login') . '?p=homehero' . ($id ? '&id=' . urlencode($id) : ''), 'image must be JPG, PNG or WebP.', 'err');
    }
    if ($existing && !empty($existing['image']) && $existing['image'] !== $uploaded) {
      $oldFile = dirname(__DIR__) . '/' . ltrim($existing['image'], '/');
      if (is_file($oldFile)) {
        @unlink($oldFile);
      }
    }
    $_POST['image'] = $uploaded;
  }

  $item = cms_home_hero_build_slide_from_post($_POST, $existing);
  if (trim($item['image'] ?? '') === '') {
    admin_redirect(url('login') . '?p=homehero' . ($id ? '&id=' . urlencode($id) : ''), 'Image is required.', 'err');
  }

  $found = false;
  foreach ($all as $i => $row) {
    if (($row['id'] ?? '') === $item['id']) {
      $all[$i] = $item;
      $found = true;
      break;
    }
  }
  if (!$found) {
    $all[] = $item;
  }
  cms_save_home_hero_slides($pdo, $all);
  admin_redirect(url('login') . '?p=homehero&id=' . urlencode($item['id']), 'Home hero slide saved.');
}

if ($action === 'delete_home_hero_slide') {
  $id = trim($_POST['id'] ?? '');
  $all = cms_home_hero_slides($pdo);
  $all = array_values(array_filter($all, static fn($row) => ($row['id'] ?? '') !== $id));
  cms_save_home_hero_slides($pdo, $all);
  admin_redirect(url('login') . '?p=homehero', 'Slide removed.');
}

if ($action === 'save_design') {
  global $designs;
  $all = cms_designs_all($pdo, $designs);
  $id = trim($_POST['id'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $type = trim($_POST['type'] ?? '');
  if ($title === '' || $type === '') {
    admin_redirect(url('login') . '?p=gallery' . ($id ? '&id=' . urlencode($id) : ''), 'Title and type are required.', 'err');
  }
  $existing = $id !== '' ? cms_design_by_id($all, $id) : null;
  $image = trim($_POST['image'] ?? '');
  if ($existing && $image === '') {
    $image = trim($existing['image'] ?? '');
  }
  if (!empty($_FILES['image_file']['tmp_name'])) {
    $uploaded = cms_upload_design_image($_FILES['image_file'], $title);
    if ($uploaded === null) {
      admin_redirect(url('login') . '?p=gallery' . ($id ? '&id=' . urlencode($id) : ''), 'Image must be JPG, PNG or WebP.', 'err');
    }
    if ($existing && !empty($existing['image']) && $existing['image'] !== $uploaded) {
      $oldFile = dirname(__DIR__) . '/' . ltrim($existing['image'], '/');
      if (is_file($oldFile)) {
        @unlink($oldFile);
      }
    }
    $image = $uploaded;
  }
  $item = [
    'id' => $id !== '' ? $id : bin2hex(random_bytes(8)),
    'title' => $title,
    'type' => $type,
    'alt' => trim($_POST['alt'] ?? ''),
    'share_text' => trim($_POST['share_text'] ?? ''),
    'variant' => trim($_POST['variant'] ?? ''),
    'image' => $image,
    'sort_order' => (int) ($_POST['sort_order'] ?? 0),
    'published' => !empty($_POST['published']),
  ];
  $found = false;
  foreach ($all as $i => $row) {
    if (($row['id'] ?? '') === $item['id']) {
      $all[$i] = $item;
      $found = true;
      break;
    }
  }
  if (!$found) {
    $all[] = $item;
  }
  cms_save_designs_list($pdo, $all);
  admin_redirect(url('login') . '?p=gallery&id=' . urlencode($item['id']), 'Design saved.');
}

if ($action === 'delete_design') {
  global $designs;
  $id = trim($_POST['id'] ?? '');
  $all = cms_designs_all($pdo, $designs);
  $all = array_values(array_filter($all, static fn($row) => ($row['id'] ?? '') !== $id));
  cms_save_designs_list($pdo, $all);
  admin_redirect(url('login') . '?p=gallery', 'Design removed.');
}

if ($action === 'save_mail') {
  cms_save_mail_config($pdo, [
    'from_email' => trim($_POST['from_email'] ?? ''),
    'from_name' => trim($_POST['from_name'] ?? ''),
    'reply_to' => trim($_POST['reply_to'] ?? ''),
    'notify_on_news' => !empty($_POST['notify_on_news']),
    'newsletter_subject' => trim($_POST['newsletter_subject'] ?? ''),
  ]);
  admin_redirect(url('login') . '?p=settings', 'Mail settings saved.');
}

if ($action === 'save_newsletter_modal') {
  cms_save_newsletter_modal_config($pdo, [
    'enabled' => !empty($_POST['modal_enabled']),
    'scroll_percent' => (int) ($_POST['scroll_percent'] ?? 85),
    'title' => trim($_POST['modal_title'] ?? ''),
    'subtitle' => trim($_POST['modal_subtitle'] ?? ''),
    'button_text' => trim($_POST['modal_button_text'] ?? ''),
    'image' => trim($_POST['modal_image'] ?? ''),
    'success_message' => trim($_POST['modal_success_message'] ?? ''),
  ]);
  admin_redirect(url('login') . '?p=settings', 'Newsletter modal saved.');
}

if ($action === 'save_news') {
  $id = (int) ($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  if ($title === '') {
    admin_redirect(url('login') . '?p=newsedit' . ($id ? '&id=' . $id : ''), 'Title is required.', 'err');
  }
  $wasPublished = false;
  if ($id > 0) {
    $existing = cms_news_post_by_id($pdo, $id);
    $wasPublished = $existing && !empty($existing['is_published']);
  }
  $postId = cms_save_news_post($pdo, [
    'id' => $id,
    'title' => $title,
    'slug' => trim($_POST['slug'] ?? ''),
    'excerpt' => trim($_POST['excerpt'] ?? ''),
    'content_html' => cms_sanitize_news_html($_POST['content_html'] ?? ''),
    'cover_image' => trim($_POST['cover_image'] ?? ''),
    'is_published' => !empty($_POST['is_published']),
    'published_at' => trim($_POST['published_at'] ?? ''),
  ]);
  $saved = cms_news_post_by_id($pdo, $postId);
  $flash = 'Post saved.';
  if ($saved && !empty($saved['is_published']) && !empty($_POST['notify_subscribers'])) {
    $sent = cms_broadcast_news_post($pdo, $saved);
    $flash .= $sent > 0 ? " Emailed {$sent} subscriber(s)." : ' No emails sent (check mail settings or subscribers).';
  }
  admin_redirect(url('login') . '?p=newsedit&id=' . $postId, $flash);
}

if ($action === 'delete_news') {
  $id = (int) ($_POST['id'] ?? 0);
  if ($id > 0) {
    cms_delete_news_post($pdo, $id);
  }
  admin_redirect(url('login') . '?p=news', 'Post deleted.');
}

if ($action === 'delete_subscriber') {
  $id = (int) ($_POST['id'] ?? 0);
  if ($id > 0) {
    $pdo->prepare('DELETE FROM newsletter_subscribers WHERE id = ?')->execute([$id]);
  }
  admin_redirect(url('login') . '?p=news', 'Subscriber removed.');
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
