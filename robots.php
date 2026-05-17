<?php
require_once __DIR__ . '/includes/data.php';
header('Content-Type: text/plain; charset=UTF-8');
$sitemap = site_url('sitemap.xml');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /login\n";
echo "Disallow: /newsletter-subscribe\n";
echo "Disallow: /storage/\n";
echo "\nSitemap: {$sitemap}\n";
