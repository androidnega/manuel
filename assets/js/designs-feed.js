(function () {
  var items = document.querySelectorAll('.designs-masonry__item');
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (items.length) {
    if (reduced) {
      items.forEach(function (el) {
        el.classList.add('is-visible');
      });
    } else {
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var el = entry.target;
            var delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
            setTimeout(function () {
              el.classList.add('is-visible');
            }, delay);
            observer.unobserve(el);
          });
        },
        { threshold: 0.06, rootMargin: '0px 0px 10% 0px' }
      );

      items.forEach(function (el, i) {
        el.setAttribute('data-reveal-delay', String(Math.min((i % 10) * 60, 420)));
        observer.observe(el);
        if (el.getBoundingClientRect().top < window.innerHeight * 0.92) {
          el.classList.add('is-visible');
        }
      });
    }
  }

  var lightbox = document.getElementById('designsLightbox');
  if (!lightbox) return;

  var lbImg = lightbox.querySelector('[data-lightbox-img]');
  var lbTitle = lightbox.querySelector('[data-lightbox-title]');
  var lbType = lightbox.querySelector('[data-lightbox-type]');
  var triggers = document.querySelectorAll('[data-design-lightbox]');
  var lastFocus = null;

  var lbWa = lightbox.querySelector('[data-lightbox-wa]');

  function openLightbox(btn) {
    var src = btn.getAttribute('data-src');
    if (!src || !lbImg) return;
    lastFocus = document.activeElement;
    lbImg.src = src;
    lbImg.alt = btn.getAttribute('aria-label') || '';
    if (lbTitle) lbTitle.textContent = btn.getAttribute('data-title') || '';
    if (lbType) lbType.textContent = btn.getAttribute('data-type') || '';
    if (lbWa) {
      var wa = btn.getAttribute('data-wa-url') || '';
      lbWa.href = wa;
      lbWa.hidden = wa === '';
    }
    lightbox.hidden = false;
    lightbox.setAttribute('aria-hidden', 'false');
    requestAnimationFrame(function () {
      lightbox.classList.add('is-open');
    });
    document.body.style.overflow = 'hidden';
    var closeBtn = lightbox.querySelector('.designs-lightbox__close');
    if (closeBtn) closeBtn.focus();
  }

  function closeLightbox() {
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    setTimeout(function () {
      lightbox.hidden = true;
      if (lbImg) lbImg.src = '';
    }, 280);
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  triggers.forEach(function (btn) {
    btn.addEventListener('click', function () {
      openLightbox(btn);
    });
  });

  document.querySelectorAll('[data-design-wa]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.stopPropagation();
      if (navigator.share && link.closest('.designs-post__footer')) {
        var card = link.closest('.designs-post');
        var media = card ? card.querySelector('[data-design-lightbox]') : null;
        var shareUrl = media ? media.getAttribute('data-src') : window.location.href;
        var shareText = media ? media.getAttribute('data-share-text') : '';
        e.preventDefault();
        navigator.share({
          title: media ? media.getAttribute('data-title') : document.title,
          text: shareText,
          url: shareUrl,
        }).catch(function () {
          window.open(link.href, '_blank', 'noopener,noreferrer');
        });
      }
    });
  });

  lightbox.querySelectorAll('[data-lightbox-close]').forEach(function (el) {
    el.addEventListener('click', closeLightbox);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && lightbox.classList.contains('is-open')) {
      closeLightbox();
    }
  });
})();
