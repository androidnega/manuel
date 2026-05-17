(function () {
  var root = document.querySelector('[data-about-code-demo]');
  if (!root) return;

  var codeEl = root.querySelector('[data-code-target]');
  var outputEl = root.querySelector('[data-output-target]');
  var preview = root.querySelector('[data-code-preview]');
  var runBtn = root.querySelector('[data-code-run]');
  var runLabel = root.querySelector('[data-code-run-label]');
  var caret = root.querySelector('[data-code-caret]');
  var fileEl = root.querySelector('[data-code-file]');
  var gutterEl = root.querySelector('[data-code-gutter]');
  var editorWrap = root.querySelector('[data-code-editor-wrap]');
  var statusEl = root.querySelector('[data-code-status]');
  var dotsNav = root.querySelector('[data-code-dots]');
  if (!codeEl || !outputEl || !preview || !runBtn) return;

  var demos = [
    {
      file: 'attendance.py',
      source:
        '# Campus check-in\n' +
        'present = ["Ada", "Kofi", "Ama"]\n\n' +
        'for name in present:\n' +
        '    print(f"✓ {name} — checked in")',
      output: '✓ Ada — checked in\n✓ Kofi — checked in\n✓ Ama — checked in',
    },
    {
      file: 'inventory.py',
      source:
        'stock = {"laptops": 4, "cameras": 1, "mics": 6}\n' +
        'low = [item for item, qty in stock.items() if qty < 3]\n\n' +
        'print("Restock soon:", ", ".join(low))',
      output: 'Restock soon: cameras',
    },
    {
      file: 'quote_builder.py',
      source:
        'def estimate(hours, rate=45):\n' +
        '    return {"hours": hours, "total": hours * rate}\n\n' +
        'quote = estimate(12)\n' +
        'print(quote)',
      output: "{'hours': 12, 'total': 540}",
    },
    {
      file: 'notify.py',
      source:
        'subscribers = 128\n' +
        'title = "New project shipped"\n\n' +
        'print(f"Emailing {subscribers} people…")\n' +
        'print("Done — inbox updated.")',
      output: 'Emailing 128 people…\nDone — inbox updated.',
    },
    {
      file: 'resize_poster.py',
      source:
        'from PIL import Image\n\n' +
        'poster = Image.open("quote-poster.jpg")\n' +
        'poster.thumbnail((1200, 1200))\n' +
        'poster.save("quote-poster-web.jpg")\n' +
        'print("Export ready.")',
      output: 'Export ready.',
    },
  ];

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var typingMs = 24;
  var timer = null;
  var started = false;
  var demoIndex = 0;
  var current = null;

  function escapeHtml(str) {
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function highlightPython(code) {
    var html = escapeHtml(code);
    html = html.replace(/(#.*)$/gm, '<span class="tok-comment">$1</span>');
    html = html.replace(/("(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*')/g, '<span class="tok-string">$1</span>');
    html = html.replace(/\b(\d+)\b/g, '<span class="tok-number">$1</span>');
    html = html.replace(
      /\b(def|return|for|in|if|else|elif|import|from|class|True|False|None|and|or|not|with|as|while)\b/g,
      '<span class="tok-keyword">$1</span>'
    );
    html = html.replace(
      /\b(print|len|range|str|int|list|dict|join|open|save|thumbnail)\b/g,
      '<span class="tok-builtin">$1</span>'
    );
    return html;
  }

  function updateGutter(lineCount) {
    if (!gutterEl) return;
    var lines = [];
    for (var n = 1; n <= Math.max(lineCount, 1); n++) {
      lines.push(String(n));
    }
    gutterEl.textContent = lines.join('\n');
  }

  function updateDots() {
    if (!dotsNav) return;
    dotsNav.innerHTML = '';
    demos.forEach(function (_, i) {
      var dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'about-code-card__nav-dot' + (i === demoIndex ? ' is-active' : '');
      dot.setAttribute('aria-label', 'Snippet ' + (i + 1));
      dotsNav.appendChild(dot);
    });
  }

  function clearTimer() {
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }
  }

  function wait(ms, fn) {
    clearTimer();
    timer = setTimeout(fn, ms);
  }

  function setStatus(state) {
    if (!statusEl) return;
    statusEl.classList.remove('is-running', 'is-done');
    if (state === 'running') {
      statusEl.textContent = 'running…';
      statusEl.classList.add('is-running');
    } else if (state === 'done') {
      statusEl.textContent = 'exit 0';
      statusEl.classList.add('is-done');
    } else {
      statusEl.textContent = 'idle';
    }
  }

  function resetView() {
    codeEl.textContent = '';
    codeEl.innerHTML = '';
    outputEl.textContent = '';
    preview.classList.remove('is-visible');
    runBtn.classList.remove('is-pressing', 'is-done');
    if (runLabel) runLabel.textContent = 'Run';
    setStatus('idle');
    if (caret) caret.classList.remove('is-hidden');
    updateGutter(1);
    if (editorWrap) editorWrap.classList.remove('is-fading');
  }

  function loadDemo(index) {
    demoIndex = ((index % demos.length) + demos.length) % demos.length;
    current = demos[demoIndex];
    if (fileEl) {
      fileEl.classList.add('is-switching');
      fileEl.textContent = current.file;
      wait(120, function () {
        if (fileEl) fileEl.classList.remove('is-switching');
      });
    }
    updateDots();
  }

  function showStill() {
    loadDemo(demoIndex);
    codeEl.innerHTML = highlightPython(current.source);
    outputEl.textContent = current.output;
    preview.classList.add('is-visible');
    runBtn.classList.add('is-done');
    if (runLabel) runLabel.textContent = 'Ran';
    setStatus('done');
    updateGutter(current.source.split('\n').length);
    if (caret) caret.classList.add('is-hidden');
  }

  function typeCode(i) {
    if (!current) return;
    var source = current.source;
    var lineCount = source.slice(0, i + 1).split('\n').length;
    updateGutter(lineCount);

    if (i >= source.length) {
      if (caret) caret.classList.add('is-hidden');
      codeEl.innerHTML = highlightPython(source);
      wait(650, pressRun);
      return;
    }
    codeEl.textContent = source.slice(0, i + 1);
    wait(typingMs, function () {
      typeCode(i + 1);
    });
  }

  function pressRun() {
    if (runLabel) runLabel.textContent = 'Running…';
    runBtn.classList.add('is-pressing');
    setStatus('running');
    wait(200, function () {
      runBtn.classList.remove('is-pressing');
      runBtn.classList.add('is-done');
      if (runLabel) runLabel.textContent = 'Ran';
      preview.classList.add('is-visible');
      outputEl.textContent = '';
      setStatus('running');
      typeOutput(0);
    });
  }

  function typeOutput(i) {
    if (!current) return;
    var text = current.output;
    if (i >= text.length) {
      setStatus('done');
      wait(2800, fadeToNext);
      return;
    }
    outputEl.textContent = text.slice(0, i + 1);
    wait(16, function () {
      typeOutput(i + 1);
    });
  }

  function fadeToNext() {
    if (editorWrap) editorWrap.classList.add('is-fading');
    preview.classList.remove('is-visible');
    wait(320, function () {
      demoIndex = (demoIndex + 1) % demos.length;
      resetView();
      loadDemo(demoIndex);
      if (reduced) {
        showStill();
        wait(4000, fadeToNext);
        return;
      }
      wait(180, function () {
        typeCode(0);
      });
    });
  }

  function runCycle() {
    resetView();
    loadDemo(demoIndex);
    if (reduced) {
      showStill();
      wait(5000, function () {
        demoIndex = (demoIndex + 1) % demos.length;
        fadeToNext();
      });
      return;
    }
    typeCode(0);
  }

  function start() {
    if (started) return;
    started = true;
    updateDots();
    runCycle();
  }

  var io = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) start();
      });
    },
    { threshold: 0.25 }
  );
  io.observe(root);

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) clearTimer();
  });
})();
