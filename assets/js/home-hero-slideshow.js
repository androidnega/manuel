(function () {
  'use strict';

  function initHomeHeroSlideshow() {
    var root = document.querySelector('[data-home-hero-slideshow]');
    if (!root) {
      return;
    }

    var slides = root.querySelectorAll('.home-hero-slideshow__slide');
    if (slides.length < 2) {
      return;
    }

    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced) {
      root.classList.add('home-hero-slideshow--reduced');
    }

    var interval = parseInt(root.getAttribute('data-interval') || '6000', 10);
    if (!interval || interval < 3000) {
      interval = 6000;
    }

    var index = 0;
    var timer = null;
    var leaveMs = reduced ? 350 : 1100;

    function show(next) {
      if (next === index || next < 0 || next >= slides.length) {
        return;
      }
      var current = slides[index];
      var target = slides[next];
      current.classList.remove('is-active');
      current.classList.add('is-leaving');
      target.classList.add('is-active');
      window.setTimeout(function () {
        current.classList.remove('is-leaving');
      }, leaveMs);
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

    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        stop();
      } else {
        start();
      }
    });

    start();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHomeHeroSlideshow);
  } else {
    initHomeHeroSlideshow();
  }
})();
