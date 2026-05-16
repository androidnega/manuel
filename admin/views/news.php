<?php
$posts = cms_news_posts($pdo, false);
$subscriberCount = cms_newsletter_count($pdo);
?>
<div class="admin-intro admin-intro--row">
  <p class="admin-intro__text">Publish news posts. Subscribers (<?= (int) $subscriberCount ?>) can be emailed when you publish.</p>
  <a href="<?= url('login') ?>?p=newsedit" class="admin-btn admin-btn--primary admin-btn--sm"><?= admin_icon('save') ?> New post</a>
</div>

<?php if (!$posts): ?>
  <p class="admin-card text-sm text-body">No posts yet. Create your first article.</p>
<?php else: ?>
  <div class="space-y-3">
    <?php foreach ($posts as $post):
      $date = $post['published_at'] ?: $post['created_at'];
      $ts = strtotime($date);
      $dateLabel = $ts ? date('M j, Y', $ts) : '—';
    ?>
    <article class="admin-card flex flex-wrap items-center justify-between gap-3">
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
          <h3 class="font-extrabold text-sm truncate"><?= htmlspecialchars($post['title']) ?></h3>
          <?php if (!empty($post['is_published'])): ?>
            <span class="rounded-full bg-mint/20 text-mint px-2 py-0.5 text-[10px] font-extrabold">Published</span>
          <?php else: ?>
            <span class="rounded-full bg-cloud px-2 py-0.5 text-[10px] font-extrabold text-body">Draft</span>
          <?php endif; ?>
        </div>
        <p class="text-xs text-body mt-1"><?= htmlspecialchars($dateLabel) ?> · /news/<?= htmlspecialchars($post['slug']) ?></p>
      </div>
      <div class="flex flex-wrap gap-2 shrink-0">
        <?php if (!empty($post['is_published'])): ?>
          <a href="<?= news_article_url($post['slug']) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--sm"><?= admin_icon('site') ?> View</a>
        <?php endif; ?>
        <a href="<?= url('login') ?>?p=newsedit&id=<?= (int) $post['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Edit</a>
        <form method="post" action="<?= url('login') ?>" onsubmit="return confirm('Delete this post?');">
          <input type="hidden" name="action" value="delete_news" />
          <input type="hidden" name="id" value="<?= (int) $post['id'] ?>" />
          <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm text-red-600">Delete</button>
        </form>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
