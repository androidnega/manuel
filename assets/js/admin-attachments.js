(function () {
  'use strict';

  document.querySelectorAll('[data-attachment-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('[data-attachment-item]');
      var panel = item ? item.querySelector('[data-attachment-panel]') : null;
      if (!panel) {
        return;
      }
      var isOpen = item.classList.contains('is-open');

      document.querySelectorAll('[data-attachment-item]').forEach(function (other) {
        other.classList.remove('is-open');
        var otherBtn = other.querySelector('[data-attachment-toggle]');
        var otherPanel = other.querySelector('[data-attachment-panel]');
        if (otherPanel) {
          otherPanel.classList.add('hidden');
        }
        if (otherBtn) {
          otherBtn.setAttribute('aria-expanded', 'false');
        }
      });

      if (!isOpen) {
        panel.classList.remove('hidden');
        item.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });
})();
