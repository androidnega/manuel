<?php

function cms_storage_path(string $file = ''): string
{
  $dir = dirname(__DIR__) . '/storage';
  if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
  }
  return $file === '' ? $dir : $dir . '/' . ltrim($file, '/');
}

function cms_db(): PDO
{
  static $pdo = null;
  if ($pdo instanceof PDO) {
    return $pdo;
  }
  $pdo = new PDO('sqlite:' . cms_storage_path('site.sqlite'), null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  cms_migrate($pdo);
  return $pdo;
}

function cms_migrate(PDO $pdo): void
{
  $pdo->exec('CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS pages (
    slug TEXT PRIMARY KEY,
    hero_label TEXT,
    hero_title TEXT,
    hero_desc TEXT,
    body_json TEXT
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS cms_lists (
    list_key TEXT PRIMARY KEY,
    data_json TEXT NOT NULL
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS team_members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    role TEXT NOT NULL,
    bio TEXT,
    photo_path TEXT,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    subject TEXT,
    message TEXT NOT NULL,
    is_read INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS quote_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT,
    organization TEXT,
    project_name TEXT NOT NULL,
    project_type TEXT NOT NULL,
    budget_range TEXT NOT NULL,
    timeline TEXT NOT NULL,
    description TEXT NOT NULL,
    referral TEXT,
    is_read INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS site_settings (
    setting_key TEXT PRIMARY KEY,
    setting_value TEXT
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS page_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_slug TEXT NOT NULL,
    view_date TEXT NOT NULL,
    view_hour INTEGER NOT NULL,
    ip_hash TEXT,
    user_agent TEXT,
    referrer TEXT,
    created_at TEXT NOT NULL
  )');
  $pdo->exec('CREATE INDEX IF NOT EXISTS idx_page_views_date ON page_views(view_date)');
  $pdo->exec('CREATE TABLE IF NOT EXISTS page_views_daily (
    page_slug TEXT NOT NULL,
    view_date TEXT NOT NULL,
    views INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (page_slug, view_date)
  )');

  cms_ensure_admin_user($pdo);
}

function cms_ensure_admin_user(PDO $pdo): void
{
  $hash = password_hash('admin123', PASSWORD_DEFAULT);
  $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
  if ($count === 0) {
    $pdo->prepare('INSERT INTO admin_users (username, password_hash, created_at) VALUES (?, ?, ?)')
      ->execute(['admin', $hash, date('c')]);
    return;
  }
  $ver = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'admin_cred_version'")->fetchColumn();
  if ($ver !== '2') {
    $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE username = ?')->execute([$hash, 'admin']);
    $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
      ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value')
      ->execute(['admin_cred_version', '2']);
  }
}

/** Append missing list items (matched by link or title). */
function cms_sync_list_defaults(PDO $pdo, string $key, array $defaults): void
{
  $current = cms_get_list($pdo, $key, []);
  if ($current === [] && $defaults !== []) {
    cms_save_list($pdo, $key, $defaults);
    return;
  }
  $keys = [];
  foreach ($current as $item) {
    if (!empty($item['link'])) {
      $keys[strtolower($item['link'])] = true;
    }
    if (!empty($item['url'])) {
      $keys[strtolower($item['url'])] = true;
    }
    if (!empty($item['title'])) {
      $keys['t:' . strtolower($item['title'])] = true;
    }
    if (!empty($item['label'])) {
      $keys['l:' . strtolower($item['label'])] = true;
    }
  }
  $merged = $current;
  $changed = false;
  foreach ($defaults as $item) {
    $match = false;
    if (!empty($item['link']) && isset($keys[strtolower($item['link'])])) {
      $match = true;
    }
    if (!empty($item['url']) && isset($keys[strtolower($item['url'])])) {
      $match = true;
    }
    if (!empty($item['title']) && isset($keys['t:' . strtolower($item['title'])])) {
      $match = true;
    }
    if (!empty($item['label']) && isset($keys['l:' . strtolower($item['label'])])) {
      $match = true;
    }
    if (!$match) {
      $merged[] = $item;
      $changed = true;
    }
  }
  if ($changed) {
    cms_save_list($pdo, $key, $merged);
  }
}

function cms_nav_from_list(array $items): array
{
  $nav = [];
  foreach ($items as $item) {
    if (!empty($item['label']) && !empty($item['href'])) {
      $nav[$item['label']] = $item['href'];
    }
  }
  return $nav;
}

function cms_quotes_page_defaults(): array
{
  return [
    'label' => 'Request a quote',
    'title' => 'Get a quote for your project.',
    'desc' => 'Share what you need — website, app, system or design — and receive a clear estimate tailored to your scope and timeline.',
    'body' => [
      'intro_left' => 'Use this form when you want pricing and scope for real work: websites, apps, dashboards, integrations, or design support.',
      'intro_right' => 'Fields marked with * are required. I usually respond within 1–2 business days with questions or a quote outline.',
      'note_1' => 'Typical requests: business websites, school/campus tools, inventory systems, landing pages, UI design.',
      'note_2' => 'Include links, references, or deadlines if you have them — it speeds up the estimate.',
      'note_3' => 'Not sure on budget? Choose “Not sure yet” and describe what success looks like.',
    ],
  ];
}

function cms_sync_quotes_page_copy(PDO $pdo): void
{
  $ver = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'quotes_page_v'")->fetchColumn();
  if ($ver === '3') {
    return;
  }
  $defaults = cms_quotes_page_defaults();
  cms_save_page(
    $pdo,
    'quotes',
    $defaults['label'],
    $defaults['title'],
    $defaults['desc'],
    $defaults['body']
  );
  $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
    ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value')
    ->execute(['quotes_page_v', '3']);
}

function cms_nav_to_list(array $nav): array
{
  $items = [];
  foreach ($nav as $label => $href) {
    $items[] = ['label' => $label, 'href' => $href];
  }
  return $items;
}

function cms_seed_from_globals(PDO $pdo, array $lists): void
{
  $count = (int) $pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn();
  if ($count > 0) {
    return;
  }

  $defaultPages = [
    'home' => ['label' => '', 'title' => 'Building practical software with energy, clarity and care.', 'desc' => 'I’m Manuel — web and software developer in Ghana.', 'body' => [
      'badge' => 'Open for websites, apps, systems & creative work',
      'intro' => 'I’m Manuel — web and software developer in Ghana. Explore work, services, quotes, designs and about on their own pages, or reach out to start a project.',
      'cta_title' => 'Need a website, app or system?',
      'cta_text' => 'Same energy as the poster — let’s make it happen.',
      'clients_label' => 'Live work',
      'clients_title' => 'Projects in production',
      'clients_lead' => 'Tap a card to open the site in a new tab.',
      'pages_label' => 'Site pages',
      'pages_title' => 'Go to a page',
      'pages_lead' => 'Each card opens the matching page on this site.',
    ]],
    'projects' => ['label' => 'Selected work', 'title' => 'Projects and systems I’ve worked on.', 'desc' => 'Practical systems for schools, students, businesses and organizations.', 'body' => []],
    'services' => ['label' => 'What I do', 'title' => 'Services built around real needs.', 'desc' => 'Software systems and creative production for real organizations.', 'body' => [
      'cta_title' => 'Need a custom solution?',
      'cta_text' => 'Websites, campus platforms, inventory, posters, photo or video.',
    ]],
    'quotes' => array_merge(['body' => []], cms_quotes_page_defaults()),
    'designs' => ['label' => 'Design gallery', 'title' => 'Posters, graphics and visual content.', 'desc' => 'Quote graphics, brand posts and campaign visuals.', 'body' => []],
    'about' => ['label' => 'About Manuel', 'title' => 'Software skill with creative experience.', 'desc' => 'A software mind with a creative eye — building for real users in Ghana and beyond.', 'body' => [
      'paragraph1' => 'I am a computer software engineer and multimedia creative focused on practical digital systems with clean interfaces and strong visual presentation.',
      'paragraph2' => 'My work spans campus platforms, document systems, attendance tools, inventory, websites, photography, video and graphic design.',
      'technical' => 'Web systems, UI design, databases, campus and business tools.',
      'creative' => 'Photography, video, motion graphics and poster design.',
      'experience_title' => 'Organizations I’ve worked with',
    ]],
    'contact' => ['label' => 'Contact', 'title' => 'Have a project in mind?', 'desc' => 'Let’s build something clean and useful.', 'body' => [
      'intro_left' => 'Reach out for websites, systems, apps, posters, design, photo or video.',
      'intro_right' => 'Share a few details and I’ll get back to you.',
    ]],
  ];

  $ins = $pdo->prepare('INSERT INTO pages (slug, hero_label, hero_title, hero_desc, body_json) VALUES (?, ?, ?, ?, ?)');
  foreach ($defaultPages as $slug => $p) {
    $ins->execute([$slug, $p['label'], $p['title'], $p['desc'], json_encode($p['body'], JSON_UNESCAPED_UNICODE)]);
  }

  $lins = $pdo->prepare('INSERT INTO cms_lists (list_key, data_json) VALUES (?, ?)');
  foreach ($lists as $key => $data) {
    $lins->execute([$key, json_encode($data, JSON_UNESCAPED_UNICODE)]);
  }
}

function cms_save_list(PDO $pdo, string $key, array $data): void
{
  $stmt = $pdo->prepare('INSERT INTO cms_lists (list_key, data_json) VALUES (?, ?)
    ON CONFLICT(list_key) DO UPDATE SET data_json = excluded.data_json');
  $stmt->execute([$key, json_encode($data, JSON_UNESCAPED_UNICODE)]);
}

function cms_get_list(PDO $pdo, string $key, array $fallback): array
{
  $stmt = $pdo->prepare('SELECT data_json FROM cms_lists WHERE list_key = ?');
  $stmt->execute([$key]);
  $row = $stmt->fetch();
  return cms_json_list($row['data_json'] ?? null, $fallback);
}

function cms_get_page(PDO $pdo, string $slug): ?array
{
  $stmt = $pdo->prepare('SELECT * FROM pages WHERE slug = ?');
  $stmt->execute([$slug]);
  $row = $stmt->fetch();
  if (!$row) {
    return null;
  }
  $row['body'] = cms_json_list($row['body_json'] ?? null, []);
  unset($row['body_json']);
  return $row;
}

function cms_save_page(PDO $pdo, string $slug, string $label, string $title, string $desc, array $body): void
{
  $stmt = $pdo->prepare('INSERT INTO pages (slug, hero_label, hero_title, hero_desc, body_json) VALUES (?, ?, ?, ?, ?)
    ON CONFLICT(slug) DO UPDATE SET hero_label = excluded.hero_label, hero_title = excluded.hero_title,
    hero_desc = excluded.hero_desc, body_json = excluded.body_json');
  $stmt->execute([$slug, $label, $title, $desc, json_encode($body, JSON_UNESCAPED_UNICODE)]);
}

function cms_team_members(PDO $pdo): array
{
  return $pdo->query('SELECT * FROM team_members ORDER BY sort_order ASC, id ASC')->fetchAll();
}

function cms_unread_count(PDO $pdo): int
{
  return (int) $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
}

function cms_unread_quote_requests_count(PDO $pdo): int
{
  return (int) $pdo->query('SELECT COUNT(*) FROM quote_requests WHERE is_read = 0')->fetchColumn();
}

function cms_inbox_unread_count(PDO $pdo): int
{
  return cms_unread_count($pdo) + cms_unread_quote_requests_count($pdo);
}

function cms_json_list(?string $json, array $fallback): array
{
  if ($json === null || $json === '') {
    return $fallback;
  }
  $data = json_decode($json, true);
  return is_array($data) ? $data : $fallback;
}

function cms_get_setting(PDO $pdo, string $key, ?string $default = null): ?string
{
  $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
  $stmt->execute([$key]);
  $val = $stmt->fetchColumn();
  return $val === false ? $default : (string) $val;
}

function cms_set_setting(PDO $pdo, string $key, string $value): void
{
  $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
    ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value')
    ->execute([$key, $value]);
}

function cms_maintenance_defaults(): array
{
  return [
    'enabled' => false,
    'ends_at' => '',
    'title' => "We're updating the site",
    'caption' => 'Fresh improvements are on the way. Thanks for your patience.',
  ];
}

function cms_maintenance_config(PDO $pdo): array
{
  $raw = cms_get_setting($pdo, 'maintenance', '');
  $data = $raw !== '' ? json_decode($raw, true) : [];
  if (!is_array($data)) {
    $data = [];
  }
  $config = array_merge(cms_maintenance_defaults(), $data);
  $config['enabled'] = !empty($config['enabled']);

  if ($config['enabled'] && $config['ends_at'] !== '') {
    $end = strtotime($config['ends_at']);
    if ($end !== false && $end <= time()) {
      $config['enabled'] = false;
      cms_save_maintenance_config($pdo, $config);
    }
  }

  return $config;
}

function cms_save_maintenance_config(PDO $pdo, array $config): void
{
  $payload = array_merge(cms_maintenance_defaults(), $config);
  $payload['enabled'] = !empty($payload['enabled']);
  cms_set_setting($pdo, 'maintenance', json_encode($payload, JSON_UNESCAPED_UNICODE));
}
