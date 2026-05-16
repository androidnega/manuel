<?php
require_once __DIR__ . '/includes/data.php';

$pdo = cms_db();
$posts = cms_news_posts($pdo, true);
$pageTitle = 'News | Manuelcode.info';
$heroLabel = 'News';
$heroTitle = 'Updates, launches and notes.';
$heroDesc = 'What I am building, shipping and learning along the way.';
$pageStyles = ['assets/css/news.css'];
include 'includes/header.php';
include 'includes/page-hero.php';
?>
<main class="news-index">
  <section class="py-10 sm:py-14 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <?php if ($posts === []): ?>
        <p class="news-empty reveal">No posts yet. Check back soon.</p>
      <?php else: ?>
        <div class="news-grid">
          <?php foreach ($posts as $post):
            $date = $post['published_at'] ?: $post['created_at'];
            $ts = strtotime($date);
            $dateLabel = $ts ? date('M j, Y', $ts) : '';
            $cover = trim($post['cover_image'] ?? '');
            $articleHref = news_article_url($post['slug']);
          ?>
          <article class="news-card reveal">
            <a href="<?= $articleHref ?>" class="news-card__link">
              <?php if ($cover !== ''): ?>
                <div class="news-card__media">
                  <img src="<?= asset($cover) ?>" alt="" loading="lazy" class="news-card__img" />
                </div>
              <?php else: ?>
                <div class="news-card__media news-card__media--placeholder">
                  <span class="news-card__badge">News</span>
                </div>
              <?php endif; ?>
              <div class="news-card__body">
                <?php if ($dateLabel): ?><time class="news-card__date" datetime="<?= htmlspecialchars(date('c', $ts)) ?>"><?= htmlspecialchars($dateLabel) ?></time><?php endif; ?>
                <h2 class="news-card__title"><?= htmlspecialchars($post['title']) ?></h2>
                <?php if (!empty($post['excerpt'])): ?>
                  <p class="news-card__excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>
                <?php endif; ?>
                <span class="news-card__cta">Read article <?= icon('arrow-right', 'w-4 h-4') ?></span>
              </div>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
