<?php
$metaDesc = $metaDesc ?? 'Manuelcode — software engineering, web development, UI design, graphics, photography and media production based in Ghana.';
?>
<link rel="icon" href="<?= asset($brand['favicon']) ?>" type="image/png" />
<link rel="apple-touch-icon" href="<?= asset($brand['favicon']) ?>" />
<link rel="manifest" href="<?= asset('site.webmanifest') ?>" />
<meta name="theme-color" content="#0B1E3A" />
<meta name="description" content="<?= htmlspecialchars($metaDesc) ?>" />
<meta property="og:site_name" content="Manuelcode" />
<meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'Manuelcode.info') ?>" />
<meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>" />
<meta property="og:image" content="<?= asset($brand['logo']) ?>" />
<meta property="og:type" content="website" />
<meta name="twitter:card" content="summary_large_image" />
