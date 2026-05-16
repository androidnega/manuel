  <footer class="bg-white border-t border-line py-8 reveal">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <a href="<?= page_url('index.php') ?>" class="shrink-0 transition-opacity hover:opacity-90" aria-label="Manuelcode home">
          <?php $logoVariant = 'wordmark'; $logoTheme = 'light'; $showIcon = true; $showTagline = true; include __DIR__ . '/logo.php'; ?>
        </a>
        <nav class="flex flex-wrap gap-x-5 gap-y-2 text-xs font-bold text-body" aria-label="Footer">
          <?php foreach ($footerNav as $label => $href): ?>
            <a href="<?= page_url($href) ?>" class="<?= isCurrentPage($href) ? 'text-blue' : 'hover:text-ink' ?> transition-colors" <?= isCurrentPage($href) ? 'aria-current="page"' : '' ?>><?= htmlspecialchars($label) ?></a>
          <?php endforeach; ?>
        </nav>
        <a href="<?= htmlspecialchars($site['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-xs font-extrabold text-mint hover:text-ink transition-colors shrink-0">
          <?= icon('message', 'w-4 h-4') ?> WhatsApp
        </a>
      </div>
      <p class="mt-6 text-sm text-body text-center sm:text-left">© <?= date('Y') ?> Manuelcode. All rights reserved.</p>
    </div>
  </footer>
  <?php include __DIR__ . '/site-lock-foot.php'; ?>
  <script src="<?= asset('assets/js/theme.js') ?>"></script>
  <?php if (!empty($showHomeLoader)): ?>
  <script src="<?= asset('assets/js/home-loader.js') ?>"></script>
  <?php endif; ?>
  <?php if (!empty($pageScripts) && is_array($pageScripts)): foreach ($pageScripts as $scriptHref): ?>
  <script src="<?= asset($scriptHref) ?>"></script>
  <?php endforeach; endif; ?>
  <script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
