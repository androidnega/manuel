(function () {
  var root = document.getElementById('updateCountdown');
  if (!root) {
    return;
  }

  var endsAt = root.getAttribute('data-ends-at');
  if (!endsAt) {
    return;
  }

  var end = new Date(endsAt).getTime();
  if (Number.isNaN(end)) {
    return;
  }

  var units = {
    days: root.querySelector('[data-unit="days"]'),
    hours: root.querySelector('[data-unit="hours"]'),
    minutes: root.querySelector('[data-unit="minutes"]'),
    seconds: root.querySelector('[data-unit="seconds"]'),
  };

  function pad(n) {
    return String(n).padStart(2, '0');
  }

  function tickUnit(el) {
    if (!el) {
      return;
    }
    el.classList.remove('is-tick');
    void el.offsetWidth;
    el.classList.add('is-tick');
    window.setTimeout(function () {
      el.classList.remove('is-tick');
    }, 320);
  }

  function render() {
    var diff = Math.max(0, end - Date.now());
    if (diff <= 0) {
      window.location.reload();
      return;
    }

    var totalSec = Math.floor(diff / 1000);
    var days = Math.floor(totalSec / 86400);
    var hours = Math.floor((totalSec % 86400) / 3600);
    var minutes = Math.floor((totalSec % 3600) / 60);
    var seconds = totalSec % 60;

    var next = {
      days: pad(days),
      hours: pad(hours),
      minutes: pad(minutes),
      seconds: pad(seconds),
    };

    Object.keys(units).forEach(function (key) {
      var el = units[key];
      if (el && el.textContent !== next[key]) {
        el.textContent = next[key];
        if (key === 'seconds' || key === 'minutes') {
          tickUnit(el);
        }
      }
    });
  }

  render();
  window.setInterval(render, 1000);
})();
