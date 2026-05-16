<article class="rounded-2xl overflow-hidden bg-cloud border border-line">
  <?php if (!empty($d['image'])): ?>
    <?php
    $src = $d['image'];
    $alt = $d['title'];
    $fit = $d['fit'] ?? 'poster';
    $frameClass = 'rounded-t-2xl';
    include __DIR__ . '/media.php';
    ?>
  <?php elseif (($d['variant'] ?? '') === 'campaign'): ?>
    <div class="aspect-[4/3] max-h-44 bg-blue p-5 text-white flex items-end">
      <div>
        <p class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-white/70">Brand</p>
        <h3 class="mt-2 text-2xl font-extrabold leading-none">The FASA Dream</h3>
      </div>
    </div>
  <?php elseif (($d['variant'] ?? '') === 'ui'): ?>
    <div class="aspect-[4/3] max-h-44 bg-cloud p-5 flex items-end border-b border-line">
      <div>
        <?= icon('laptop', 'w-8 h-8 text-blue') ?>
        <h3 class="mt-4 text-2xl font-extrabold leading-none">Web UI</h3>
      </div>
    </div>
  <?php else: ?>
    <div class="aspect-[4/3] max-h-44 bg-deep p-5 text-white flex items-end">
      <div>
        <p class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-white/60">Manuelcode</p>
        <h3 class="mt-2 text-2xl font-extrabold leading-none">Design. Build. Deliver.</h3>
      </div>
    </div>
  <?php endif; ?>
  <div class="p-5 sm:p-6">
    <h3 class="font-extrabold text-sm sm:text-base"><?= htmlspecialchars($d['title']) ?></h3>
    <p class="text-xs sm:text-sm text-body mt-1"><?= htmlspecialchars($d['type']) ?></p>
  </div>
</article>
