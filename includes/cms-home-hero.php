<?php

/** Homepage hero slideshow slides (admin-managed). */

function cms_home_hero_timezone(): DateTimeZone
{
  return new DateTimeZone('Africa/Accra');
}

function cms_home_hero_now(): DateTimeImmutable
{
  return new DateTimeImmutable('now', cms_home_hero_timezone());
}

function cms_home_hero_is_monday(?DateTimeInterface $when = null): bool
{
  $when = $when ?? cms_home_hero_now();
  return (int) $when->format('N') === 1;
}

function cms_home_hero_monday_slide(): array
{
  return [
    'id' => 'slide-monday-active',
    'image' => 'assets/images/manuelcode-monday-active-promo-ghana.jpg',
    'alt' => 'It\'s Monday — Bring me work. Manuelcode is active for websites, apps and software jobs.',
    'sort_order' => 0,
    'published' => true,
    'monday_only' => true,
    'schedule_exclusive' => true,
    'schedule_days' => [1],
  ];
}

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

function cms_home_hero_time_to_minutes(string $hhmm): int
{
  if (!preg_match('/^(\d{1,2}):(\d{2})$/', trim($hhmm), $m)) {
    return 0;
  }
  return min(1439, max(0, (int) $m[1] * 60 + (int) $m[2]));
}

function cms_home_hero_parse_schedule_datetime(string $value): string
{
  $value = trim($value);
  if ($value === '') {
    return '';
  }
  $tz = cms_home_hero_timezone();
  $dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value, $tz);
  if (!$dt) {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value, $tz);
  }
  if (!$dt) {
    return '';
  }
  return $dt->format('Y-m-d H:i:s');
}

function cms_home_hero_format_schedule_datetime(string $stored): string
{
  $stored = trim($stored);
  if ($stored === '') {
    return '';
  }
  $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $stored, cms_home_hero_timezone());
  return $dt ? $dt->format('Y-m-d\TH:i') : '';
}

function cms_home_hero_normalize_schedule(array $item): array
{
  $days = $item['schedule_days'] ?? [];
  if (!is_array($days)) {
    $days = [];
  }
  $days = array_values(array_unique(array_filter(array_map('intval', $days), static fn($d) => $d >= 1 && $d <= 7)));
  sort($days);

  $item['schedule_days'] = $days;
  $item['schedule_from'] = trim($item['schedule_from'] ?? '');
  $item['schedule_until'] = trim($item['schedule_until'] ?? '');
  $item['schedule_time_from'] = trim($item['schedule_time_from'] ?? '');
  $item['schedule_time_until'] = trim($item['schedule_time_until'] ?? '');
  $item['schedule_exclusive'] = !empty($item['schedule_exclusive']);

  if (!empty($item['monday_only'])) {
    $item['schedule_exclusive'] = true;
    if ($days === []) {
      $item['schedule_days'] = [1];
    }
  }

  return $item;
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
    $item['monday_only'] = !empty($item['monday_only']);
    $item['sort_order'] = (int) ($item['sort_order'] ?? 0);
    $item['alt'] = trim($item['alt'] ?? '');
    $out[] = cms_home_hero_normalize_schedule($item);
  }
  usort($out, static function ($a, $b) {
    return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
  });
  return $out;
}

function cms_home_hero_slide_is_exclusive(array $slide): bool
{
  return !empty($slide['monday_only']) || !empty($slide['schedule_exclusive']);
}

function cms_home_hero_slide_is_active(array $slide, ?DateTimeImmutable $when = null): bool
{
  if (empty($slide['published'])) {
    return false;
  }

  $when = $when ?? cms_home_hero_now();

  if (!empty($slide['monday_only'])) {
    return (int) $when->format('N') === 1;
  }

  $from = trim($slide['schedule_from'] ?? '');
  if ($from !== '') {
    $start = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $from, cms_home_hero_timezone());
    if ($start && $when < $start) {
      return false;
    }
  }

  $until = trim($slide['schedule_until'] ?? '');
  if ($until !== '') {
    $end = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $until, cms_home_hero_timezone());
    if ($end && $when > $end) {
      return false;
    }
  }

  $days = $slide['schedule_days'] ?? [];
  if (is_array($days) && $days !== []) {
    if (!in_array((int) $when->format('N'), array_map('intval', $days), true)) {
      return false;
    }
  }

  $tFrom = trim($slide['schedule_time_from'] ?? '');
  $tUntil = trim($slide['schedule_time_until'] ?? '');
  if ($tFrom !== '' || $tUntil !== '') {
    $nowMin = (int) $when->format('H') * 60 + (int) $when->format('i');
    $fromMin = $tFrom !== '' ? cms_home_hero_time_to_minutes($tFrom) : 0;
    $untilMin = $tUntil !== '' ? cms_home_hero_time_to_minutes($tUntil) : 1439;
    if ($fromMin <= $untilMin) {
      if ($nowMin < $fromMin || $nowMin > $untilMin) {
        return false;
      }
    } elseif ($nowMin > $untilMin && $nowMin < $fromMin) {
      return false;
    }
  }

  return true;
}

function cms_home_hero_schedule_summary(array $slide): string
{
  if (!empty($slide['monday_only'])) {
    return 'Mondays only · replaces slideshow';
  }

  $parts = [];
  $from = trim($slide['schedule_from'] ?? '');
  $until = trim($slide['schedule_until'] ?? '');
  if ($from !== '' || $until !== '') {
    $range = [];
    if ($from !== '') {
      $range[] = 'from ' . substr($from, 0, 16);
    }
    if ($until !== '') {
      $range[] = 'until ' . substr($until, 0, 16);
    }
    $parts[] = implode(' ', $range);
  }

  $days = $slide['schedule_days'] ?? [];
  if (is_array($days) && $days !== []) {
    $names = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $labels = [];
    foreach ($days as $d) {
      $labels[] = $names[(int) $d] ?? '';
    }
    $parts[] = implode(', ', array_filter($labels));
  }

  $tf = trim($slide['schedule_time_from'] ?? '');
  $tu = trim($slide['schedule_time_until'] ?? '');
  if ($tf !== '' || $tu !== '') {
    $parts[] = trim(($tf ?: '00:00') . '–' . ($tu ?: '23:59') . ' daily');
  }

  if (!empty($slide['schedule_exclusive'])) {
    $parts[] = 'exclusive when active';
  }

  return $parts !== [] ? implode(' · ', $parts) : 'Always (no schedule)';
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

function cms_home_hero_public(PDO $pdo, ?DateTimeImmutable $when = null): array
{
  $when = $when ?? cms_home_hero_now();
  $active = [];
  foreach (cms_home_hero_slides($pdo) as $slide) {
    if (cms_home_hero_slide_is_active($slide, $when)) {
      $active[] = $slide;
    }
  }

  $exclusive = array_values(array_filter($active, 'cms_home_hero_slide_is_exclusive'));
  if ($exclusive !== []) {
    return $exclusive;
  }

  return $active;
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

function cms_home_hero_build_slide_from_post(array $post, ?array $existing = null): array
{
  $days = [];
  for ($d = 1; $d <= 7; $d++) {
    if (!empty($post['schedule_day_' . $d])) {
      $days[] = $d;
    }
  }

  $mondayOnly = !empty($post['monday_only']);
  $item = [
    'id' => trim($post['id'] ?? '') !== '' ? trim($post['id']) : bin2hex(random_bytes(8)),
    'image' => trim($post['image'] ?? ''),
    'alt' => trim($post['alt'] ?? ''),
    'sort_order' => (int) ($post['sort_order'] ?? 0),
    'published' => !empty($post['published']),
    'monday_only' => $mondayOnly,
    'schedule_from' => cms_home_hero_parse_schedule_datetime($post['schedule_from'] ?? ''),
    'schedule_until' => cms_home_hero_parse_schedule_datetime($post['schedule_until'] ?? ''),
    'schedule_days' => $days,
    'schedule_time_from' => trim($post['schedule_time_from'] ?? ''),
    'schedule_time_until' => trim($post['schedule_time_until'] ?? ''),
    'schedule_exclusive' => !empty($post['schedule_exclusive']) || $mondayOnly,
  ];

  if ($existing) {
    if ($item['image'] === '') {
      $item['image'] = trim($existing['image'] ?? '');
    }
    if ($item['id'] === '') {
      $item['id'] = $existing['id'];
    }
  }

  return cms_home_hero_normalize_schedule($item);
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

function cms_sync_home_hero_monday_slide(PDO $pdo): void
{
  if (cms_get_setting($pdo, 'home_hero_monday_v') === '1') {
    return;
  }
  $all = cms_home_hero_slides($pdo);
  $found = false;
  foreach ($all as $slide) {
    if (($slide['id'] ?? '') === 'slide-monday-active') {
      $found = true;
      break;
    }
  }
  if (!$found) {
    $all[] = cms_home_hero_monday_slide();
    cms_save_home_hero_slides($pdo, $all);
  }
  cms_set_setting($pdo, 'home_hero_monday_v', '1');
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
