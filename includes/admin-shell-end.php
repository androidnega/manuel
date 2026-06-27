    </main>
  </div>
</div>
<?php include __DIR__ . '/site-lock-foot.php'; ?>
<script src="<?= asset('assets/js/admin.js') ?>"></script>
<?php if (($view ?? '') === 'attachments'): ?>
<script src="<?= asset('assets/js/admin-attachments.js') ?>"></script>
<?php endif; ?>
<script src="<?= asset('assets/js/form-uppercase.js') ?>"></script>
