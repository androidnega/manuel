<?php

/** Design gallery list (CMS) + WhatsApp share helpers. */

function cms_designs_normalize(array $items): array
{
  $out = [];
  $changed = false;
  foreach ($items as $item) {
    if (!is_array($item)) {
      continue;
    }
    if (empty($item['id'])) {
      $item['id'] = bin2hex(random_bytes(8));
      $changed = true;
    }
    if (!isset($item['published'])) {
      $item['published'] = true;
    } else {
      $item['published'] = !empty($item['published']);
    }
    $item['sort_order'] = (int) ($item['sort_order'] ?? 0);
    $out[] = $item;
  }
  usort($out, static function ($a, $b) {
    $oa = (int) ($a['sort_order'] ?? 0);
    $ob = (int) ($b['sort_order'] ?? 0);
    if ($oa === $ob) {
      return strcmp($a['title'] ?? '', $b['title'] ?? '');
    }
    return $oa <=> $ob;
  });
  return $out;
}

function cms_designs_all(PDO $pdo, array $defaults): array
{
  $items = cms_get_list($pdo, 'designs', $defaults);
  return cms_designs_normalize(is_array($items) ? $items : []);
}

function cms_designs_public(array $items): array
{
  return array_values(array_filter($items, static function ($item) {
    return !empty($item['published']);
  }));
}

function cms_design_by_id(array $items, string $id): ?array
{
  foreach ($items as $item) {
    if (($item['id'] ?? '') === $id) {
      return $item;
    }
  }
  return null;
}

function cms_save_designs_list(PDO $pdo, array $items): void
{
  cms_save_list($pdo, 'designs', cms_designs_normalize($items));
}

function design_share_message(array $d): string
{
  global $site;
  $custom = trim($d['share_text'] ?? '');
  if ($custom !== '') {
    return $custom;
  }
  $title = trim($d['title'] ?? 'Design');
  $type = trim($d['type'] ?? '');
  $lines = [$title . ($type !== '' ? ' — ' . $type : '')];
  if (!empty($d['image'])) {
    $lines[] = site_url(seo_resolve_image_path($d['image']));
  }
  $lines[] = 'More work: ' . site_url('designs');
  $siteName = $site['name'] ?? 'Manuelcode';
  $lines[] = '— ' . $siteName;
  return implode("\n", $lines);
}

function design_whatsapp_url(array $d): string
{
  global $site;
  $text = design_share_message($d);
  $wa = trim($site['whatsapp'] ?? '');
  if (preg_match('#wa\.me/(\d+)#', $wa, $m)) {
    return 'https://wa.me/' . $m[1] . '?text=' . rawurlencode($text);
  }
  return 'https://wa.me/?text=' . rawurlencode($text);
}

function cms_upload_design_image(array $file, string $title = ''): ?string
{
  if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    return null;
  }
  $mime = mime_content_type($file['tmp_name']) ?: '';
  $allowed = ['image/jpeg', 'image/png', 'image/webp'];
  if (!in_array($mime, $allowed, true)) {
    return null;
  }
  $dir = dirname(__DIR__) . '/assets/images/gallery';
  if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
  }
  $base = cms_slugify($title !== '' ? $title : 'design');
  $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
  $fname = $base . '-' . time() . '.' . $ext;
  if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $fname)) {
    return null;
  }
  return 'assets/images/gallery/' . $fname;
}

/** Ensure every design has an id (one-time). */
function cms_sync_design_ids(PDO $pdo, array $defaults): void
{
  if (cms_get_setting($pdo, 'designs_ids_v') === '1') {
    return;
  }
  $items = cms_designs_all($pdo, $defaults);
  cms_save_designs_list($pdo, $items);
  cms_set_setting($pdo, 'designs_ids_v', '1');
}
