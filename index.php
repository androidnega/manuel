<?php
require_once __DIR__ . '/includes/data.php';
$home = cms_page('home', [
  'label' => '',
  'title' => 'Building practical software with energy, clarity and care.',
  'desc' => 'I’m Manuel — web and software developer in Ghana.',
  'body' => [
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
  ],
]);
$hb = $home['body'];
$pageTitle = 'Manuelcode.info | Home';
$showHomeLoader = true;
$pdo = cms_db();
$homeHeroSlides = cms_home_hero_public($pdo);
$homeHeroInterval = cms_home_hero_interval_ms($pdo);
$pageStyles = ['assets/css/home-hero-slideshow.css'];
$pageScripts = ['assets/js/home-hero-slideshow.js'];
include 'includes/header.php';
?>
<main>
  <section class="relative overflow-hidden bg-white soft-grid">
    <div class="absolute inset-0 bg-gradient-to-b from-white via-white/95 to-white pointer-events-none"></div>
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">
      <div class="grid lg:grid-cols-[1fr_1.05fr] gap-8 lg:gap-12 items-center">
        <div class="order-2 lg:order-1">
          <div class="reveal inline-flex items-center gap-2 rounded-full bg-cloud border border-line px-3 py-1.5 text-[11px] font-extrabold text-body shadow-sleek-sm">
            <span class="h-2 w-2 rounded-full bg-mint animate-pulse"></span>
            <?= htmlspecialchars($home['body']['badge'] ?? '') ?>
          </div>

          <h1 class="reveal reveal-delay-1 mt-5 text-3xl sm:text-4xl lg:text-[2.75rem] font-extrabold tracking-[-0.04em] leading-[1.06] text-ink">
            <?= htmlspecialchars($home['title']) ?>
          </h1>

          <p class="reveal reveal-delay-2 mt-4 max-w-lg text-[0.9375rem] leading-relaxed text-body">
            <?= htmlspecialchars($home['body']['intro'] ?? '') ?>
          </p>

          <ul class="reveal reveal-delay-2 mt-5 flex flex-wrap gap-2 text-xs font-bold text-body">
            <li class="inline-flex items-center gap-1.5 rounded-full bg-white border border-line px-3 py-1.5 shadow-sleek-sm"><?= icon('code', 'w-3.5 h-3.5 text-blue') ?> Web</li>
            <li class="inline-flex items-center gap-1.5 rounded-full bg-white border border-line px-3 py-1.5 shadow-sleek-sm"><?= icon('smartphone', 'w-3.5 h-3.5 text-mint') ?> Mobile</li>
            <li class="inline-flex items-center gap-1.5 rounded-full bg-white border border-line px-3 py-1.5 shadow-sleek-sm"><?= icon('settings', 'w-3.5 h-3.5 text-deep') ?> Custom systems</li>
            <li class="inline-flex items-center gap-1.5 rounded-full bg-white border border-line px-3 py-1.5 shadow-sleek-sm"><?= icon('pen', 'w-3.5 h-3.5 text-amber') ?> Design & media</li>
          </ul>

          <div class="reveal reveal-delay-3 mt-6 flex flex-col sm:flex-row flex-wrap gap-3">
            <a href="<?= page_url('attachment.php') ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-mint text-white px-6 py-3 text-sm font-extrabold hover:bg-mint/90 shadow-sleek-sm transition-all hover:-translate-y-0.5">
              Register attachment <?= icon('arrow-right', 'w-4 h-4') ?>
            </a>
            <a href="<?= page_url('contact.php') ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-blue text-white px-6 py-3 text-sm font-extrabold hover:bg-blue/90 shadow-sleek-sm transition-all hover:-translate-y-0.5">
              Let’s work <?= icon('arrow-right', 'w-4 h-4') ?>
            </a>
            <a href="<?= page_url('projects.php') ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-white border border-line px-6 py-3 text-sm font-extrabold hover:border-ink hover:shadow-sleek-sm transition-all">
              View projects
            </a>
          </div>

          <div class="mt-6 grid grid-cols-3 gap-2 max-w-md">
            <?php foreach ($stats as $i => $stat): ?>
              <div class="reveal reveal-scale rounded-2xl bg-white border border-line p-3 sm:p-4 text-center sm:text-left shadow-sleek-sm reveal-delay-<?= min($i + 1, 5) ?>">
                <p class="text-2xl font-extrabold tracking-tight text-ink"><?= htmlspecialchars($stat['value']) ?></p>
                <p class="mt-0.5 text-[10px] sm:text-xs font-bold text-body"><?= htmlspecialchars($stat['label']) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="order-1 lg:order-2 flex justify-center lg:justify-end">
          <?php include __DIR__ . '/includes/home-hero-slideshow.php'; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="py-8 sm:py-10 bg-white border-y border-line">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <?php
      $label = $hb['clients_label'] ?? 'Live work';
      $title = $hb['clients_title'] ?? 'Projects in production';
      $lead = $hb['clients_lead'] ?? '';
      unset($ctaHref, $ctaLabel);
      include 'includes/section-head.php';
      ?>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <?php foreach ($clientLogos as $i => $client): ?>
          <div class="reveal reveal-scale reveal-delay-<?= min(($i % 5) + 1, 5) ?>">
            <?php include __DIR__ . '/includes/client-card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="py-10 sm:py-12 bg-cloud">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <?php
      $label = $hb['pages_label'] ?? 'Site pages';
      $title = $hb['pages_title'] ?? 'Go to a page';
      $lead = $hb['pages_lead'] ?? '';
      unset($ctaHref, $ctaLabel);
      include 'includes/section-head.php';
      ?>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($homePages as $i => $page):
          $cardRevealClass = 'reveal-delay-' . min(($i % 5) + 1, 5);
          include __DIR__ . '/includes/page-card.php';
        endforeach; ?>
      </div>
    </div>
  </section>

  <section class="py-10 sm:py-12 bg-deep text-white">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 text-center">
      <h2 class="reveal text-xl sm:text-2xl font-extrabold"><?= htmlspecialchars($hb['cta_title'] ?? '') ?></h2>
      <p class="reveal reveal-delay-1 mt-2 text-[0.9375rem] leading-relaxed text-white/70"><?= htmlspecialchars($hb['cta_text'] ?? '') ?></p>
      <div class="reveal reveal-delay-2 mt-5 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="<?= page_url('contact.php') ?>" class="inline-flex items-center gap-2 rounded-full bg-blue text-white px-6 py-3 text-sm font-extrabold hover:bg-blue/90 shadow-sleek-sm transition-all hover:-translate-y-0.5">
          Go to contact <?= icon('arrow-right', 'w-4 h-4') ?>
        </a>
        <a href="<?= htmlspecialchars($site['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-3 text-sm font-extrabold hover:bg-white/10 transition-colors">
          WhatsApp <?= icon('message', 'w-4 h-4') ?>
        </a>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
