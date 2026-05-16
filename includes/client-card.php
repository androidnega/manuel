<?php
/** @var array $client */
$host = parse_url($client['url'], PHP_URL_HOST) ?: $client['url'];
?>
<a
  href="<?= htmlspecialchars($client['url']) ?>"
  target="_blank"
  rel="noopener noreferrer"
  class="client-card group flex flex-col h-full rounded-2xl border border-line bg-white p-4 sm:p-5 min-h-[128px] hover:border-blue/40 hover:shadow-sleek hover:-translate-y-1 transition-all duration-300 ease-out"
>
  <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-deep text-white group-hover:bg-blue group-hover:scale-105 transition-all duration-300">
    <?= icon($client['icon'] ?? 'globe', 'w-5 h-5') ?>
  </span>
  <h3 class="mt-4 text-sm font-extrabold text-ink group-hover:text-blue transition-colors"><?= htmlspecialchars($client['label']) ?></h3>
  <?php if (!empty($client['tag'])): ?>
    <p class="mt-0.5 text-[10px] font-bold text-body/80"><?= htmlspecialchars($client['tag']) ?></p>
  <?php endif; ?>
  <p class="mt-1 text-[11px] font-semibold text-body truncate"><?= htmlspecialchars($host) ?></p>
  <span class="mt-auto pt-3 inline-flex items-center gap-1.5 text-[11px] font-extrabold text-blue opacity-0 translate-y-1 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
    Visit site <?= icon('external-link', 'w-3.5 h-3.5') ?>
  </span>
</a>
