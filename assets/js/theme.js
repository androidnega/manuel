/**
 * Theme: auto (6pm–6am dark) unless visitor picks light/dark (localStorage).
 */
(function () {
  var STORAGE_KEY = 'manuelcode-theme-mode';

  function getStoredMode() {
    try {
      var m = localStorage.getItem(STORAGE_KEY);
      if (m === 'light' || m === 'dark' || m === 'auto') {
        return m;
      }
    } catch (e) {}
    return 'auto';
  }

  function setStoredMode(mode) {
    try {
      localStorage.setItem(STORAGE_KEY, mode);
    } catch (e) {}
  }

  function isDarkTime(date) {
    var d = date || new Date();
    var h = d.getHours();
    return h >= 18 || h < 6;
  }

  function resolveTheme(mode) {
    mode = mode || getStoredMode();
    if (mode === 'light') {
      return 'light';
    }
    if (mode === 'dark') {
      return 'dark';
    }
    return isDarkTime() ? 'dark' : 'light';
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
    window.clearTimeout(window.__themeSwitchTimer);
    applyTheme(next);
  }

  function msUntilNextSwitch() {
    var now = new Date();
    var next = new Date(now);

    if (isDarkTime(now)) {
      next.setHours(6, 0, 0, 0);
      if (now.getHours() >= 18) {
        next.setDate(next.getDate() + 1);
      }
    } else {
      next.setHours(18, 0, 0, 0);
    }

    var ms = next.getTime() - now.getTime();
    return ms <= 0 ? 1000 : ms;
  }

  function scheduleThemeSwitch() {
    if (getStoredMode() !== 'auto') {
      return;
    }
    window.clearTimeout(window.__themeSwitchTimer);
    window.__themeSwitchTimer = window.setTimeout(function () {
      refresh();
      scheduleThemeSwitch();
    }, msUntilNextSwitch());
  }

  window.ManuelcodeTheme = {
    getMode: getStoredMode,
    setMode: function (mode) {
      if (mode !== 'light' && mode !== 'dark' && mode !== 'auto') {
        return;
      }
      setStoredMode(mode);
      refresh();
      window.clearTimeout(window.__themeSwitchTimer);
      if (mode === 'auto') {
        scheduleThemeSwitch();
      }
    },
    toggle: toggle,
    refresh: refresh,
    isDarkTime: isDarkTime,
    apply: refresh,
  };

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-theme-toggle]')) {
      e.preventDefault();
      toggle();
    }
  });

  refresh();
  scheduleThemeSwitch();

  document.addEventListener('visibilitychange', function () {
    if (!document.hidden && getStoredMode() === 'auto') {
      refresh();
      scheduleThemeSwitch();
    }
  });
})();
