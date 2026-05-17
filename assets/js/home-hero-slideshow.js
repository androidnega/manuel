(function () {
  'use strict';

  var root = document.querySelector('[data-home-hero-slideshow]');
  if (!root) return;

  var slides = root.querySelectorAll('.home-hero-slideshow__slide');
  if (slides.length < 2) return;

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduced) return;

  var interval = parseInt(root.getAttribute('data-interval') || '180000', 10); // 180s default, matches CMS
  if (interval < 3000) interval = 180000;

  var index = 0;
  var timer = null;

  function show(next) {
    if (next === index) return;
    var current = slides[index];
    var target = slides[next];
    current.classList.remove('is-active');
    current.classList.add('is-leaving');
    target.classList.add('is-active');
    window.setTimeout(function () {
      current.classList.remove('is-leaving');
    }, 1100);
    index = next;
  }

  function next() {
    show((index + 1) % slides.length);
  }

  function start() {
    stop();
    timer = window.setInterval(next, interval);
  }

  function stop() {
    if (timer) {
      window.clearInterval(timer);
      timer = null;
    }
  }

  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', start);
  root.addEventListener('focusin', stop);
  root.addEventListener('focusout', start);

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) stop();
    else start();
  });

  start();
})();
