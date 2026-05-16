/**
 * Theme: light by default. Visitor can toggle to dark (saved in localStorage).
 */
(function () {
  var STORAGE_KEY = 'manuelcode-theme-mode';

  function getStoredMode() {
    try {
      var m = localStorage.getItem(STORAGE_KEY);
      if (m === 'dark') {
        return 'dark';
      }
      if (m === 'light') {
        return 'light';
      }
      if (m === 'auto') {
        setStoredMode('light');
      }
    } catch (e) {}
    return 'light';
  }

  function setStoredMode(mode) {
    try {
      localStorage.setItem(STORAGE_KEY, mode === 'dark' ? 'dark' : 'light');
    } catch (e) {}
  }

  function resolveTheme() {
    return getStoredMode();
  }

  function updateToggleButtons(theme) {
    var isDark = theme === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
      btn.setAttribute(
        'title',
        isDark ? 'Switch to light mode' : 'Switch to dark mode'
      );
      btn.setAttribute(
        'aria-label',
        isDark ? 'Switch to light mode' : 'Switch to dark mode'
      );
      var sun = btn.querySelector('[data-theme-icon="sun"]');
      var moon = btn.querySelector('[data-theme-icon="moon"]');
      if (sun) {
        sun.classList.toggle('hidden', isDark);
      }
      if (moon) {
        moon.classList.toggle('hidden', !isDark);
      }
    });
  }

  function applyTheme(theme) {
    var root = document.documentElement;
    root.setAttribute('data-theme', theme);
    var meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
      meta.setAttribute('content', theme === 'dark' ? '#0a111f' : '#0B1E3A');
    }
    updateToggleButtons(theme);
  }

  function refresh() {
    applyTheme(resolveTheme());
  }

  function toggle() {
    var next = resolveTheme() === 'dark' ? 'light' : 'dark';
    setStoredMode(next);
    applyTheme(next);
  }

  window.ManuelcodeTheme = {
    getMode: getStoredMode,
    setMode: function (mode) {
      setStoredMode(mode === 'dark' ? 'dark' : 'light');
      refresh();
    },
    toggle: toggle,
    refresh: refresh,
    apply: refresh,
  };

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-theme-toggle]')) {
      e.preventDefault();
      toggle();
    }
  });

  refresh();
})();
