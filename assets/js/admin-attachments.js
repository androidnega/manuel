(function () {
  'use strict';

  document.querySelectorAll('[data-attachment-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('[data-attachment-item]');
      var panel = item ? item.querySelector('.admin-attachments-item__detail') : null;
      if (!panel) {
        return;
      }
      var isOpen = !panel.hidden;

      document.querySelectorAll('[data-attachment-item]').forEach(function (other) {
        other.classList.remove('is-open');
        var otherBtn = other.querySelector('[data-attachment-toggle]');
        var otherPanel = other.querySelector('.admin-attachments-item__detail');
        if (otherPanel) {
          otherPanel.hidden = true;
        }
        if (otherBtn) {
          otherBtn.setAttribute('aria-expanded', 'false');
        }
      });

      if (!isOpen) {
        panel.hidden = false;
        item.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });
})();
