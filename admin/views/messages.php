<?php
$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
?>
<div class="admin-intro">
  <p class="admin-intro__text">Messages sent from the contact form.</p>
</div>

<?php if (!$messages): ?>
  <p class="admin-card text-sm text-body">No messages yet.</p>
<?php else: ?>
  <div class="space-y-3 max-w-3xl">
    <?php foreach ($messages as $msg): ?>
      <article class="admin-card <?= $msg['is_read'] ? '' : 'ring-2 ring-blue/20' ?>">
        <div class="flex flex-wrap items-start justify-between gap-2">
          <div class="min-w-0">
            <h3 class="font-extrabold text-sm"><?= htmlspecialchars($msg['name']) ?></h3>
            <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-xs text-blue font-semibold"><?= htmlspecialchars($msg['email']) ?></a>
          </div>
          <p class="text-[11px] text-body shrink-0"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($msg['created_at']))) ?></p>
        </div>
        <?php if ($msg['subject']): ?>
          <p class="mt-2 text-xs font-bold text-ink">Subject: <?= htmlspecialchars($msg['subject']) ?></p>
        <?php endif; ?>
        <p class="mt-2 text-sm text-body whitespace-pre-wrap"><?= htmlspecialchars($msg['message']) ?></p>
        <div class="mt-3 flex flex-wrap gap-2">
          <?php if (!$msg['is_read']): ?>
            <form method="post" action="<?= url('login') ?>">
              <input type="hidden" name="action" value="message_read" />
              <input type="hidden" name="id" value="<?= (int) $msg['id'] ?>" />
              <button type="submit" class="admin-link text-mint">Mark read</button>
            </form>
          <?php endif; ?>
          <form method="post" action="<?= url('login') ?>" onsubmit="return confirm('Delete this message?');">
            <input type="hidden" name="action" value="message_delete" />
            <input type="hidden" name="id" value="<?= (int) $msg['id'] ?>" />
            <button type="submit" class="admin-link text-red-600">Delete</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
