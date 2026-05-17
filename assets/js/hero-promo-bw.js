(function () {
  'use strict';

  var canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  if (!canHover) {
    return;
  }

  document.querySelectorAll('[data-hero-promo-bw]').forEach(function (stage) {
    var spotSize = 110;

    function setSpot(xPct, yPct, active) {
      stage.style.setProperty('--bw-x', xPct + '%');
      stage.style.setProperty('--bw-y', yPct + '%');
      stage.style.setProperty('--bw-spot-size', active ? spotSize + 'px' : '0px');
      stage.classList.toggle('is-active', active);
    }

    stage.addEventListener('mousemove', function (e) {
      var rect = stage.getBoundingClientRect();
      if (!rect.width || !rect.height) {
        return;
      }
      var x = ((e.clientX - rect.left) / rect.width) * 100;
      var y = ((e.clientY - rect.top) / rect.height) * 100;
      setSpot(x, y, true);
    });

    stage.addEventListener('mouseleave', function () {
      setSpot(50, 50, false);
    });
  });
})();
