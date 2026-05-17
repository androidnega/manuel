<?php
require_once __DIR__ . '/seo.php';
seo_apply_globals();

$metaDesc = $metaDesc ?? 'Manuelcode — software engineering, web development, UI design, graphics, photography and media production based in Ghana.';
$metaKeywords = $metaKeywords ?? seo_default_keywords();
$ogImage = $ogImage ?? null;
$ogType = $ogType ?? 'website';
$metaRobots = $metaRobots ?? 'index, follow, max-image-preview:large';
$canonicalUrl = $canonicalUrl ?? seo_canonical_url();
$ogImageUrl = seo_og_image_url($ogImage);
$jsonLd = seo_json_ld();
?>
<?php $faviconMime = str_ends_with($brand['favicon'] ?? '', '.webp') ? 'image/webp' : 'image/png'; ?>
<link rel="icon" href="<?= asset($brand['favicon']) ?>" type="<?= $faviconMime ?>" />
<link rel="apple-touch-icon" href="<?= asset($brand['favicon']) ?>" />
<link rel="manifest" href="<?= asset('site.webmanifest') ?>" />
<link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>" />
<meta name="theme-color" content="#0B1E3A" />
<meta name="description" content="<?= htmlspecialchars($metaDesc) ?>" />
<meta name="keywords" content="<?= htmlspecialchars($metaKeywords) ?>" />
<meta name="robots" content="<?= htmlspecialchars($metaRobots) ?>" />
<meta name="author" content="Manuel Kwofie" />
<meta name="geo.region" content="GH" />
<meta property="og:site_name" content="<?= htmlspecialchars(seo_site_name()) ?>" />
<meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'Manuelcode.info') ?>" />
<meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>" />
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>" />
<meta property="og:image" content="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES, 'UTF-8') ?>" />
<meta property="og:image:alt" content="<?= htmlspecialchars($pageTitle ?? 'Manuelcode') ?>" />
<meta property="og:type" content="<?= htmlspecialchars($ogType) ?>" />
<meta property="og:locale" content="en_GH" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?= htmlspecialchars($pageTitle ?? 'Manuelcode.info') ?>" />
<meta name="twitter:description" content="<?= htmlspecialchars($metaDesc) ?>" />
<meta name="twitter:image" content="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES, 'UTF-8') ?>" />
<?php if (defined('GOOGLE_SITE_VERIFICATION') && GOOGLE_SITE_VERIFICATION !== ''): ?>
<meta name="google-site-verification" content="<?= htmlspecialchars(GOOGLE_SITE_VERIFICATION) ?>" />
<?php endif; ?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
