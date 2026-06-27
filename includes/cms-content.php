<?php

/** News, newsletter subscribers, modal config, and outbound mail. */

function cms_content_migrate(PDO $pdo): void
{
  $pdo->exec('CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    created_at TEXT NOT NULL
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS news_posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    excerpt TEXT,
    content_html TEXT NOT NULL,
    cover_image TEXT,
    is_published INTEGER NOT NULL DEFAULT 0,
    published_at TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  )');
}

function cms_mail_defaults(): array
{
  return [
    'from_email' => '',
    'from_name' => 'Manuelcode',
    'reply_to' => '',
    'notify_on_news' => true,
    'newsletter_subject' => 'New from Manuelcode',
  ];
}

function cms_mail_config(PDO $pdo): array
{
  $raw = cms_get_setting($pdo, 'mail', '');
  $data = $raw !== '' ? json_decode($raw, true) : [];
  return array_merge(cms_mail_defaults(), is_array($data) ? $data : []);
}

function cms_save_mail_config(PDO $pdo, array $config): void
{
  $payload = array_merge(cms_mail_defaults(), $config);
  $payload['notify_on_news'] = !empty($payload['notify_on_news']);
  cms_set_setting($pdo, 'mail', json_encode($payload, JSON_UNESCAPED_UNICODE));
}

function cms_newsletter_modal_defaults(): array
{
  return [
    'enabled' => true,
    'scroll_percent' => 85,
    'title' => 'Get updates from Manuelcode',
    'subtitle' => 'Projects, news and creative work — no spam.',
    'button_text' => 'Subscribe',
    'image' => 'assets/images/manuelcode-leadership-quote-poster-design-ghana.jpg',
    'success_message' => "You're subscribed. Thank you!",
  ];
}

function cms_newsletter_modal_config(PDO $pdo): array
{
  $raw = cms_get_setting($pdo, 'newsletter_modal', '');
  $data = $raw !== '' ? json_decode($raw, true) : [];
  $config = array_merge(cms_newsletter_modal_defaults(), is_array($data) ? $data : []);
  $config['enabled'] = !empty($config['enabled']);
  $config['scroll_percent'] = max(50, min(98, (int) ($config['scroll_percent'] ?? 85)));
  return $config;
}

function cms_save_newsletter_modal_config(PDO $pdo, array $config): void
{
  $payload = array_merge(cms_newsletter_modal_defaults(), $config);
  $payload['enabled'] = !empty($payload['enabled']);
  $payload['scroll_percent'] = max(50, min(98, (int) $payload['scroll_percent']));
  cms_set_setting($pdo, 'newsletter_modal', json_encode($payload, JSON_UNESCAPED_UNICODE));
}

function news_article_url(string $slug): string
{
  return htmlspecialchars(url('news/' . trim($slug, '/')), ENT_QUOTES, 'UTF-8');
}

function cms_slugify(string $text): string
{
  $text = strtolower(trim($text));
  $text = preg_replace('/[^a-z0-9]+/', '-', $text);
  return trim($text, '-') ?: 'post';
}

function cms_news_posts(PDO $pdo, bool $publishedOnly = false): array
{
  $sql = 'SELECT * FROM news_posts';
  if ($publishedOnly) {
    $sql .= ' WHERE is_published = 1';
  }
  $sql .= ' ORDER BY COALESCE(published_at, created_at) DESC';
  return $pdo->query($sql)->fetchAll();
}

function cms_news_post_by_slug(PDO $pdo, string $slug): ?array
{
  $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE slug = ? LIMIT 1');
  $stmt->execute([$slug]);
  $row = $stmt->fetch();
  return $row ?: null;
}

function cms_news_post_by_id(PDO $pdo, int $id): ?array
{
  $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE id = ? LIMIT 1');
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

function cms_save_news_post(PDO $pdo, array $data): int
{
  $id = (int) ($data['id'] ?? 0);
  $title = trim($data['title'] ?? '');
  $slug = trim($data['slug'] ?? '');
  if ($slug === '') {
    $slug = cms_slugify($title);
  } else {
    $slug = cms_slugify($slug);
  }
  $slug = cms_unique_news_slug($pdo, $slug, $id);

  $now = date('c');
  $published = !empty($data['is_published']);
  $publishedAt = $data['published_at'] ?? '';
  if ($published && $publishedAt === '') {
    $publishedAt = $now;
  }
  if (!$published) {
    $publishedAt = '';
  }

  $fields = [
    'slug' => $slug,
    'title' => $title,
    'excerpt' => trim($data['excerpt'] ?? ''),
    'content_html' => (string) ($data['content_html'] ?? ''),
    'cover_image' => trim($data['cover_image'] ?? ''),
    'is_published' => $published ? 1 : 0,
    'published_at' => $publishedAt,
    'updated_at' => $now,
  ];

  if ($id > 0) {
    $existing = cms_news_post_by_id($pdo, $id);
    if ($published && empty($existing['published_at'])) {
      $fields['published_at'] = $now;
    } elseif (!$published) {
      $fields['published_at'] = '';
    } elseif ($publishedAt !== '') {
      $fields['published_at'] = $publishedAt;
    } else {
      $fields['published_at'] = $existing['published_at'] ?? $now;
    }
    $pdo->prepare('UPDATE news_posts SET slug = ?, title = ?, excerpt = ?, content_html = ?, cover_image = ?, is_published = ?, published_at = ?, updated_at = ? WHERE id = ?')
      ->execute([
        $fields['slug'],
        $fields['title'],
        $fields['excerpt'],
        $fields['content_html'],
        $fields['cover_image'],
        $fields['is_published'],
        $fields['published_at'],
        $fields['updated_at'],
        $id,
      ]);
    return $id;
  }

  $pdo->prepare('INSERT INTO news_posts (slug, title, excerpt, content_html, cover_image, is_published, published_at, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
    ->execute([
      $fields['slug'],
      $fields['title'],
      $fields['excerpt'],
      $fields['content_html'],
      $fields['cover_image'],
      $fields['is_published'],
      $fields['published_at'],
      $now,
      $now,
    ]);
  return (int) $pdo->lastInsertId();
}

function cms_unique_news_slug(PDO $pdo, string $slug, int $excludeId = 0): string
{
  $base = $slug;
  $n = 0;
  while (true) {
    $try = $n === 0 ? $base : $base . '-' . $n;
    $stmt = $pdo->prepare('SELECT id FROM news_posts WHERE slug = ? AND id != ? LIMIT 1');
    $stmt->execute([$try, $excludeId]);
    if (!$stmt->fetch()) {
      return $try;
    }
    $n++;
  }
}

function cms_delete_news_post(PDO $pdo, int $id): void
{
  $pdo->prepare('DELETE FROM news_posts WHERE id = ?')->execute([$id]);
}

function cms_newsletter_subscribe(PDO $pdo, string $email): array
{
  $email = trim($email);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return ['ok' => false, 'message' => 'Please enter a valid email address.'];
  }
  $exists = $pdo->prepare('SELECT COUNT(*) FROM newsletter_subscribers WHERE LOWER(email) = LOWER(?)');
  $exists->execute([$email]);
  if ((int) $exists->fetchColumn() > 0) {
    return ['ok' => true, 'message' => 'You are already subscribed.', 'new' => false];
  }
  try {
    $pdo->prepare('INSERT INTO newsletter_subscribers (email, created_at) VALUES (?, ?)')
      ->execute([strtoupper(trim($email)), date('c')]);
    return ['ok' => true, 'message' => 'Subscribed successfully.', 'new' => true];
  } catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'UNIQUE')) {
      return ['ok' => true, 'message' => 'You are already subscribed.', 'new' => false];
    }
    throw $e;
  }
}

function cms_newsletter_subscribers(PDO $pdo): array
{
  return $pdo->query('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();
}

function cms_newsletter_count(PDO $pdo): int
{
  return (int) $pdo->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn();
}

function cms_mail_from_address(PDO $pdo): array
{
  global $site;
  $mail = cms_mail_config($pdo);
  $email = trim($mail['from_email'] ?? '');
  if ($email === '' && !empty($site['email'])) {
    $email = $site['email'];
  }
  $name = trim($mail['from_name'] ?? '') ?: ($site['name'] ?? 'Manuelcode');
  $reply = trim($mail['reply_to'] ?? '') ?: $email;
  return ['email' => $email, 'name' => $name, 'reply' => $reply];
}

function cms_send_mail(PDO $pdo, string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
  $from = cms_mail_from_address($pdo);
  if ($from['email'] === '') {
    return false;
  }
  if ($textBody === '') {
    $textBody = trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody))));
  }
  $boundary = 'mc_' . bin2hex(random_bytes(8));
  $headers = [
    'MIME-Version: 1.0',
    'From: ' . cms_mail_format_address($from['name'], $from['email']),
    'Reply-To: ' . $from['reply'],
    'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
  ];
  $body = "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n{$textBody}\r\n\r\n";
  $body .= "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$htmlBody}\r\n\r\n--{$boundary}--";
  return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}

function cms_mail_format_address(string $name, string $email): string
{
  $name = str_replace(['"', "\r", "\n"], '', $name);
  return $name !== '' ? "\"{$name}\" <{$email}>" : $email;
}

function cms_broadcast_news_post(PDO $pdo, array $post): int
{
  $mail = cms_mail_config($pdo);
  if (empty($mail['notify_on_news'])) {
    return 0;
  }
  global $site;
  $subscribers = cms_newsletter_subscribers($pdo);
  if ($subscribers === []) {
    return 0;
  }
  $articleUrl = site_url('news/' . $post['slug']);
  $subject = trim($mail['newsletter_subject'] ?? '') ?: 'New from Manuelcode';
  $title = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
  $excerpt = htmlspecialchars($post['excerpt'] ?? '', ENT_QUOTES, 'UTF-8');
  $siteName = htmlspecialchars($site['name'] ?? 'Manuelcode', ENT_QUOTES, 'UTF-8');
  $html = '<div style="font-family:system-ui,sans-serif;max-width:560px;margin:0 auto;color:#101828;">';
  $html .= '<p style="font-size:12px;font-weight:700;color:#ff7a00;text-transform:uppercase;letter-spacing:.12em;">' . $siteName . '</p>';
  $html .= '<h1 style="font-size:22px;line-height:1.25;margin:12px 0;">' . $title . '</h1>';
  if ($excerpt !== '') {
    $html .= '<p style="color:#475467;line-height:1.6;">' . $excerpt . '</p>';
  }
  $html .= '<p><a href="' . htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#0b1e3a;color:#fff;padding:12px 20px;border-radius:999px;text-decoration:none;font-weight:700;">Read the full post</a></p>';
  $html .= '</div>';
  $sent = 0;
  foreach ($subscribers as $sub) {
    if (cms_send_mail($pdo, $sub['email'], $subject . ': ' . $post['title'], $html)) {
      $sent++;
    }
  }
  return $sent;
}

function cms_sanitize_news_html(string $html): string
{
  $html = trim($html);
  if ($html === '') {
    return '';
  }
  $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
  $html = preg_replace('#\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
  return $html;
}
