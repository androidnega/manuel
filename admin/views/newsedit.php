<?php
$editId = (int) ($_GET['id'] ?? 0);
$post = $editId > 0 ? cms_news_post_by_id($pdo, $editId) : null;
if ($editId > 0 && !$post) {
  echo '<p class="admin-flash admin-flash--err">Post not found.</p>';
  return;
}
$publishedAtLocal = '';
if (!empty($post['published_at'])) {
  $ts = strtotime($post['published_at']);
  if ($ts !== false) {
    $publishedAtLocal = date('Y-m-d\TH:i', $ts);
  }
}
$mail = cms_mail_config($pdo);
?>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<div class="admin-intro">
  <p class="admin-intro__text"><?= $post ? 'Edit article' : 'Write a new article with the editor below.' ?></p>
  <a href="<?= url('login') ?>?p=news" class="admin-link text-sm font-bold"><?= admin_icon('back') ?> All posts</a>
</div>

<form method="post" action="<?= url('login') ?>" class="admin-card max-w-4xl space-y-4" id="newsPostForm">
  <input type="hidden" name="action" value="save_news" />
  <input type="hidden" name="id" value="<?= $post ? (int) $post['id'] : 0 ?>" />
  <input type="hidden" name="content_html" id="contentHtml" value="" />

  <label class="admin-field">
    <span class="admin-field__label">Title</span>
    <input name="title" required class="admin-input" value="<?= htmlspecialchars($post['title'] ?? '') ?>" />
  </label>

  <label class="admin-field">
    <span class="admin-field__label">URL slug</span>
    <input name="slug" class="admin-input" placeholder="auto-from-title" value="<?= htmlspecialchars($post['slug'] ?? '') ?>" />
  </label>

  <label class="admin-field">
    <span class="admin-field__label">Excerpt (short summary)</span>
    <textarea name="excerpt" rows="2" class="admin-textarea"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
  </label>

  <label class="admin-field">
    <span class="admin-field__label">Cover image path</span>
    <input name="cover_image" class="admin-input" placeholder="assets/images/..." value="<?= htmlspecialchars($post['cover_image'] ?? '') ?>" />
  </label>

  <div class="admin-field">
    <span class="admin-field__label">Content</span>
    <div id="quillEditor" class="bg-white border border-line rounded-xl min-h-[280px]"><?= $post['content_html'] ?? '' ?></div>
  </div>

  <label class="admin-toggle">
    <input type="checkbox" name="is_published" value="1" <?= !empty($post['is_published']) ? 'checked' : '' ?> />
    <span class="text-sm font-bold text-ink">Published (visible on site)</span>
  </label>

  <label class="admin-field">
    <span class="admin-field__label">Publish date (optional)</span>
    <input type="datetime-local" name="published_at" value="<?= htmlspecialchars($publishedAtLocal) ?>" class="admin-input" />
  </label>

  <?php if (!empty($mail['notify_on_news'])): ?>
  <label class="admin-toggle">
    <input type="checkbox" name="notify_subscribers" value="1" <?= !empty($post['is_published']) ? '' : 'checked' ?> />
    <span class="text-sm font-bold text-ink">Email subscribers when saving (if published)</span>
  </label>
  <?php endif; ?>

  <div class="flex flex-wrap gap-2 pt-2">
    <button type="submit" class="admin-btn admin-btn--primary"><?= admin_icon('save') ?> Save post</button>
    <a href="<?= url('login') ?>?p=news" class="admin-btn admin-btn--ghost">Cancel</a>
  </div>
</form>

<script>
(function () {
  var form = document.getElementById('newsPostForm');
  var hidden = document.getElementById('contentHtml');
  var editorEl = document.getElementById('quillEditor');
  if (!form || !hidden || !editorEl || typeof Quill === 'undefined') return;

  var initial = editorEl.innerHTML;
  editorEl.innerHTML = '';

  var quill = new Quill(editorEl, {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [2, 3, false] }],
        ['bold', 'italic', 'underline'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['blockquote', 'link', 'image'],
        ['clean']
      ]
    }
  });

  if (initial && initial.trim() !== '') {
    quill.clipboard.dangerouslyPasteHTML(initial);
  }

  form.addEventListener('submit', function () {
    hidden.value = quill.root.innerHTML;
  });
})();
</script>
