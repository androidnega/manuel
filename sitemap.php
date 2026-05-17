<?php
require_once __DIR__ . '/includes/data.php';

header('Content-Type: application/xml; charset=UTF-8');

$pages = [
  ['loc' => '', 'priority' => '1.0', 'changefreq' => 'weekly'],
  ['loc' => 'about', 'priority' => '0.9', 'changefreq' => 'monthly'],
  ['loc' => 'projects', 'priority' => '0.9', 'changefreq' => 'weekly'],
  ['loc' => 'services', 'priority' => '0.85', 'changefreq' => 'monthly'],
  ['loc' => 'designs', 'priority' => '0.85', 'changefreq' => 'weekly'],
  ['loc' => 'quotes', 'priority' => '0.8', 'changefreq' => 'monthly'],
  ['loc' => 'contact', 'priority' => '0.8', 'changefreq' => 'yearly'],
  ['loc' => 'news', 'priority' => '0.85', 'changefreq' => 'weekly'],
];

$pdo = cms_db();
$newsPosts = cms_news_posts($pdo, true);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$now = date('c');
foreach ($pages as $p) {
  $loc = site_url($p['loc']);
  echo "  <url>\n";
  echo '    <loc>' . htmlspecialchars($loc) . "</loc>\n";
  echo "    <lastmod>{$now}</lastmod>\n";
  echo '    <changefreq>' . $p['changefreq'] . "</changefreq>\n";
  echo '    <priority>' . $p['priority'] . "</priority>\n";
  echo "  </url>\n";
}

foreach ($newsPosts as $post) {
  $loc = site_url('news/' . $post['slug']);
  $lastmod = $post['updated_at'] ?? $post['published_at'] ?? $post['created_at'];
  echo "  <url>\n";
  echo '    <loc>' . htmlspecialchars($loc) . "</loc>\n";
  if ($lastmod) {
    echo '    <lastmod>' . htmlspecialchars(date('c', strtotime($lastmod))) . "</lastmod>\n";
  }
  echo "    <changefreq>monthly</changefreq>\n";
  echo "    <priority>0.7</priority>\n";
  echo "  </url>\n";
}

echo "</urlset>\n";
