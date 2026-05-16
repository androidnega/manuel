(function () {
  var sidebar = document.getElementById('adminSidebar');
  var overlay = document.getElementById('adminOverlay');
  var menuBtn = document.getElementById('adminMenuBtn');

  if (!sidebar || !overlay || !menuBtn) {
    return;
  }

  function openNav() {
    sidebar.classList.add('is-open');
    overlay.classList.add('is-open');
    document.body.classList.add('admin-shell-lock');
    menuBtn.setAttribute('aria-expanded', 'true');
  }

  function closeNav() {
    sidebar.classList.remove('is-open');
    overlay.classList.remove('is-open');
    document.body.classList.remove('admin-shell-lock');
    menuBtn.setAttribute('aria-expanded', 'false');
  }

  menuBtn.addEventListener('click', function () {
    if (sidebar.classList.contains('is-open')) {
      closeNav();
    } else {
      openNav();
    }
  });

  overlay.addEventListener('click', closeNav);

  sidebar.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      if (window.matchMedia('(max-width: 1023px)').matches) {
        closeNav();
      }
    });
  });

  window.addEventListener('resize', function () {
    if (window.matchMedia('(min-width: 1024px)').matches) {
      closeNav();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeNav();
    }
  });
})();
