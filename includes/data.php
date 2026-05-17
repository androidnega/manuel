<?php
if (is_file(__DIR__ . '/site-config.php')) {
  require_once __DIR__ . '/site-config.php';
}

require_once __DIR__ . '/icon.php';

$site = [
  'name' => 'Manuelcode',
  'title' => 'Manuel Kwofie',
  'tagline' => 'Software • Design • Media',
  'email' => 'kwofiee3@gmail.com',
  'phone' => '0541069241',
  'website' => 'manuelcode.info',
  'whatsapp' => 'https://wa.me/233541069241',
];
$brand = [
  'logo' => 'assets/images/manuelcode-logo-main.png',
  'logo_dark' => 'assets/images/manuelcode-logo-dark.webp',
  'favicon' => 'assets/images/manuelcode-favicon.webp',
  'guide' => 'assets/images/manuelcode-brand-identity-design-guide.jpg',
  'hero_promo' => 'assets/images/manuelcode-bring-me-work-promo-software-ghana.png',
  'og_default' => 'assets/images/manuelcode-favicon.webp',
];
/** Primary header navigation (kept minimal). */
$headerNav = [
  'Home' => 'index.php',
  'Projects' => 'projects.php',
  'Services' => 'services.php',
  'Contact' => 'contact.php',
];

/** Footer links — secondary pages included. */
$footerNav = [
  'Projects' => 'projects.php',
  'Services' => 'services.php',
  'News' => 'news.php',
  'Quotes' => 'quotes.php',
  'Designs' => 'designs.php',
  'About' => 'about.php',
  'Contact' => 'contact.php',
];

/** Homepage cards — titles match each real page (see pageTitle / hero on each .php file). */
$homePages = [
  [
    'title' => 'Projects',
    'desc' => 'Projects and systems I’ve worked on — campus platforms, apps and live links.',
    'href' => 'projects.php',
    'icon' => 'layers',
    'color' => 'deep',
    'cta' => 'View projects',
  ],
  [
    'title' => 'Services',
    'desc' => 'Services built around real needs — websites, systems, UI and business tools.',
    'href' => 'services.php',
    'icon' => 'laptop',
    'color' => 'blue',
    'cta' => 'View services',
  ],
  [
    'title' => 'Get a quote',
    'desc' => 'Request a project quote — scope, budget and timeline for websites, apps, or systems.',
    'href' => 'quotes.php',
    'icon' => 'quote',
    'color' => 'mint',
    'cta' => 'Request quote',
  ],
  [
    'title' => 'Designs',
    'desc' => 'Posters, graphics and visual content — design gallery and brand work.',
    'href' => 'designs.php',
    'icon' => 'pen',
    'color' => 'amber',
    'cta' => 'View designs',
  ],
  [
    'title' => 'About',
    'desc' => 'Software skill with creative experience — background, skills and CV.',
    'href' => 'about.php',
    'icon' => 'user',
    'color' => 'deep',
    'cta' => 'About Manuel',
  ],
  [
    'title' => 'Contact',
    'desc' => 'Have a project in mind? Reach out for websites, apps, systems or design.',
    'href' => 'contact.php',
    'icon' => 'mail',
    'color' => 'mint',
    'cta' => 'Get in touch',
  ],
];
$clientLogos = [
  ['label' => 'Kuukuacares', 'url' => 'https://kuukuacares.com', 'icon' => 'user', 'tag' => 'MP Ahanta West'],
  ['label' => 'Go Ahanta', 'url' => 'https://goahanta.org', 'icon' => 'globe', 'tag' => 'Ahanta tourism'],
  ['label' => 'KTI', 'url' => 'https://kikamtech.org/', 'icon' => 'layers', 'tag' => 'Kikam Technical Institute'],
  ['label' => 'Quizsnap', 'url' => 'https://quiz.neckpressing.com', 'icon' => 'shield', 'tag' => 'Campus quizzes'],
  ['label' => 'Documento', 'url' => 'https://documento.neckpressing.com', 'icon' => 'file-text', 'tag' => 'Student docs'],
  ['label' => 'At-enda', 'url' => 'https://at-enda.manuelcode.info', 'icon' => 'clock', 'tag' => 'Attendance'],
  ['label' => 'SellApp', 'url' => 'https://sellapp.store', 'icon' => 'smartphone', 'tag' => 'Phone sales'],
  ['label' => 'GNAAS', 'url' => 'https://gnaas.org', 'icon' => 'globe', 'tag' => 'Organization web'],
];
$services = [
  ['icon' => 'laptop', 'color' => 'blue', 'title' => 'Web Development', 'desc' => 'Business websites, dashboards, school systems, portals and admin platforms.'],
  ['icon' => 'smartphone', 'color' => 'mint', 'title' => 'Mobile Apps', 'desc' => 'Clean mobile-first tools for students, businesses and organizations.'],
  ['icon' => 'pen', 'color' => 'amber', 'title' => 'Graphic Design', 'desc' => 'Posters, quote designs, campaign visuals, branding and social media graphics.'],
  ['icon' => 'camera', 'color' => 'deep', 'title' => 'Photo & Video', 'desc' => 'Photography, retouching, editing, motion graphics and content production.'],
  ['icon' => 'database', 'color' => 'blue', 'title' => 'Business Systems', 'desc' => 'Inventory, records, document workflows and practical software tools for teams.'],
  ['icon' => 'sparkles', 'color' => 'mint', 'title' => 'UI Design', 'desc' => 'Modern layouts, product screens, landing pages and clean dashboard concepts.'],
];
$projects = [
  ['icon' => 'user', 'category' => 'Government & Public', 'title' => 'Kuukuacares.com', 'desc' => 'Official website for the Member of Parliament for Ahanta West — constituency news, initiatives and contact.', 'tags' => ['MP', 'Ahanta West', 'Official'], 'link' => 'https://kuukuacares.com', 'featured' => true],
  ['icon' => 'globe', 'category' => 'Tourism', 'title' => 'Go Ahanta', 'desc' => 'goahanta.org — the destination site for Ahanta: culture, places to visit and regional tourism.', 'tags' => ['Tourism', 'Ahanta', 'Ghana'], 'link' => 'https://goahanta.org', 'featured' => false],
  ['icon' => 'layers', 'category' => 'Education', 'title' => 'Kikam Technical Institute', 'desc' => 'Official website for KTI (Kikam Technical Institute) — programs, campus life and admissions since 1963.', 'tags' => ['TVET', 'Western Region', 'School'], 'link' => 'https://kikamtech.org/', 'featured' => false],
  ['icon' => 'shield', 'category' => 'Campus Assessment', 'title' => 'Quizsnap', 'desc' => 'Online proctored quiz platform for campus assessments and controlled sessions.', 'tags' => ['Exam System', 'Proctoring'], 'link' => 'https://quiz.neckpressing.com', 'featured' => false],
  ['icon' => 'file-text', 'category' => 'Documentation', 'title' => 'Documento', 'desc' => 'Document management and project support for students.', 'tags' => [], 'link' => 'https://documento.neckpressing.com', 'featured' => false],
  ['icon' => 'clock', 'category' => 'Attendance', 'title' => 'At-enda', 'desc' => 'Attendance marking platform for campus sessions.', 'tags' => [], 'link' => 'https://at-enda.manuelcode.info', 'featured' => false],
  ['icon' => 'smartphone', 'category' => 'Inventory', 'title' => 'SellApp', 'desc' => 'Phone sales, swapping and stock management.', 'tags' => [], 'link' => 'https://sellapp.store', 'featured' => false],
  ['icon' => 'globe', 'category' => 'Website', 'title' => 'GNAAS Website', 'desc' => 'Organization website development and management.', 'tags' => [], 'link' => 'https://gnaas.org', 'featured' => false],
  ['icon' => 'code', 'category' => 'Portfolio', 'title' => 'Manuelcode.info', 'desc' => 'Personal platform for software projects, quotes, designs and creative works.', 'tags' => [], 'link' => 'https://manuelcode.info', 'featured' => false],
];
/** Legacy CMS list key (unused on public site; quote requests use the form). */
$quotes = [];
$designs = [
  [
    'title' => 'Leadership Poster',
    'type' => 'Quote design',
    'image' => 'assets/images/manuelcode-leadership-quote-poster-design-ghana.jpg',
    'alt' => 'Manuelcode leadership quote poster graphic design Ghana',
  ],
  [
    'title' => 'Bring Me Work',
    'type' => 'Promo graphic',
    'image' => 'assets/images/manuelcode-bring-me-work-promo-software-ghana.png',
    'alt' => 'Manuelcode Bring Me Work promo — web development Ghana',
  ],
  [
    'title' => 'Portrait Feature',
    'type' => 'Personal brand',
    'image' => 'assets/images/manuel-kwofie-software-engineer-portrait-ghana.jpg',
    'alt' => 'Manuel Kwofie software engineer portrait Ghana',
  ],
  [
    'title' => 'Brand Identity',
    'type' => 'Logo & guidelines',
    'image' => 'assets/images/manuelcode-brand-identity-design-guide.jpg',
    'alt' => 'Manuelcode brand identity design guide',
  ],
  ['title' => 'Campaign Concept', 'type' => 'Bold identity', 'variant' => 'campaign'],
  ['title' => 'System Interface', 'type' => 'Dashboard layouts', 'variant' => 'ui'],
  ['title' => 'Brand Post', 'type' => 'Personal identity', 'variant' => 'brand'],
];
$companies = [
  ['name' => 'GNAAS', 'role' => 'Web Developer & Manager'],
  ['name' => 'Glitz Studios', 'role' => 'Photography & Graphic Design'],
  ['name' => 'The Multimedia Group', 'role' => 'Motion Graphics Internship'],
  ['name' => 'Hypersoft Ghana LTD', 'role' => 'Website Manager'],
  ['name' => 'Voice of Hope Media Ministry', 'role' => 'Senior Graphic Designer'],
  ['name' => 'Busua Pastries and Squeeze', 'role' => 'Graphic Designer'],
];
$stats = [
  ['value' => '6+', 'label' => 'Systems built'],
  ['value' => '300+', 'label' => 'Creative works'],
  ['value' => 'GH', 'label' => 'Based in Ghana'],
];
/** Host for this request (proxy-aware; optional SITE_CANONICAL_HOST override). */
function request_host(): string
{
  if (defined('SITE_CANONICAL_HOST') && SITE_CANONICAL_HOST !== '') {
    return strtolower(SITE_CANONICAL_HOST);
  }

  foreach (['HTTP_X_FORWARDED_HOST', 'HTTP_X_HOST'] as $key) {
    if (!empty($_SERVER[$key])) {
      $host = trim(explode(',', (string) $_SERVER[$key])[0]);
      $host = preg_replace('/:\d+$/', '', $host);
      if ($host !== '') {
        return strtolower($host);
      }
    }
  }

  $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
  $host = preg_replace('/:\d+$/', '', $host);

  if ($host === '' || $host === 'localhost' || $host === '127.0.0.1') {
    $serverName = strtolower($_SERVER['SERVER_NAME'] ?? '');
    $serverName = preg_replace('/:\d+$/', '', $serverName);
    if ($serverName !== '' && $serverName !== 'localhost' && $serverName !== '127.0.0.1') {
      return $serverName;
    }
  }

  return $host !== '' ? $host : 'localhost';
}

/** True when the browser is on production (HTTP_HOST only — not SERVER_NAME). */
function site_is_live_domain(): bool
{
  $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
  $host = preg_replace('/:\d+$/', '', $host);

  if ($host === 'localhost' || $host === '127.0.0.1' || $host === '') {
    return false;
  }

  foreach (['HTTP_X_FORWARDED_HOST', 'HTTP_X_HOST'] as $key) {
    if (empty($_SERVER[$key])) {
      continue;
    }
    $fwd = strtolower(trim(explode(',', (string) $_SERVER[$key])[0]));
    $fwd = preg_replace('/:\d+$/', '', $fwd);
    if ($fwd === 'manuelcode.info' || $fwd === 'www.manuelcode.info') {
      return true;
    }
  }

  return $host === 'manuelcode.info' || $host === 'www.manuelcode.info';
}

/** Web path to site root from document root (empty string = domain root). */
function base_path(): string
{
  static $base = null;
  if ($base !== null) {
    return $base;
  }

  if (defined('SITE_BASE_PATH')) {
    $base = SITE_BASE_PATH;
    return $base;
  }

  if (site_is_live_domain()) {
    $base = '';
    return $base;
  }

  $docRoot = isset($_SERVER['DOCUMENT_ROOT'])
    ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/')
    : '';
  $projectDir = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');

  if ($docRoot !== '' && $projectDir !== '' && str_starts_with($projectDir, $docRoot)) {
    $base = substr($projectDir, strlen($docRoot));
    $base = $base === '' ? '' : rtrim($base, '/');
  } else {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if ($scriptDir === '/' || $scriptDir === '.') {
      $base = '';
    } else {
      $base = rtrim($scriptDir, '/');
    }
  }

  return $base;
}

/** Root-relative URL for internal pages and assets. */
function url(string $path = ''): string
{
  $path = ltrim(str_replace('\\', '/', $path), '/');
  $base = base_path();
  if ($path === '') {
    return $base === '' ? '/' : $base . '/';
  }
  return ($base === '' ? '' : $base) . '/' . $path;
}

function request_scheme(): string
{
  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    return 'https';
  }
  return 'http';
}

/** Root-relative path for redirects (keeps the browser on the current host). */
function redirect_url(string $path = ''): string
{
  return url($path);
}

/** Full URL (scheme + host + path) when an absolute URL is required. */
function site_url(string $path = ''): string
{
  if (PHP_SAPI === 'cli') {
    return url($path);
  }
  return request_scheme() . '://' . request_host() . url($path);
}

/** URL slug for a page (no .php). */
function page_slug(string $page): string
{
  $page = basename(ltrim(str_replace('\\', '/', $page), '/'));
  $page = preg_replace('/\.php$/i', '', $page);
  if ($page === '' || strtolower($page) === 'index') {
    return '';
  }
  return $page;
}

/** Link to an internal page (extensionless URL). */
function page_url(string $page): string
{
  return htmlspecialchars(url(page_slug($page)), ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
  return htmlspecialchars(url($path), ENT_QUOTES, 'UTF-8');
}

function isCurrentPage(string $page): bool
{
  $target = page_slug($page);
  $current = page_slug(basename($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
  if ($current === $target) {
    return true;
  }

  $uri = $_SERVER['REQUEST_URI'] ?? '';
  $path = trim(parse_url($uri, PHP_URL_PATH) ?: '', '/');
  $base = trim(base_path(), '/');
  if ($base !== '' && str_starts_with($path, $base)) {
    $path = ltrim(substr($path, strlen($base)), '/');
  }
  if ($target === '') {
    return $path === '' || $path === 'index';
  }
  return $path === $target;
}

/** @deprecated Use navLinkClass() in header. */
function activeNav(string $page): string
{
  return isCurrentPage($page) ? 'text-blue' : 'hover:text-ink';
}

function navLinkClass(string $page): string
{
  return isCurrentPage($page)
    ? 'bg-white text-ink shadow-sleek-sm font-extrabold'
    : 'text-body hover:text-ink font-semibold';
}
function serviceColorClasses($color)
{
  $map = [
    'blue' => ['bg' => 'bg-blue/10', 'text' => 'text-blue', 'hover' => 'hover:border-blue/40'],
    'mint' => ['bg' => 'bg-mint/10', 'text' => 'text-mint', 'hover' => 'hover:border-mint/40'],
    'amber' => ['bg' => 'bg-amber/10', 'text' => 'text-amber', 'hover' => 'hover:border-amber/40'],
    'deep' => ['bg' => 'bg-deep/10', 'text' => 'text-deep', 'hover' => 'hover:border-deep/40'],
  ];
  return $map[$color] ?? $map['blue'];
}

/** Extensionless slug → canonical slug (e.g. /project → /projects). */
function slug_aliases(): array
{
  return [
    'project' => 'projects',
  ];
}

/** Send bad or extensionless paths to the correct canonical URL. */
function canonical_redirect_if_needed(): void
{
  if (PHP_SAPI === 'cli' || headers_sent()) {
    return;
  }

  $uri = $_SERVER['REQUEST_URI'] ?? '';
  $path = parse_url($uri, PHP_URL_PATH) ?: $uri;
  $qs = $_SERVER['QUERY_STRING'] ?? '';
  $suffix = $qs !== '' ? '?' . $qs : '';

  $base = base_path();
  $basePrefix = $base === '' ? '' : $base;
  $relative = $path;
  if ($basePrefix !== '' && str_starts_with($path, $basePrefix)) {
    $relative = substr($path, strlen($basePrefix)) ?: '/';
  }
  $segment = strtolower(trim($relative, '/'));
  $aliases = slug_aliases();
  if ($segment !== '' && isset($aliases[$segment])) {
    header('Location: ' . redirect_url($aliases[$segment]) . $suffix, true, 301);
    exit;
  }

  if (str_contains($path, '/xamppfiles/htdocs/') || str_contains($path, '/Applications/XAMPP/')) {
    $slug = page_slug(basename($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    header('Location: ' . redirect_url($slug) . $suffix, true, 301);
    exit;
  }

  // Legacy /manuelcode/... → /... on live (e.g. /manuelcode/login.php → /login)
  if (site_is_live_domain() && preg_match('#^/manuelcode(/.*)?$#i', $path, $m)) {
    $rest = isset($m[1]) ? trim($m[1], '/') : '';
    $rest = preg_replace('/\.php$/i', '', $rest);
    header('Location: ' . redirect_url($rest) . $suffix, true, 301);
    exit;
  }

  if ($base === '' && !site_is_live_domain() && preg_match('#^/manuelcode(/.*)?$#i', $path, $m)) {
    $rest = isset($m[1]) ? trim($m[1], '/') : '';
    header('Location: ' . redirect_url($rest) . $suffix, true, 301);
    exit;
  }

  if ($base !== '' && preg_match('#^' . preg_quote($base, '#') . '/manuelcode(/.*)?$#i', $path, $m)) {
    $rest = isset($m[1]) ? trim($m[1], '/') : '';
    header('Location: ' . redirect_url($rest) . $suffix, true, 301);
    exit;
  }

  // Live: never expose .php in the browser (login.php → /login)
  if (site_is_live_domain() && preg_match('#^/([^/]+)\.php$#i', $path, $m)) {
    header('Location: ' . redirect_url(page_slug($m[1] . '.php')) . $suffix, true, 301);
    exit;
  }

  $basePrefix = $base === '' ? '' : $base;
  if (preg_match('#^' . preg_quote($basePrefix, '#') . '/(.+)\.php$#i', $path, $m)) {
    $slug = page_slug($m[1]);
    header('Location: ' . redirect_url($slug) . $suffix, true, 301);
    exit;
  }
  if ($path === $basePrefix . '/index.php') {
    header('Location: ' . redirect_url('') . $suffix, true, 301);
    exit;
  }
}

canonical_redirect_if_needed();

require_once __DIR__ . '/cms-load.php';
