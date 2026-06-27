(function () {
  'use strict';

  var btn = document.getElementById('backToTop');
  if (!btn) {
    return;
  }

  var showAfter = 420;
  var ticking = false;
  var minWater = 26;
  var maxWater = 82;

  function scrollProgress() {
    var docHeight = document.documentElement.scrollHeight - window.innerHeight;
    if (docHeight <= 0) {
      return 0;
    }
    return Math.min(1, Math.max(0, window.scrollY / docHeight));
  }

  function updateWaterLevel() {
    var progress = scrollProgress();
    var level = minWater + (maxWater - minWater) * progress;
    btn.style.setProperty('--btt-water-level', level.toFixed(1) + '%');
  }

  function toggleVisible() {
    var show = window.scrollY > showAfter;
    btn.classList.toggle('is-visible', show);
    btn.setAttribute('aria-hidden', show ? 'false' : 'true');
    btn.tabIndex = show ? 0 : -1;
  }

  function onScroll() {
    if (ticking) {
      return;
    }
    ticking = true;
    requestAnimationFrame(function () {
      toggleVisible();
      updateWaterLevel();
      ticking = false;
    });
  }

  function scrollToTop() {
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: prefersReduced ? 'auto' : 'smooth',
    });
  }

  function ripple() {
    btn.classList.remove('is-ripple');
    void btn.offsetWidth;
    btn.classList.add('is-ripple');
    window.setTimeout(function () {
      btn.classList.remove('is-ripple');
    }, 700);
  }

  btn.setAttribute('aria-hidden', 'true');
  btn.tabIndex = -1;

  btn.addEventListener('click', function () {
    ripple();
    scrollToTop();
  });

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });
  toggleVisible();
  updateWaterLevel();
})();
