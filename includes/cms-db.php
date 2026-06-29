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
    role TEXT NOT NULL DEFAULT \'super\',
    class_group TEXT,
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
  $pdo->exec('CREATE TABLE IF NOT EXISTS industrial_attachments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    index_number TEXT NOT NULL,
    contact TEXT NOT NULL,
    company_name TEXT NOT NULL,
    location TEXT NOT NULL,
    official_position TEXT NOT NULL,
    class_group TEXT NOT NULL,
    is_read INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL
  )');
  $pdo->exec('CREATE INDEX IF NOT EXISTS idx_industrial_attachments_group ON industrial_attachments(class_group)');
  $pdo->exec('CREATE INDEX IF NOT EXISTS idx_industrial_attachments_created ON industrial_attachments(created_at)');
  $pdo->exec('CREATE TABLE IF NOT EXISTS page_views_daily (
    page_slug TEXT NOT NULL,
    view_date TEXT NOT NULL,
    views INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (page_slug, view_date)
  )');

  cms_ensure_admin_user($pdo);

  cms_admin_users_migrate($pdo);
  cms_attachment_migrate_companies_json($pdo);

  require_once __DIR__ . '/cms-content.php';
  cms_content_migrate($pdo);
}

function cms_ensure_admin_user(PDO $pdo): void
{
  $hash = password_hash('admin123', PASSWORD_DEFAULT);
  $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
  if ($count === 0) {
    $pdo->prepare('INSERT INTO admin_users (username, password_hash, role, created_at) VALUES (?, ?, ?, ?)')
      ->execute(['admin', $hash, 'super', date('c')]);
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
    if (!empty($item['href'])) {
      $keys[strtolower($item['href'])] = true;
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
    if (!empty($item['href']) && isset($keys[strtolower($item['href'])])) {
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
      'intro' => 'I’m Manuel — I build websites and apps from Ghana. Browse projects, services, quotes and designs, or reach out when you’re ready to start something.',
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

function cms_unread_attachments_count(PDO $pdo, ?string $classGroup = null): int
{
  if ($classGroup !== null && $classGroup !== '') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM industrial_attachments WHERE is_read = 0 AND class_group = ?');
    $stmt->execute([$classGroup]);
    return (int) $stmt->fetchColumn();
  }
  return (int) $pdo->query('SELECT COUNT(*) FROM industrial_attachments WHERE is_read = 0')->fetchColumn();
}

function cms_admin_users_migrate(PDO $pdo): void
{
  $cols = $pdo->query('PRAGMA table_info(admin_users)')->fetchAll(PDO::FETCH_ASSOC);
  $names = array_column($cols, 'name');
  if (!in_array('role', $names, true)) {
    $pdo->exec("ALTER TABLE admin_users ADD COLUMN role TEXT NOT NULL DEFAULT 'super'");
  }
  if (!in_array('class_group', $names, true)) {
    $pdo->exec('ALTER TABLE admin_users ADD COLUMN class_group TEXT');
  }
  $pdo->exec("UPDATE admin_users SET role = 'super' WHERE role IS NULL OR role = ''");
}

function cms_class_admin_users(PDO $pdo): array
{
  $stmt = $pdo->query("SELECT id, username, class_group, created_at FROM admin_users WHERE role = 'class' ORDER BY username ASC");
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function cms_create_class_admin_user(PDO $pdo, string $username, string $password, string $classGroup): ?string
{
  $username = strtolower(trim($username));
  $classGroup = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($classGroup)));
  $groups = cms_attachment_class_groups($pdo);
  if ($username === '') {
    return 'Username is required.';
  }
  if (!preg_match('/^[a-z0-9._-]{3,32}$/', $username)) {
    return 'Username must be 3–32 characters (letters, numbers, dot, dash, underscore).';
  }
  if (strlen($password) < 8) {
    return 'Password must be at least 8 characters.';
  }
  if ($classGroup === '' || !isset($groups[$classGroup])) {
    return 'Select a valid class group.';
  }
  $check = $pdo->prepare('SELECT COUNT(*) FROM admin_users WHERE username = ?');
  $check->execute([$username]);
  if ((int) $check->fetchColumn() > 0) {
    return 'That username is already taken.';
  }
  $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash, role, class_group, created_at) VALUES (?, ?, ?, ?, ?)');
  $stmt->execute([
    $username,
    password_hash($password, PASSWORD_DEFAULT),
    'class',
    $classGroup,
    date('c'),
  ]);
  return null;
}

function cms_delete_class_admin_user(PDO $pdo, int $id, int $currentUserId): ?string
{
  if ($id <= 0) {
    return 'Invalid user.';
  }
  if ($id === $currentUserId) {
    return 'You cannot delete your own account while signed in.';
  }
  $stmt = $pdo->prepare('SELECT id, role FROM admin_users WHERE id = ?');
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row || ($row['role'] ?? '') !== 'class') {
    return 'Class account not found.';
  }
  $pdo->prepare('DELETE FROM admin_users WHERE id = ?')->execute([$id]);
  return null;
}

function cms_reset_class_admin_password(PDO $pdo, int $id, string $password): ?string
{
  if (strlen($password) < 8) {
    return 'Password must be at least 8 characters.';
  }
  $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE id = ? AND role = 'class'");
  $stmt->execute([$id]);
  if (!$stmt->fetch()) {
    return 'Class account not found.';
  }
  $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([
    password_hash($password, PASSWORD_DEFAULT),
    $id,
  ]);
  return null;
}

function cms_attachment_default_groups(): array
{
  return [
    'group_a' => ['label' => 'BTECH IT GROUP A', 'level' => 'L-200'],
    'group_e' => ['label' => 'BTECH I.T GROUP E', 'level' => 'L-200'],
  ];
}

function cms_attachment_normalize_group_entry(mixed $value): ?array
{
  if (is_array($value)) {
    $label = trim((string) ($value['label'] ?? ''));
    $level = trim((string) ($value['level'] ?? ''));
  } else {
    $label = trim((string) $value);
    $level = '';
  }
  if ($label === '') {
    return null;
  }
  return [
    'label' => $label,
    'level' => $level !== '' ? strtoupper($level) : '',
  ];
}

function cms_attachment_normalize_groups(mixed $groups): array
{
  $defaults = cms_attachment_default_groups();
  if (!is_array($groups) || $groups === []) {
    return $defaults;
  }
  $out = [];
  foreach ($groups as $key => $value) {
    $key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $key));
    if ($key === '') {
      continue;
    }
    $entry = cms_attachment_normalize_group_entry($value);
    if ($entry !== null) {
      $out[$key] = $entry;
    }
  }
  return $out !== [] ? $out : $defaults;
}

function cms_attachment_groups(?PDO $pdo = null): array
{
  if ($pdo === null) {
    $pdo = cms_db();
  }
  $config = cms_attachment_registration_config($pdo);
  return cms_attachment_normalize_groups($config['groups'] ?? []);
}

function cms_attachment_class_groups(?PDO $pdo = null): array
{
  $out = [];
  foreach (cms_attachment_groups($pdo) as $key => $group) {
    $out[$key] = $group['label'];
  }
  return $out;
}

function cms_attachment_group_display(array $group): string
{
  $label = strtoupper($group['label'] ?? '');
  $level = trim((string) ($group['level'] ?? ''));
  if ($level !== '') {
    return $label . ' — ' . strtoupper($level);
  }
  return $label;
}

function cms_attachment_group_by_key(?PDO $pdo, string $groupKey): ?array
{
  $groups = cms_attachment_groups($pdo);
  return $groups[$groupKey] ?? null;
}

function cms_attachment_group_key(string $label, array $existing): string
{
  $base = strtolower(preg_replace('/[^a-z0-9]+/', '_', $label));
  $base = trim($base, '_');
  if ($base === '') {
    $base = 'group';
  }
  if (!str_starts_with($base, 'group')) {
    $base = 'group_' . $base;
  }
  $key = $base;
  $n = 2;
  while (isset($existing[$key])) {
    $key = $base . '_' . $n;
    $n++;
  }
  return $key;
}

function cms_attachment_group_count(PDO $pdo, string $groupKey): int
{
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM industrial_attachments WHERE class_group = ?');
  $stmt->execute([$groupKey]);
  return (int) $stmt->fetchColumn();
}

function cms_attachment_max_companies(): int
{
  return 3;
}

function cms_attachment_migrate_companies_json(PDO $pdo): void
{
  $cols = $pdo->query('PRAGMA table_info(industrial_attachments)')->fetchAll(PDO::FETCH_ASSOC);
  $hasJson = false;
  foreach ($cols as $col) {
    if (($col['name'] ?? '') === 'companies_json') {
      $hasJson = true;
      break;
    }
  }
  if (!$hasJson) {
    $pdo->exec('ALTER TABLE industrial_attachments ADD COLUMN companies_json TEXT');
  }

  $stmt = $pdo->query('SELECT id, company_name, location, official_position, companies_json FROM industrial_attachments');
  $update = $pdo->prepare('UPDATE industrial_attachments SET companies_json = ? WHERE id = ?');
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (($row['companies_json'] ?? '') !== '') {
      continue;
    }
    $update->execute([
      cms_attachment_companies_encode([[
        'name' => $row['company_name'] ?? '',
        'location' => $row['location'] ?? '',
        'official_position' => $row['official_position'] ?? '',
      ]]),
      (int) $row['id'],
    ]);
  }
}

function cms_attachment_normalize_company(mixed $company): ?array
{
  if (!is_array($company)) {
    return null;
  }
  $name = cms_form_upper($company['name'] ?? '');
  $location = cms_form_upper($company['location'] ?? '');
  $official = cms_form_upper($company['official_position'] ?? $company['official'] ?? '');
  if ($name === '' && $location === '' && $official === '') {
    return null;
  }
  return [
    'name' => $name,
    'location' => $location,
    'official_position' => $official,
  ];
}

function cms_attachment_normalize_companies_list(mixed $companies): array
{
  if (!is_array($companies)) {
    return [];
  }
  $out = [];
  foreach ($companies as $company) {
    $entry = cms_attachment_normalize_company($company);
    if ($entry === null || $entry['name'] === '') {
      continue;
    }
    $out[] = $entry;
    if (count($out) >= cms_attachment_max_companies()) {
      break;
    }
  }
  return $out;
}

function cms_attachment_companies_encode(array $companies): string
{
  return json_encode(cms_attachment_normalize_companies_list($companies), JSON_UNESCAPED_UNICODE) ?: '[]';
}

function cms_attachment_companies_from_row(array $row): array
{
  $raw = trim((string) ($row['companies_json'] ?? ''));
  if ($raw !== '') {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
      $companies = cms_attachment_normalize_companies_list($decoded);
      if ($companies !== []) {
        return $companies;
      }
    }
  }

  return cms_attachment_normalize_companies_list([[
    'name' => $row['company_name'] ?? '',
    'location' => $row['location'] ?? '',
    'official_position' => $row['official_position'] ?? '',
  ]]);
}

function cms_attachment_primary_company_fields(array $companies): array
{
  $first = $companies[0] ?? ['name' => '', 'location' => '', 'official_position' => ''];
  return [
    'company_name' => $first['name'],
    'location' => $first['location'],
    'official_position' => $first['official_position'],
  ];
}

function cms_attachment_find_by_index(PDO $pdo, string $indexNumber, string $classGroup): ?array
{
  $indexNumber = trim($indexNumber);
  $classGroup = trim($classGroup);
  if ($indexNumber === '' || $classGroup === '') {
    return null;
  }
  $stmt = $pdo->prepare('SELECT * FROM industrial_attachments WHERE UPPER(index_number) = UPPER(?) AND class_group = ? LIMIT 1');
  $stmt->execute([$indexNumber, $classGroup]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

function cms_attachment_get_by_id(PDO $pdo, int $id): ?array
{
  if ($id <= 0) {
    return null;
  }
  $stmt = $pdo->prepare('SELECT * FROM industrial_attachments WHERE id = ? LIMIT 1');
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

function cms_attachment_validate_companies(array $companies, bool $requireAtLeastOne = true): ?string
{
  if ($requireAtLeastOne && $companies === []) {
    return 'Add at least one company (maximum ' . cms_attachment_max_companies() . ').';
  }
  if (count($companies) > cms_attachment_max_companies()) {
    return 'You can register up to ' . cms_attachment_max_companies() . ' companies only.';
  }
  foreach ($companies as $i => $company) {
    $num = $i + 1;
    if ($company['name'] === '') {
      return 'Company ' . $num . ' name is required.';
    }
    if ($company['location'] === '' || $company['official_position'] === '') {
      return 'Complete location and official position for company ' . $num . '.';
    }
  }
  return null;
}

function cms_attachment_registration_defaults(): array
{
  return [
    'closes_at' => '',
    'closed_message' => 'Registration for industrial attachment is now closed. Contact your class representative if you still need help.',
    'groups' => cms_attachment_default_groups(),
  ];
}

function cms_attachment_registration_config(PDO $pdo): array
{
  $raw = cms_get_setting($pdo, 'attachment_registration', '');
  $data = $raw !== '' ? json_decode($raw, true) : [];
  if (!is_array($data)) {
    $data = [];
  }
  return array_merge(cms_attachment_registration_defaults(), $data);
}

function cms_attachment_registration_is_open(PDO $pdo): bool
{
  $config = cms_attachment_registration_config($pdo);
  $closesAt = trim($config['closes_at'] ?? '');
  if ($closesAt === '') {
    return true;
  }
  $end = strtotime($closesAt);
  if ($end === false) {
    return true;
  }
  return time() < $end;
}

function cms_save_attachment_registration_config(PDO $pdo, array $config): void
{
  $current = cms_attachment_registration_config($pdo);
  $payload = array_merge(cms_attachment_registration_defaults(), $current, $config);
  if (isset($payload['groups']) && is_array($payload['groups'])) {
    $payload['groups'] = cms_attachment_normalize_groups($payload['groups']);
  }
  cms_set_setting($pdo, 'attachment_registration', json_encode($payload, JSON_UNESCAPED_UNICODE));
}

function cms_inbox_unread_count(PDO $pdo): int
{
  return cms_unread_count($pdo) + cms_unread_quote_requests_count($pdo) + cms_unread_attachments_count($pdo);
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

/** One-time path fixes after image compression renames. */
function cms_sync_image_paths(PDO $pdo): void
{
  if (cms_get_setting($pdo, 'image_paths_v') === '1') {
    return;
  }

  $map = [
    'assets/images/dark-logo.png' => 'assets/images/dark-logo.webp',
    'assets/images/favicon.png' => 'assets/images/favicon.webp',
    'assets/images/brand-guide.png' => 'assets/images/brand-guide.jpg',
    'assets/images/quote-poster-original.png' => 'assets/images/quote-poster-original.jpg',
    'assets/images/manuel-portrait.png' => 'assets/images/manuel-portrait.jpg',
  ];

  $designs = cms_get_list($pdo, 'designs', []);
  $changed = false;
  foreach ($designs as &$item) {
    if (!empty($item['image']) && isset($map[$item['image']])) {
      $item['image'] = $map[$item['image']];
      $changed = true;
    }
  }
  unset($item);
  if ($changed) {
    cms_save_list($pdo, 'designs', $designs);
  }

  cms_set_setting($pdo, 'image_paths_v', '1');
}

/** SEO-friendly image filenames (v2). */
function cms_sync_seo_image_paths(PDO $pdo): void
{
  if (cms_get_setting($pdo, 'image_paths_seo_v') === '2') {
    return;
  }

  require_once __DIR__ . '/seo.php';
  $map = seo_image_paths_map();

  $designs = cms_get_list($pdo, 'designs', []);
  $changed = false;
  foreach ($designs as &$item) {
    if (!empty($item['image']) && isset($map[$item['image']])) {
      $item['image'] = $map[$item['image']];
      $changed = true;
    }
  }
  unset($item);
  if ($changed) {
    cms_save_list($pdo, 'designs', $designs);
  }

  $siteRow = cms_get_list($pdo, 'site', []);
  if (is_array($siteRow)) {
    $siteChanged = false;
    foreach (['logo', 'logo_dark', 'favicon', 'guide', 'hero_promo', 'og_default'] as $key) {
      if (!empty($siteRow[$key]) && isset($map[$siteRow[$key]])) {
        $siteRow[$key] = $map[$siteRow[$key]];
        $siteChanged = true;
      }
    }
    if ($siteChanged) {
      cms_save_list($pdo, 'site', $siteRow);
    }
  }

  $modalRaw = cms_get_setting($pdo, 'newsletter_modal', '');
  if ($modalRaw !== '') {
    $modal = json_decode($modalRaw, true);
    if (is_array($modal) && !empty($modal['image']) && isset($map[$modal['image']])) {
      $modal['image'] = $map[$modal['image']];
      cms_set_setting($pdo, 'newsletter_modal', json_encode($modal, JSON_UNESCAPED_UNICODE));
    }
  }

  cms_set_setting($pdo, 'image_paths_seo_v', '2');
}
