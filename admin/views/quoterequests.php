<?php
$requests = $pdo->query('SELECT * FROM quote_requests ORDER BY created_at DESC')->fetchAll();
?>
<div class="admin-intro">
  <p class="admin-intro__text">Project quote requests submitted from the Quotes page.</p>
</div>

<?php if (!$requests): ?>
  <p class="admin-card text-sm text-body">No quote requests yet.</p>
<?php else: ?>
  <div class="space-y-3 max-w-3xl">
    <?php foreach ($requests as $req): ?>
      <article class="admin-card <?= $req['is_read'] ? '' : 'ring-2 ring-blue/20' ?>">
        <div class="flex flex-wrap items-start justify-between gap-2">
          <div class="min-w-0">
            <h3 class="font-extrabold text-sm"><?= htmlspecialchars($req['project_name']) ?></h3>
            <p class="text-xs text-body mt-0.5">
              <?= htmlspecialchars($req['name']) ?> ·
              <a href="mailto:<?= htmlspecialchars($req['email']) ?>" class="text-blue font-semibold"><?= htmlspecialchars($req['email']) ?></a>
            </p>
          </div>
          <p class="text-[11px] text-body shrink-0"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($req['created_at']))) ?></p>
        </div>
        <dl class="mt-3 grid sm:grid-cols-2 gap-2 text-xs">
          <div><dt class="font-bold text-body">Type</dt><dd class="text-ink"><?= htmlspecialchars($req['project_type']) ?></dd></div>
          <div><dt class="font-bold text-body">Budget</dt><dd class="text-ink"><?= htmlspecialchars($req['budget_range']) ?></dd></div>
          <div><dt class="font-bold text-body">Timeline</dt><dd class="text-ink"><?= htmlspecialchars($req['timeline']) ?></dd></div>
          <?php if ($req['phone']): ?><div><dt class="font-bold text-body">Phone</dt><dd class="text-ink"><?= htmlspecialchars($req['phone']) ?></dd></div><?php endif; ?>
          <?php if ($req['organization']): ?><div><dt class="font-bold text-body">Organization</dt><dd class="text-ink"><?= htmlspecialchars($req['organization']) ?></dd></div><?php endif; ?>
          <?php if ($req['referral']): ?><div class="sm:col-span-2"><dt class="font-bold text-body">Referral</dt><dd class="text-ink"><?= htmlspecialchars($req['referral']) ?></dd></div><?php endif; ?>
        </dl>
        <p class="mt-3 text-sm text-body whitespace-pre-wrap border-t border-line pt-3"><?= htmlspecialchars($req['description']) ?></p>
        <div class="mt-3 flex flex-wrap gap-2">
          <?php if (!$req['is_read']): ?>
            <form method="post" action="<?= url('login') ?>">
              <input type="hidden" name="action" value="quote_read" />
              <input type="hidden" name="id" value="<?= (int) $req['id'] ?>" />
              <button type="submit" class="admin-link text-mint">Mark read</button>
            </form>
          <?php endif; ?>
          <form method="post" action="<?= url('login') ?>" onsubmit="return confirm('Delete this quote request?');">
            <input type="hidden" name="action" value="quote_delete" />
            <input type="hidden" name="id" value="<?= (int) $req['id'] ?>" />
            <button type="submit" class="admin-link text-red-600">Delete</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
