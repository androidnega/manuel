(function () {
  'use strict';

  var opts = { passive: false };

  document.addEventListener(
    'gesturestart',
    function (e) {
      e.preventDefault();
    },
    opts
  );

  document.addEventListener(
    'gesturechange',
    function (e) {
      e.preventDefault();
    },
    opts
  );

  document.addEventListener(
    'gestureend',
    function (e) {
      e.preventDefault();
    },
    opts
  );

  document.addEventListener(
    'touchmove',
    function (e) {
      if (e.scale !== undefined && e.scale !== 1) {
        e.preventDefault();
      }
    },
    opts
  );

  document.addEventListener(
    'wheel',
    function (e) {
      if (e.ctrlKey) {
        e.preventDefault();
      }
    },
    opts
  );

  document.addEventListener(
    'keydown',
    function (e) {
      if (
        (e.ctrlKey || e.metaKey) &&
        (e.key === '+' || e.key === '-' || e.key === '=' || e.key === '0')
      ) {
        e.preventDefault();
      }
    },
    opts
  );
})();
