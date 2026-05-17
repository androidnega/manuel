(function () {
  'use strict';

  var items = document.querySelectorAll('.admin-hero-item[name="hero-slide"]');
  if (!items.length) {
    return;
  }

  items.forEach(function (details) {
    details.addEventListener('toggle', function () {
      if (!details.open || !details.id) {
        return;
      }
      var id = details.id.replace(/^slide-/, '');
      var base = window.location.pathname + window.location.search.split('&')[0].replace(/[&?]id=[^&]*/, '').replace(/[&?]new=1/, '');
      var sep = base.indexOf('?') >= 0 ? '&' : '?';
      if (!base.includes('p=homehero')) {
        return;
      }
      if (id === 'new') {
        window.history.replaceState(null, '', base + (base.indexOf('?') >= 0 ? '&' : '?') + 'new=1');
        return;
      }
      window.history.replaceState(null, '', base + sep + 'id=' + encodeURIComponent(id));
    });
  });
})();
