(function () {
  'use strict';

  function shouldHide(args) {
    var msg = args[0];
    if (msg == null) {
      return false;
    }
    var text = typeof msg === 'string' ? msg : String(msg);
    return text.indexOf('cdn.tailwindcss.com') !== -1 || text.indexOf('should not be used in production') !== -1;
  }

  ['warn', 'error'].forEach(function (level) {
    var original = console[level];
    if (typeof original !== 'function') {
      return;
    }
    console[level] = function () {
      if (shouldHide(arguments)) {
        return;
      }
      return original.apply(console, arguments);
    };
  });
})();
