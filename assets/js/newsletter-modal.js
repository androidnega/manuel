(function () {
  var root = document.getElementById('newsletterModal');
  if (!root) return;

  var scrollPct = parseInt(root.getAttribute('data-scroll') || '85', 10);
  var storageKey = 'mc_nl_dismissed';
  var shown = false;

  if (localStorage.getItem(storageKey) === '1') {
    return;
  }

  var backdrop = root.querySelector('[data-nl-close]');
  var closeBtn = root.querySelector('.nl-modal__close');
  var form = root.getElementById('newsletterForm');
  var msg = root.querySelector('.nl-modal__msg');
  var submitBtn = form ? form.querySelector('.nl-modal__submit') : null;

  function openModal() {
    if (shown) return;
    shown = true;
    root.classList.add('is-open');
    root.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(persist) {
    root.classList.remove('is-open');
    root.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (persist) {
      localStorage.setItem(storageKey, '1');
    }
  }

  function onScroll() {
    var doc = document.documentElement;
    var scrollTop = window.scrollY || doc.scrollTop;
    var max = doc.scrollHeight - window.innerHeight;
    if (max <= 0) return;
    var pct = (scrollTop / max) * 100;
    if (pct >= scrollPct) {
      openModal();
      window.removeEventListener('scroll', onScroll, { passive: true });
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  if (backdrop) backdrop.addEventListener('click', function () { closeModal(true); });
  if (closeBtn) closeBtn.addEventListener('click', function () { closeModal(true); });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && root.classList.contains('is-open')) {
      closeModal(true);
    }
  });

  if (!form) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!submitBtn) return;
    var emailInput = form.querySelector('input[name="email"]');
    var email = emailInput ? emailInput.value.trim() : '';
    if (!email) {
      if (msg) {
        msg.textContent = 'Please enter your email.';
        msg.className = 'nl-modal__msg is-err';
      }
      return;
    }

    submitBtn.disabled = true;
    if (msg) {
      msg.textContent = '';
      msg.className = 'nl-modal__msg';
    }

    var action = form.getAttribute('action') || '';
    var body = new FormData();
    body.append('email', email);

    fetch(action, { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (msg) {
          msg.textContent = data.message || (data.ok ? 'Subscribed!' : 'Something went wrong.');
          msg.className = 'nl-modal__msg ' + (data.ok ? 'is-ok' : 'is-err');
        }
        if (data.ok) {
          if (emailInput) emailInput.value = '';
          setTimeout(function () { closeModal(true); }, 2200);
        }
      })
      .catch(function () {
        if (msg) {
          msg.textContent = 'Could not subscribe. Try again later.';
          msg.className = 'nl-modal__msg is-err';
        }
      })
      .finally(function () {
        submitBtn.disabled = false;
      });
  });
})();
