<section class="relative overflow-hidden bg-white soft-grid border-b border-line">
  <div class="absolute inset-0 bg-gradient-to-b from-white via-white/95 to-white"></div>
  <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 sm:pt-12 pb-5 sm:pb-6">
    <p class="reveal text-xs font-extrabold text-blue uppercase tracking-[0.2em]"><?= htmlspecialchars($heroLabel ?? '') ?></p>
    <h1 class="reveal reveal-delay-1 mt-2 text-3xl sm:text-4xl lg:text-[2.65rem] font-extrabold tracking-[-0.04em] leading-tight"><?= htmlspecialchars($heroTitle ?? '') ?></h1>
    <?php if (!empty($heroDesc)): ?>
      <p class="reveal reveal-delay-2 text-[0.9375rem] leading-relaxed mt-2.5 max-w-2xl text-body"><?= htmlspecialchars($heroDesc) ?></p>
    <?php endif; ?>
  </div>
</section>
