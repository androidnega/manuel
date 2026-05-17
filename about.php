<?php
require_once __DIR__ . '/includes/data.php';
$cms = cms_page('about', [
  'label' => 'About Manuel',
  'title' => 'Software skill with creative experience.',
  'desc' => 'A software mind with a creative eye — building for real users in Ghana and beyond.',
  'body' => [
    'paragraph1' => 'I am a computer software engineer and multimedia creative focused on practical digital systems with clean interfaces and strong visual presentation.',
    'paragraph2' => 'My work spans campus platforms, document systems, attendance tools, inventory, websites, photography, video and graphic design.',
    'technical' => 'Web systems, UI design, databases, campus and business tools.',
    'creative' => 'Photography, video, motion graphics and poster design.',
    'experience_title' => 'Organizations I’ve worked with',
  ],
]);
$pageTitle = 'About | Manuelcode.info';
$pageStyles = ['assets/css/team-cards.css', 'assets/css/about-intro.css', 'assets/css/about-code-demo.css'];
$pageScripts = ['assets/js/about-code-demo.js'];
$heroLabel = $cms['label'];
$heroTitle = $cms['title'];
$heroDesc = $cms['desc'];
$pageBody = $cms['body'];
include 'includes/header.php';
include 'includes/page-hero.php';
?>
<main>
  <section class="py-10 sm:py-12 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="about-intro grid gap-6 items-start lg:items-stretch">
        <div class="about-intro__media reveal reveal-left rounded-2xl bg-cloud border border-line p-2.5 max-w-md mx-auto lg:max-w-none lg:mx-0 w-full">
          <?php $src = 'assets/images/manuel-kwofie-software-engineer-portrait-ghana.jpg'; $alt = 'Manuel Kwofie — software engineer and creative, Ghana'; $fit = 'portrait'; $frameClass = 'rounded-xl h-full min-h-[280px] lg:min-h-0'; include 'includes/media.php'; ?>
        </div>
        <div class="about-intro__content reveal reveal-right flex flex-col min-h-0">
          <p class="text-[0.9375rem] leading-relaxed text-body"><?= htmlspecialchars($pageBody['paragraph1'] ?? '') ?></p>
          <p class="text-[0.9375rem] leading-relaxed mt-4 text-body"><?= htmlspecialchars($pageBody['paragraph2'] ?? '') ?></p>
          <div class="mt-5 grid sm:grid-cols-2 gap-3 flex-1 min-h-0">
            <div class="reveal reveal-scale reveal-delay-1 rounded-2xl bg-cloud border border-line p-5 sm:p-6">
              <h3 class="font-extrabold text-sm">Technical</h3>
              <p class="mt-2 text-xs text-body leading-relaxed"><?= htmlspecialchars($pageBody['technical'] ?? '') ?></p>
            </div>
            <div class="reveal reveal-scale reveal-delay-2 rounded-2xl bg-cloud border border-line p-5 sm:p-6">
              <h3 class="font-extrabold text-sm">Creative</h3>
              <p class="mt-2 text-xs text-body leading-relaxed"><?= htmlspecialchars($pageBody['creative'] ?? '') ?></p>
            </div>
            <?php include __DIR__ . '/includes/about-code-demo.php'; ?>
          </div>
        </div>
      </div>
      <?php if (file_exists(__DIR__ . '/assets/Emmanuel_Kofi_Kwofie_CV.pdf')): ?>
        <div class="about-intro__cv-row mt-5">
          <a href="<?= asset('assets/Emmanuel_Kofi_Kwofie_CV.pdf') ?>" class="inline-flex items-center gap-2 rounded-full bg-deep text-white px-6 py-3 text-sm font-extrabold hover:bg-ink shadow-sleek-sm transition-all">Download CV <?= icon('download', 'w-4 h-4') ?></a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php if (!empty($teamMembers)): ?>
  <section class="py-12 sm:py-16 bg-cloud" aria-labelledby="team-heading">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="max-w-2xl">
        <p class="reveal text-xs font-extrabold text-blue uppercase tracking-[0.2em]">Team</p>
        <h2 id="team-heading" class="reveal reveal-delay-1 text-2xl sm:text-3xl font-extrabold tracking-tight mt-2">People behind the work</h2>
      </div>
      <ul class="team-grid mt-8 sm:mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6" role="list">
        <?php foreach ($teamMembers as $i => $member):
          $hasBio = trim((string) ($member['bio'] ?? '')) !== '';
          $photoPath = $member['photo_path'] ?? '';
          $photoVer = '';
          if ($photoPath !== '') {
            $photoFile = __DIR__ . '/' . ltrim($photoPath, '/');
            if (is_file($photoFile)) {
              $photoVer = '?v=' . (int) filemtime($photoFile);
            }
          }
        ?>
        <li class="reveal reveal-scale reveal-delay-<?= min(($i % 5) + 1, 5) ?> h-full">
          <article class="team-card group <?= $hasBio ? '' : 'team-card--no-bio' ?>">
            <span class="team-card__shine" aria-hidden="true"></span>
            <div class="team-card__media">
              <?php if ($photoPath !== ''): ?>
                <img
                  src="<?= asset($photoPath) . $photoVer ?>"
                  alt="<?= htmlspecialchars($member['name']) ?>"
                  class="team-card__img lazy-img"
                  loading="lazy"
                  decoding="async"
                />
              <?php else: ?>
                <div class="team-card__avatar" aria-hidden="true"><?= htmlspecialchars(mb_substr($member['name'], 0, 1)) ?></div>
              <?php endif; ?>
            </div>
            <div class="team-card__content">
              <h3 class="team-card__name"><?= htmlspecialchars($member['name']) ?></h3>
              <p class="team-card__role"><?= htmlspecialchars($member['role']) ?></p>
              <?php if ($hasBio): ?>
                <p class="team-card__bio team-card__bio--clamp"><?= htmlspecialchars($member['bio']) ?></p>
              <?php endif; ?>
            </div>
          </article>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
  <?php endif; ?>

  <section class="py-10 sm:py-12 bg-deep text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="reveal text-xs font-extrabold text-blue/80 uppercase tracking-[0.2em]">Experience</p>
      <h2 class="reveal reveal-delay-1 text-2xl sm:text-3xl font-extrabold tracking-tight mt-2"><?= htmlspecialchars($pageBody['experience_title'] ?? 'Organizations I’ve worked with') ?></h2>
      <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php foreach ($companies as $i => $co): ?>
          <article class="reveal reveal-scale reveal-delay-<?= min(($i % 5) + 1, 5) ?> rounded-2xl bg-white/5 border border-white/10 p-5 sm:p-6">
            <h3 class="font-extrabold"><?= htmlspecialchars($co['name']) ?></h3>
            <p class="mt-1 text-xs text-white/60"><?= htmlspecialchars($co['role']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
