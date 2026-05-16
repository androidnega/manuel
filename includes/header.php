<?php require_once __DIR__ . '/data.php'; ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <?php include __DIR__ . '/viewport-meta.php'; ?>
  <title><?= htmlspecialchars($pageTitle ?? 'Manuelcode.info') ?></title>
  <?php include __DIR__ . '/theme-head.php'; ?>
  <?php if (!empty($pageStyles) && is_array($pageStyles)): foreach ($pageStyles as $styleHref): ?>
  <link rel="stylesheet" href="<?= asset($styleHref) ?>" />
  <?php endforeach; endif; ?>
  <?php include __DIR__ . '/head-meta.php'; ?>
  <?php include __DIR__ . '/site-lock-head.php'; ?>
  <?php include __DIR__ . '/back-to-top-head.php'; ?>
  <?php if (!empty($showHomeLoader)): ?>
  <link rel="stylesheet" href="<?= asset('assets/css/home-loader.css') ?>" />
  <link rel="preload" href="<?= asset($brand['favicon'] ?? 'assets/images/favicon.webp') ?>" as="image" />
  <?php endif; ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
          colors: {
            ink: '#101828',
            body: '#475467',
            line: '#EAECF0',
            cloud: '#F9FAFB',
            blue: '#FF7A00',
            deep: '#0B1E3A',
            mint: '#12B76A',
            amber: '#F79009',
            navy: '#0B1E3A'
          },
          boxShadow: {
            sleek: '0 24px 48px -28px rgba(11, 30, 58, 0.28)',
            'sleek-sm': '0 12px 28px -16px rgba(11, 30, 58, 0.18)',
          }
        }
      }
    }
  </script>
  <style type="text/tailwindcss">
    @layer utilities {
      .soft-grid {
        background-image: linear-gradient(#eef2f6 1px, transparent 1px), linear-gradient(90deg, #eef2f6 1px, transparent 1px);
        background-size: 44px 44px;
      }
      .hero-promo-wrap {
        -webkit-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
      }
      .hero-promo-img {
        -webkit-user-drag: none;
        user-drag: none;
        pointer-events: none;
      }
      .reveal {
        opacity: 0;
        transform: translate3d(0, 28px, 0);
        transition:
          opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
          transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        will-change: opacity, transform;
      }
      .reveal.is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0);
      }
      .reveal-fade { transform: none; }
      .reveal-scale {
        transform: translate3d(0, 12px, 0) scale(0.97);
      }
      .reveal-scale.is-visible {
        transform: translate3d(0, 0, 0) scale(1);
      }
      .reveal-left { transform: translate3d(-24px, 0, 0); }
      .reveal-right { transform: translate3d(24px, 0, 0); }
      .reveal-delay-1 { transition-delay: 0.1s; }
      .reveal-delay-2 { transition-delay: 0.18s; }
      .reveal-delay-3 { transition-delay: 0.26s; }
      .reveal-delay-4 { transition-delay: 0.34s; }
      .reveal-delay-5 { transition-delay: 0.42s; }
      @media (prefers-reduced-motion: reduce) {
        .reveal {
          opacity: 1;
          transform: none;
          transition: none;
        }
      }
      .brand-icon {
        --brand-icon-size: 2.75rem;
        width: var(--brand-icon-size);
        height: var(--brand-icon-size);
        padding: 3px;
        border-radius: 50%;
        flex-shrink: 0;
        background: conic-gradient(
          from 0deg,
          transparent 0deg 22deg,
          #ff7a00 22deg 158deg,
          transparent 158deg 202deg,
          #0b1e3a 202deg 338deg,
          transparent 338deg 360deg
        );
      }
      .brand-icon--lg {
        --brand-icon-size: 3.25rem;
        padding: 3.5px;
      }
      .brand-icon__img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        object-position: center 18%;
        display: block;
        background: #fff;
      }
      .brand-tagline {
        margin-top: 0.125rem;
        line-height: 1;
      }
    }
  </style>
</head>
<body class="bg-[#fafbfc] text-ink font-sans antialiased selection:bg-blue/20">
  <?php if (!empty($showHomeLoader)): include __DIR__ . '/home-loader.php'; endif; ?>
  <header class="sticky top-0 z-50 pt-3 sm:pt-4">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="w-full rounded-2xl border border-line/80 bg-white/95 backdrop-blur-xl shadow-sleek-sm">
      <nav class="flex items-center justify-between gap-2 sm:gap-3 px-4 sm:px-5 lg:px-6 py-2.5 sm:py-3 min-h-[4rem] sm:min-h-[4.25rem]" aria-label="Main">
        <a href="<?= page_url('index.php') ?>" class="flex items-center shrink-0 min-w-0 py-0.5 transition-opacity hover:opacity-90" aria-label="Manuelcode home">
          <?php $logoVariant = 'wordmark'; $logoTheme = 'light'; $showIcon = true; include __DIR__ . '/logo.php'; ?>
        </a>

        <div class="hidden md:flex items-center p-1 rounded-full bg-cloud border border-line/90">
          <?php foreach ($headerNav as $label => $href): ?>
            <a
              href="<?= page_url($href) ?>"
              class="px-3.5 lg:px-4 py-2 rounded-full text-xs lg:text-sm transition-all duration-200 <?= navLinkClass($href) ?>"
              <?= isCurrentPage($href) ? 'aria-current="page"' : '' ?>
            ><?= htmlspecialchars($label) ?></a>
          <?php endforeach; ?>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <?php include __DIR__ . '/theme-toggle.php'; ?>
          <a
            href="<?= page_url('contact.php') ?>"
            class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-deep text-white px-4 py-2 text-xs sm:text-sm font-extrabold hover:bg-ink transition-colors"
          >
            Let’s talk <?= icon('arrow-right', 'w-3.5 h-3.5 sm:w-4 sm:h-4') ?>
          </a>
          <button
            id="menuBtn"
            type="button"
            class="md:hidden h-10 w-10 rounded-xl border border-line bg-cloud grid place-items-center text-ink hover:border-ink transition-colors"
            aria-label="Open menu"
            aria-expanded="false"
          >
            <?= icon('menu', 'w-5 h-5') ?>
          </button>
        </div>
      </nav>

      <div id="mobileMenu" class="hidden md:hidden border-t border-line/80 px-4 sm:px-5 pb-3 pt-1">
        <div class="grid grid-cols-2 gap-1.5">
          <?php foreach ($headerNav as $label => $href): ?>
            <a
              href="<?= page_url($href) ?>"
              class="px-3 py-2.5 rounded-xl text-sm font-bold text-center transition-colors <?= isCurrentPage($href) ? 'bg-blue/10 text-blue' : 'text-body hover:bg-cloud' ?>"
              <?= isCurrentPage($href) ? 'aria-current="page"' : '' ?>
            ><?= htmlspecialchars($label) ?></a>
          <?php endforeach; ?>
        </div>
        <a href="<?= page_url('contact.php') ?>" class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-deep text-white py-2.5 text-sm font-extrabold">
          Let’s talk <?= icon('arrow-right', 'w-4 h-4') ?>
        </a>
      </div>
      </div>
    </div>
  </header>
