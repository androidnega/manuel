(function () {
  'use strict';

  function setOpen(item, open) {
    var btn = item.querySelector('[data-attachment-toggle]');
    var panel = item.querySelector('[data-attachment-panel]');
    var chevron = item.querySelector('[data-attachment-chevron]');
    if (!btn || !panel) {
      return;
    }
    panel.hidden = !open;
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    item.setAttribute('data-open', open ? 'true' : 'false');
    if (chevron) {
      chevron.style.transform = open ? 'rotate(180deg)' : '';
    }
  }

  document.addEventListener('click', function (event) {
    var btn = event.target.closest('[data-attachment-toggle]');
    if (!btn) {
      return;
    }

    var item = btn.closest('[data-attachment-item]');
    var panel = item ? item.querySelector('[data-attachment-panel]') : null;
    if (!item || !panel) {
      return;
    }

    var willOpen = panel.hidden;
    setOpen(item, willOpen);
  });
})();
