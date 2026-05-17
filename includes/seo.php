<?php

/** SEO defaults and per-page meta for search engines and social sharing. */

function seo_default_keywords(): string
{
  return implode(', ', [
    'Manuelcode',
    'Manuel Kwofie',
    'software engineer Ghana',
    'web developer Ghana',
    'graphic design Ghana',
    'UI design',
    'web development',
    'mobile apps',
    'campus software',
    'poster design',
    'quote graphics',
    'photography Ghana',
    'multimedia creative',
    'Ahanta West',
    'business websites',
  ]);
}

function seo_site_name(): string
{
  global $site;
  return $site['name'] ?? 'Manuelcode';
}

function seo_current_slug(): string
{
  $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
  $slug = page_slug($script);
  return $slug === '' ? 'home' : $slug;
}

/** @return array{title:string,description:string,keywords:string,og_image:?string,type:string} */
function seo_page_config(string $slug): array
{
  global $brand, $site;

  $pages = [
    'home' => [
      'title' => 'Manuelcode.info | Software, Design & Media — Ghana',
      'description' => 'Manuel Kwofie (Manuelcode) — software engineer, web developer, UI designer, graphic design, photography and media production in Ghana. Websites, apps, campus systems and creative work.',
      'keywords' => seo_default_keywords() . ', portfolio, freelance developer',
      'type' => 'website',
    ],
    'about' => [
      'title' => 'About Manuel Kwofie | Software & Creative — Manuelcode',
      'description' => 'About Manuel Kwofie — computer software engineer and multimedia creative in Ghana. Web systems, databases, photography, video and graphic design.',
      'keywords' => 'Manuel Kwofie, about Manuelcode, software engineer bio Ghana, creative developer',
      'type' => 'profile',
    ],
    'projects' => [
      'title' => 'Projects | Web & Campus Systems — Manuelcode',
      'description' => 'Software projects by Manuelcode — Kuukuacares, Go Ahanta, KTI, Quizsnap, Documento, SellApp and more. Live websites and campus platforms.',
      'keywords' => 'Manuelcode projects, Ghana web projects, campus software, MP website, school website',
      'type' => 'website',
    ],
    'services' => [
      'title' => 'Services | Web, UI, Design & Systems — Manuelcode',
      'description' => 'Web development, UI design, graphic design, photo & video, and business systems — services by Manuelcode in Ghana.',
      'keywords' => 'web development services Ghana, graphic design services, UI design freelancer',
      'type' => 'website',
    ],
    'designs' => [
      'title' => 'Design Gallery | Posters & Graphics — Manuelcode',
      'description' => 'Graphic design gallery by Manuelcode — quote posters, campaign visuals, brand identity and social graphics. Ghana creative work.',
      'keywords' => 'graphic design Ghana, poster design, quote graphics, brand design, Manuelcode designs',
      'type' => 'website',
    ],
    'quotes' => [
      'title' => 'Request a Quote | Websites & Apps — Manuelcode',
      'description' => 'Request a project quote from Manuelcode — websites, mobile apps, campus systems, design and timelines for Ghana and international clients.',
      'keywords' => 'web development quote Ghana, app development quote, project estimate',
      'type' => 'website',
    ],
    'contact' => [
      'title' => 'Contact Manuelcode | Start a Project',
      'description' => 'Contact Manuel Kwofie for websites, software, UI design and creative work. Email, phone and WhatsApp — based in Ghana.',
      'keywords' => 'contact Manuelcode, hire web developer Ghana, software developer contact',
      'type' => 'website',
    ],
    'news' => [
      'title' => 'News & Updates | Manuelcode',
      'description' => 'News and updates from Manuelcode — launches, projects and notes on software and creative work.',
      'keywords' => 'Manuelcode news, developer blog Ghana, project updates',
      'type' => 'website',
    ],
    'login' => [
      'title' => 'Admin | Manuelcode',
      'description' => 'Manuelcode site administration.',
      'keywords' => '',
      'type' => 'website',
      'robots' => 'noindex, nofollow',
    ],
  ];

  $config = $pages[$slug] ?? [
    'title' => 'Manuelcode.info',
    'description' => 'Manuelcode — software engineering, design and media in Ghana.',
    'keywords' => seo_default_keywords(),
    'type' => 'website',
  ];

  if ($slug === 'news-article') {
    $config['robots'] = 'index, follow';
  }

  return $config;
}

function seo_canonical_url(): string
{
  $uri = $_SERVER['REQUEST_URI'] ?? '/';
  $path = parse_url($uri, PHP_URL_PATH) ?: '/';
  $path = '/' . trim($path, '/');
  if ($path === '/') {
    return site_url('');
  }
  return site_url(ltrim($path, '/'));
}

/** Favicon / logo mark used for WhatsApp, Facebook, Twitter, etc. */
function seo_social_image_path(): string
{
  global $brand;
  $jpg = dirname(__DIR__) . '/assets/images/manuelcode-favicon-social.jpg';
  if (is_file($jpg)) {
    return 'assets/images/manuelcode-favicon-social.jpg';
  }
  $path = $brand['favicon'] ?? 'assets/images/manuelcode-favicon.webp';
  return seo_resolve_image_path($path);
}

function seo_social_image_url(): string
{
  return site_url(ltrim(seo_social_image_path(), '/'));
}

function seo_social_image_type(): string
{
  $path = seo_social_image_path();
  if (str_ends_with($path, '.webp')) {
    return 'image/webp';
  }
  if (str_ends_with($path, '.png')) {
    return 'image/png';
  }
  if (str_ends_with($path, '.jpg') || str_ends_with($path, '.jpeg')) {
    return 'image/jpeg';
  }
  return 'image/png';
}

function seo_og_image_url(?string $path): string
{
  if ($path === null || $path === '') {
    global $brand;
    $path = $brand['logo'] ?? 'assets/images/manuelcode-logo-main.png';
  }
  if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
    return $path;
  }
  return site_url(ltrim(seo_resolve_image_path($path), '/'));
}

function seo_apply_globals(): void
{
  $slug = seo_current_slug();
  $config = seo_page_config($slug);

  global $pageTitle, $metaDesc, $metaKeywords, $ogType, $metaRobots, $canonicalUrl;

  if (empty($pageTitle)) {
    $pageTitle = $config['title'];
  }
  if (empty($metaDesc)) {
    $metaDesc = $config['description'];
  }
  if (empty($metaKeywords)) {
    $metaKeywords = $config['keywords'] ?? seo_default_keywords();
  }
  if (empty($ogType)) {
    $ogType = $config['type'] ?? 'website';
  }
  if (empty($metaRobots)) {
    $metaRobots = $config['robots'] ?? 'index, follow, max-image-preview:large';
  }
  if (empty($canonicalUrl)) {
    $canonicalUrl = seo_canonical_url();
  }
}

function seo_json_ld(): array
{
  global $site;
  $slug = seo_current_slug();
  $graphs = [];

  $graphs[] = [
    '@type' => 'Organization',
    '@id' => site_url('#organization'),
    'name' => seo_site_name(),
    'url' => site_url(''),
    'logo' => seo_og_image_url('assets/images/manuelcode-logo-main.png'),
    'email' => $site['email'] ?? '',
    'description' => 'Software engineering, web development, UI design, graphic design and media production in Ghana.',
    'founder' => [
      '@type' => 'Person',
      'name' => $site['title'] ?? 'Manuel Kwofie',
    ],
    'areaServed' => 'GH',
  ];

  if ($slug === 'home') {
    $graphs[] = [
      '@type' => 'WebSite',
      '@id' => site_url('#website'),
      'url' => site_url(''),
      'name' => seo_site_name(),
      'description' => 'Portfolio and services — software, design and media.',
      'publisher' => ['@id' => site_url('#organization')],
      'inLanguage' => 'en',
    ];
  }

  if ($slug === 'about') {
    $graphs[] = [
      '@type' => 'Person',
      'name' => $site['title'] ?? 'Manuel Kwofie',
      'url' => site_url('about'),
      'image' => seo_og_image_url('assets/images/manuel-kwofie-software-engineer-portrait-ghana.jpg'),
      'jobTitle' => 'Software Engineer & Multimedia Creative',
      'worksFor' => ['@id' => site_url('#organization')],
    ];
  }

  return [
    '@context' => 'https://schema.org',
    '@graph' => $graphs,
  ];
}

function seo_image_paths_map(): array
{
  return [
    'assets/images/quote-poster-original.jpg' => 'assets/images/manuelcode-leadership-quote-poster-design-ghana.jpg',
    'assets/images/manuel-portrait.jpg' => 'assets/images/manuel-kwofie-software-engineer-portrait-ghana.jpg',
    'assets/images/brand-guide.jpg' => 'assets/images/manuelcode-brand-identity-design-guide.jpg',
    'assets/images/main-logo.png' => 'assets/images/manuelcode-logo-main.png',
    'assets/images/dark-logo.webp' => 'assets/images/manuelcode-logo-dark.webp',
    'assets/images/favicon.webp' => 'assets/images/manuelcode-favicon.webp',
    'assets/images/bring-me-work-promo.png' => 'assets/images/manuelcode-bring-me-work-promo-software-ghana.png',
    'assets/images/quote-poster-original.png' => 'assets/images/manuelcode-leadership-quote-poster-design-ghana.jpg',
    'assets/images/manuel-portrait.png' => 'assets/images/manuel-kwofie-software-engineer-portrait-ghana.jpg',
    'assets/images/brand-guide.png' => 'assets/images/manuelcode-brand-identity-design-guide.jpg',
    'assets/images/dark-logo.png' => 'assets/images/manuelcode-logo-dark.webp',
    'assets/images/favicon.png' => 'assets/images/manuelcode-favicon.webp',
  ];
}

function seo_resolve_image_path(string $path): string
{
  $map = seo_image_paths_map();
  return $map[$path] ?? $path;
}
