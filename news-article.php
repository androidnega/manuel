<?php
require_once __DIR__ . '/includes/data.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
  header('Location: ' . redirect_url('news'));
  exit;
}

$pdo = cms_db();
$post = cms_news_post_by_slug($pdo, $slug);
if (!$post || empty($post['is_published'])) {
  http_response_code(404);
  $pageTitle = 'Not found | Manuelcode.info';
  $heroLabel = 'News';
  $heroTitle = 'Post not found';
  $heroDesc = 'This article may have been removed or is not published yet.';
  include 'includes/header.php';
  include 'includes/page-hero.php';
  echo '<main class="py-16 text-center"><a href="' . page_url('news.php') . '" class="text-sm font-bold text-blue">← Back to news</a></main>';
  include 'includes/footer.php';
  exit;
}

$date = $post['published_at'] ?: $post['created_at'];
$ts = strtotime($date);
$dateLabel = $ts ? date('F j, Y', $ts) : '';
$cover = trim($post['cover_image'] ?? '');
$pageTitle = htmlspecialchars($post['title']) . ' | News | Manuelcode.info';
$metaDesc = trim($post['excerpt'] ?? '') ?: strip_tags($post['title']) . ' — news from Manuelcode, software and design in Ghana.';
$canonicalUrl = site_url('news/' . $post['slug']);
$heroLabel = 'News';
$heroTitle = $post['title'];
$heroDesc = $post['excerpt'] ?? '';
$pageStyles = ['assets/css/news.css'];
include 'includes/header.php';
include 'includes/page-hero.php';
?>
<main class="news-article">
  <article class="py-10 sm:py-14 bg-white">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
      <a href="<?= page_url('news.php') ?>" class="news-back reveal inline-flex items-center gap-2 text-xs font-bold text-blue mb-8">← Back to news</a>
      <?php if ($dateLabel): ?><time class="news-article__date reveal" datetime="<?= htmlspecialchars(date('c', $ts)) ?>"><?= htmlspecialchars($dateLabel) ?></time><?php endif; ?>
      <?php if ($cover !== ''): ?>
      <figure class="news-article__cover reveal">
        <img src="<?= asset($cover) ?>" alt="" loading="lazy" class="news-article__cover-img" />
      </figure>
      <?php endif; ?>
      <div class="news-prose reveal">
        <?= cms_sanitize_news_html($post['content_html']) ?>
      </div>
    </div>
  </article>
</main>
<?php include 'includes/footer.php'; ?>
