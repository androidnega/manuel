(function () {
  'use strict';

  var skipTypes = {
    password: true,
    hidden: true,
    file: true,
    checkbox: true,
    radio: true,
    number: true,
    range: true,
    color: true,
    submit: true,
    button: true,
    reset: true,
    datetime: true,
    'datetime-local': true,
    date: true,
    time: true,
    month: true,
    week: true,
  };

  var skipNames = {
    json: true,
    content_html: true,
    slug: true,
    cover_image: true,
    modal_image: true,
    image: true,
    class_group: true,
    attachment_closes_at: true,
    maintenance_ends_at: true,
    published_at: true,
    password: true,
    username: true,
    class_username: true,
    class_password: true,
    companies_json: true,
    existing_id: true,
  };

  function shouldUppercase(el) {
    if (!el || el.disabled || el.readOnly) {
      return false;
    }
    if (el.closest('[data-no-uppercase]')) {
      return false;
    }
    if (el.classList.contains('no-uppercase')) {
      return false;
    }
    var tag = el.tagName;
    if (tag !== 'INPUT' && tag !== 'TEXTAREA' && tag !== 'SELECT') {
      return false;
    }
    if (tag === 'SELECT') {
      return false;
    }
    var name = el.getAttribute('name') || '';
    if (skipNames[name]) {
      return false;
    }
    if (/_path$|_url$|_html$|_json$/i.test(name)) {
      return false;
    }
    var type = (el.getAttribute('type') || 'text').toLowerCase();
    if (skipTypes[type]) {
      return false;
    }
    return true;
  }

  function toUpper(el) {
    if (!shouldUppercase(el)) {
      return;
    }
    var val = el.value;
    var upper = val.toUpperCase();
    if (val === upper) {
      return;
    }
    var start = el.selectionStart;
    var end = el.selectionEnd;
    el.value = upper;
    if (typeof start === 'number' && typeof end === 'number') {
      el.setSelectionRange(start, end);
    }
  }

  function bindForm(form) {
    if (!form || form.dataset.noUppercase === '1') {
      return;
    }
    form.querySelectorAll('input, textarea').forEach(function (el) {
      if (!shouldUppercase(el)) {
        return;
      }
      el.addEventListener('input', function () {
        toUpper(el);
      });
      el.addEventListener('blur', function () {
        toUpper(el);
      });
      toUpper(el);
    });
    form.addEventListener('submit', function () {
      form.querySelectorAll('input, textarea').forEach(toUpper);
    });
  }

  document.querySelectorAll('form').forEach(bindForm);

  document.addEventListener('focusin', function (e) {
    if (e.target && shouldUppercase(e.target)) {
      toUpper(e.target);
    }
  });
})();
