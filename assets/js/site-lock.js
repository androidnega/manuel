(function () {
  'use strict';

  var opts = { passive: false };

  function block(e) {
    e.preventDefault();
  }

  ['gesturestart', 'gesturechange', 'gestureend'].forEach(function (type) {
    document.addEventListener(type, block, opts);
  });

  document.addEventListener(
    'touchstart',
    function (e) {
      if (e.touches.length > 1) {
        e.preventDefault();
      }
    },
    opts
  );

  document.addEventListener(
    'touchmove',
    function (e) {
      if (e.touches.length > 1) {
        e.preventDefault();
        return;
      }
      if (e.scale !== undefined && e.scale !== 1) {
        e.preventDefault();
      }
    },
    opts
  );

  document.addEventListener(
    'wheel',
    function (e) {
      if (e.ctrlKey || e.metaKey) {
        e.preventDefault();
      }
    },
    opts
  );

  document.addEventListener(
    'keydown',
    function (e) {
      if (!(e.ctrlKey || e.metaKey)) {
        return;
      }
      var k = e.key;
      if (
        k === '+' ||
        k === '-' ||
        k === '=' ||
        k === '0' ||
        k === '_' ||
        k === 'Add' ||
        k === 'Subtract'
      ) {
        e.preventDefault();
      }
    },
    opts
  );

})();
