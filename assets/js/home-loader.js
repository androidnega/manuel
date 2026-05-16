(function () {
  'use strict';

  var loader = document.getElementById('homeLoader');
  if (!loader) {
    return;
  }

  var fill = document.getElementById('homeLoaderFill');
  var bar = document.getElementById('homeLoaderBar');
  var pctEl = document.getElementById('homeLoaderPct');
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var progress = 0;
  var done = false;
  var tickTimer = null;

  function setProgress(value) {
    progress = Math.min(100, Math.max(0, value));
    if (fill) {
      fill.style.width = progress + '%';
      if (progress >= 100) {
        fill.classList.add('is-complete');
      }
    }
    if (pctEl) {
      pctEl.textContent = String(Math.round(progress));
    }
    if (bar) {
      bar.setAttribute('aria-valuenow', String(Math.round(progress)));
    }
  }

  function finish() {
    if (done) {
      return;
    }
    done = true;
    window.clearInterval(tickTimer);
    setProgress(100);

    window.setTimeout(function () {
      loader.classList.add('is-done');
      document.body.classList.remove('is-home-loading');
      window.setTimeout(function () {
        loader.remove();
      }, reduced ? 0 : 560);
    }, reduced ? 80 : 320);
  }

  document.body.classList.add('is-home-loading');

  if (reduced) {
    setProgress(100);
    finish();
    return;
  }

  setProgress(8);

  tickTimer = window.setInterval(function () {
    if (done) {
      return;
    }
    var cap = document.readyState === 'complete' ? 96 : 88;
    if (progress < cap) {
      var step = progress < 40 ? 4 + Math.random() * 6 : 2 + Math.random() * 4;
      setProgress(progress + step);
    }
  }, 90);

  if (document.readyState === 'complete') {
    finish();
  } else {
    window.addEventListener('load', finish);
    window.setTimeout(finish, 8000);
  }
})();
