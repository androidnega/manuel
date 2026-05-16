(function () {
  'use strict';

  function markLoaded(img) {
    img.classList.add('is-loaded');
  }

  function watch(img) {
    if (img.complete && img.naturalWidth > 0) {
      markLoaded(img);
      return;
    }
    img.addEventListener('load', function onLoad() {
      img.removeEventListener('load', onLoad);
      markLoaded(img);
    });
    img.addEventListener('error', function onError() {
      img.removeEventListener('error', onError);
      markLoaded(img);
    });
  }

  function init() {
    document.querySelectorAll('img[loading="lazy"], img.lazy-img').forEach(watch);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
