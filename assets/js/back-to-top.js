(function () {
  'use strict';

  var btn = document.getElementById('backToTop');
  if (!btn) {
    return;
  }

  var showAfter = 420;
  var ticking = false;

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
  toggleVisible();
})();
