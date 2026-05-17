<?php

/** Homepage hero slideshow slides (admin-managed). */

function cms_home_hero_defaults(): array
{
  return [
    [
      'id' => 'slide-bring-me-work',
      'image' => 'assets/images/manuelcode-bring-me-work-promo-software-ghana.png',
      'alt' => 'Bring Me Work — Manuelcode promo: web development, mobile apps, custom software.',
      'sort_order' => 0,
      'published' => true,
    ],
    [
      'id' => 'slide-build-ideas',
      'image' => 'assets/images/manuelcode-build-your-ideas-online-hero-ghana.jpg',
      'alt' => 'Build your ideas online — Manuelcode web developer and software engineer in Ghana.',
      'sort_order' => 1,
      'published' => true,
    ],
  ];
}

function cms_home_hero_normalize(array $items): array
{
  $out = [];
  foreach ($items as $item) {
    if (!is_array($item) || empty($item['image'])) {
      continue;
    }
    if (empty($item['id'])) {
      $item['id'] = bin2hex(random_bytes(8));
    }
    $item['published'] = !empty($item['published']);
    $item['sort_order'] = (int) ($item['sort_order'] ?? 0);
    $item['alt'] = trim($item['alt'] ?? '');
    $out[] = $item;
  }
  usort($out, static function ($a, $b) {
    return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
  });
  return $out;
}

function cms_home_hero_slides(PDO $pdo): array
{
  $raw = cms_get_setting($pdo, 'home_hero_slides', '');
  $data = $raw !== '' ? json_decode($raw, true) : [];
  if (!is_array($data) || $data === []) {
    return cms_home_hero_normalize(cms_home_hero_defaults());
  }
  return cms_home_hero_normalize($data);
}

function cms_home_hero_public(PDO $pdo): array
{
  return array_values(array_filter(cms_home_hero_slides($pdo), static fn($s) => !empty($s['published'])));
}

function cms_save_home_hero_slides(PDO $pdo, array $slides): void
{
  cms_set_setting($pdo, 'home_hero_slides', json_encode(cms_home_hero_normalize($slides), JSON_UNESCAPED_UNICODE));
}

function cms_home_hero_by_id(array $slides, string $id): ?array
{
  foreach ($slides as $slide) {
    if (($slide['id'] ?? '') === $id) {
      return $slide;
    }
  }
  return null;
}

function cms_sync_home_hero_slides(PDO $pdo): void
{
  if (cms_get_setting($pdo, 'home_hero_v') === '1') {
    return;
  }
  cms_save_home_hero_slides($pdo, cms_home_hero_defaults());
  cms_set_setting($pdo, 'home_hero_v', '1');
  if (cms_get_setting($pdo, 'home_hero_interval', '') === '') {
    cms_set_setting($pdo, 'home_hero_interval', '180000');
  }
}

function cms_home_hero_interval_ms(PDO $pdo): int
{
  $ms = (int) cms_get_setting($pdo, 'home_hero_interval', '180000');
  return $ms >= 3000 ? $ms : 180000;
}

function cms_upload_home_hero_image(array $file, string $title = ''): ?string
{
  if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    return null;
  }
  $mime = mime_content_type($file['tmp_name']) ?: '';
  $allowed = ['image/jpeg', 'image/png', 'image/webp'];
  if (!in_array($mime, $allowed, true)) {
    return null;
  }
  $dir = dirname(__DIR__) . '/assets/images/home-hero';
  if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
  }
  $base = cms_slugify($title !== '' ? $title : 'home-hero');
  $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
  $fname = $base . '-' . time() . '.' . $ext;
  if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $fname)) {
    return null;
  }
  return 'assets/images/home-hero/' . $fname;
}
