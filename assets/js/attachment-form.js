(function () {
  'use strict';

  var form = document.getElementById('attachment-form');
  if (!form) {
    return;
  }

  var MAX = 3;
  var companies = [];
  var returningMode = false;
  var lookupTimer = null;
  var lookupRequest = null;

  var classSelect = form.querySelector('[name="class_group"]');
  var indexInput = form.querySelector('[name="index_number"]');
  var fullNameInput = form.querySelector('[name="full_name"]');
  var contactInput = form.querySelector('[name="contact"]');
  var existingIdInput = document.getElementById('existing_id');
  var companiesJsonInput = document.getElementById('companies_json');
  var tagInput = document.getElementById('company-tag-input');
  var tagsWrap = document.getElementById('company-tags');
  var detailsWrap = document.getElementById('company-details-list');
  var personalFields = document.getElementById('attachment-personal-fields');
  var companiesSection = document.getElementById('attachment-companies-section');
  var lookupStatus = document.getElementById('attachment-lookup-status');
  var companiesHint = document.getElementById('companies-hint');
  var submitBtn = document.getElementById('attachment-submit-btn');

  function upper(value) {
    return String(value || '').toUpperCase();
  }

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function activeCount() {
    return companies.filter(function (c) {
      return c.name;
    }).length;
  }

  function slotsRemaining() {
    return Math.max(0, MAX - activeCount());
  }

  function canAddMore() {
    return activeCount() < MAX;
  }

  function setHint() {
    if (!companiesHint) {
      return;
    }
    var remaining = slotsRemaining();
    if (returningMode) {
      if (remaining === 0) {
        companiesHint.textContent = 'You have registered the maximum of ' + MAX + ' companies.';
      } else {
        companiesHint.textContent = 'Add up to ' + remaining + ' more optional compan' + (remaining === 1 ? 'y' : 'ies') + '. Type a name and press comma to add.';
      }
    } else if (activeCount() === 0) {
      companiesHint.textContent = 'Add at least one company (max ' + MAX + '). Type a company name and press comma to add it.';
    } else if (remaining === 0) {
      companiesHint.textContent = 'Maximum of ' + MAX + ' companies reached.';
    } else {
      companiesHint.textContent = 'First company required. You can add ' + remaining + ' more optional compan' + (remaining === 1 ? 'y' : 'ies') + '. Press comma after each name.';
    }
  }

  function setLookupStatus(html, tone) {
    if (!lookupStatus) {
      return;
    }
    lookupStatus.innerHTML = html;
    lookupStatus.className = 'rounded-2xl px-5 py-4 text-sm font-semibold reveal ' + (tone || '');
    lookupStatus.hidden = !html;
  }

  function syncHiddenJson() {
    if (!companiesJsonInput) {
      return;
    }
    var payload = companies
      .filter(function (c) {
        return c.name;
      })
      .map(function (c) {
        return {
          name: upper(c.name),
          location: upper(c.location),
          official_position: upper(c.official_position),
        };
      });
    companiesJsonInput.value = JSON.stringify(payload);
  }

  function renderTags() {
    if (!tagsWrap || !tagInput) {
      return;
    }
    tagsWrap.innerHTML = companies
      .map(function (company, index) {
        if (!company.name) {
          return '';
        }
        var removeBtn = company.locked
          ? ''
          : '<button type="button" class="company-tag-remove ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full text-body hover:bg-line hover:text-ink" data-remove-index="' + index + '" aria-label="Remove company">&times;</button>';
        return (
          '<span class="company-tag inline-flex items-center gap-1 rounded-lg border border-line bg-cloud px-2.5 py-1 text-xs font-bold uppercase text-ink' +
          (company.locked ? ' opacity-80' : '') +
          '" data-tag-index="' +
          index +
          '">' +
          escapeHtml(upper(company.name)) +
          removeBtn +
          '</span>'
        );
      })
      .join('');

    var tagFieldWrap = document.getElementById('company-tag-field');
    if (tagFieldWrap) {
      tagFieldWrap.hidden = !canAddMore();
    }
    tagInput.disabled = !canAddMore();
    tagInput.placeholder = canAddMore()
      ? returningMode && activeCount() > 0
        ? 'Another company name (optional)'
        : activeCount() === 0
          ? 'Company name — press comma to add'
          : 'Another company name (optional)'
      : 'Maximum companies reached';

    setHint();
    syncHiddenJson();
  }

  function renderDetails() {
    if (!detailsWrap) {
      return;
    }
    detailsWrap.innerHTML = companies
      .map(function (company, index) {
        if (!company.name) {
          return '';
        }
        var locked = !!company.locked;
        var disabledAttr = locked ? ' disabled readonly' : '';
        var cardClass = locked ? 'border-line bg-white/80' : 'border-blue/20 bg-cloud/60';
        return (
          '<div class="company-detail-card rounded-xl border p-4 ' +
          cardClass +
          '" data-detail-index="' +
          index +
          '">' +
          '<p class="text-xs font-extrabold uppercase tracking-wide text-blue">Company ' +
          (index + 1) +
          (locked ? ' · registered' : '') +
          '</p>' +
          '<p class="mt-1 text-sm font-bold uppercase text-ink">' +
          escapeHtml(upper(company.name)) +
          '</p>' +
          '<div class="mt-3 grid gap-3 sm:grid-cols-2">' +
          '<div><label class="text-xs font-bold text-body">Location' +
          (locked ? '' : ' *') +
          '</label><input class="company-detail-input mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm uppercase outline-none focus:border-blue focus:ring-2 focus:ring-blue/10" data-field="location" data-index="' +
          index +
          '" value="' +
          escapeHtml(upper(company.location)) +
          '"' +
          disabledAttr +
          ' placeholder="e.g. ACCRA" /></div>' +
          '<div><label class="text-xs font-bold text-body">Official\'s position' +
          (locked ? '' : ' *') +
          '</label><input class="company-detail-input mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm uppercase outline-none focus:border-blue focus:ring-2 focus:ring-blue/10" data-field="official_position" data-index="' +
          index +
          '" value="' +
          escapeHtml(upper(company.official_position)) +
          '"' +
          disabledAttr +
          ' placeholder="e.g. IT MANAGER" /></div>' +
          '</div></div>'
        );
      })
      .join('');
  }

  function renderAll() {
    renderTags();
    renderDetails();
    if (submitBtn) {
      var hasNewCompany = companies.some(function (c) {
        return c.name && !c.locked;
      });
      if (returningMode) {
        submitBtn.textContent = hasNewCompany ? 'Save additional companies' : 'Add a company to continue';
        submitBtn.disabled = !hasNewCompany;
      } else {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit registration';
      }
    }
  }

  function addCompany(name) {
    name = upper(name.replace(/,/g, '').trim());
    if (!name || !canAddMore()) {
      return false;
    }
    var duplicate = companies.some(function (c) {
      return upper(c.name) === name;
    });
    if (duplicate) {
      return false;
    }
    companies.push({ name: name, location: '', official_position: '', locked: false });
    renderAll();
    var newIndex = companies.length - 1;
    window.setTimeout(function () {
      var input = detailsWrap.querySelector('[data-field="location"][data-index="' + newIndex + '"]');
      if (input) {
        input.focus();
      }
    }, 0);
    return true;
  }

  function removeCompany(index) {
    var company = companies[index];
    if (!company || company.locked) {
      return;
    }
    companies.splice(index, 1);
    renderAll();
  }

  function resetReturningMode() {
    returningMode = false;
    if (existingIdInput) {
      existingIdInput.value = '';
    }
    if (personalFields) {
      personalFields.hidden = false;
    }
    if (fullNameInput) {
      fullNameInput.readOnly = false;
      fullNameInput.required = true;
    }
    if (contactInput) {
      contactInput.readOnly = false;
      contactInput.required = true;
    }
    companies = companies.filter(function (c) {
      return !c.locked;
    });
    setLookupStatus('', '');
    renderAll();
  }

  function applyReturningRecord(data) {
    returningMode = true;
    if (existingIdInput) {
      existingIdInput.value = String(data.id || '');
    }
    if (fullNameInput) {
      fullNameInput.value = data.full_name || '';
      fullNameInput.readOnly = true;
      fullNameInput.required = false;
    }
    if (contactInput) {
      contactInput.value = data.contact || '';
      contactInput.readOnly = true;
      contactInput.required = false;
    }
    if (personalFields) {
      personalFields.hidden = true;
    }

    companies = (data.companies || []).map(function (company) {
      return {
        name: company.name || '',
        location: company.location || '',
        official_position: company.official_position || '',
        locked: true,
      };
    });

    var remaining = data.slots_remaining || 0;
    var name = upper(data.full_name || '');
    if (remaining === 0) {
      setLookupStatus(
        '<p>Welcome back, <span class="text-ink">' +
          escapeHtml(name) +
          '</span>. You already have ' +
          MAX +
          ' companies registered.</p>',
        'bg-cloud border border-line text-body'
      );
    } else {
      setLookupStatus(
        '<p>Welcome back, <span class="text-ink">' +
          escapeHtml(name) +
          '</span>. Add up to <span class="text-ink">' +
          remaining +
          '</span> more optional compan' +
          (remaining === 1 ? 'y' : 'ies') +
          ' below.</p>',
        'bg-blue/10 border border-blue/20 text-body'
      );
    }

    renderAll();
    if (canAddMore() && tagInput) {
      tagInput.focus();
    }
  }

  function lookupExisting() {
    if (!classSelect || !indexInput) {
      return;
    }
    var classGroup = classSelect.value;
    var indexNumber = upper(indexInput.value.trim());
    indexInput.value = indexNumber;

    if (lookupRequest) {
      lookupRequest.abort();
      lookupRequest = null;
    }

    if (classGroup === '' || indexNumber === '') {
      if (returningMode) {
        resetReturningMode();
      }
      return;
    }

    var url =
      form.getAttribute('data-lookup-url') +
      '?class_group=' +
      encodeURIComponent(classGroup) +
      '&index_number=' +
      encodeURIComponent(indexNumber);

    lookupRequest = new AbortController();
    fetch(url, { signal: lookupRequest.signal, credentials: 'same-origin' })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        lookupRequest = null;
        if (!data || !data.ok) {
          return;
        }
        if (data.found) {
          applyReturningRecord(data);
        } else if (returningMode) {
          resetReturningMode();
        }
      })
      .catch(function (err) {
        if (err && err.name === 'AbortError') {
          return;
        }
        lookupRequest = null;
      });
  }

  function scheduleLookup() {
    window.clearTimeout(lookupTimer);
    lookupTimer = window.setTimeout(lookupExisting, 350);
  }

  if (tagInput) {
    tagInput.addEventListener('keydown', function (e) {
      if (e.key === ',' || e.key === 'Enter') {
        e.preventDefault();
        if (addCompany(tagInput.value)) {
          tagInput.value = '';
        }
      } else if (e.key === 'Backspace' && tagInput.value === '') {
        for (var i = companies.length - 1; i >= 0; i--) {
          if (!companies[i].locked) {
            removeCompany(i);
            break;
          }
        }
      }
    });

    tagInput.addEventListener('blur', function () {
      if (tagInput.value.trim() !== '') {
        if (addCompany(tagInput.value)) {
          tagInput.value = '';
        }
      }
    });
  }

  if (tagsWrap) {
    tagsWrap.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-remove-index]');
      if (!btn) {
        return;
      }
      removeCompany(parseInt(btn.getAttribute('data-remove-index'), 10));
    });
  }

  if (detailsWrap) {
    detailsWrap.addEventListener('input', function (e) {
      var input = e.target.closest('.company-detail-input');
      if (!input || input.disabled) {
        return;
      }
      var index = parseInt(input.getAttribute('data-index'), 10);
      var field = input.getAttribute('data-field');
      if (!companies[index] || companies[index].locked) {
        return;
      }
      companies[index][field] = upper(input.value);
      syncHiddenJson();
    });
  }

  if (classSelect) {
    classSelect.addEventListener('change', scheduleLookup);
  }
  if (indexInput) {
    indexInput.addEventListener('input', scheduleLookup);
    indexInput.addEventListener('blur', lookupExisting);
  }

  form.addEventListener('submit', function (e) {
    if (tagInput && tagInput.value.trim() !== '') {
      addCompany(tagInput.value);
      tagInput.value = '';
    }

    syncHiddenJson();
    var list = companies.filter(function (c) {
      return c.name;
    });

    if (returningMode) {
      var newOnes = list.filter(function (c) {
        return !c.locked;
      });
      if (newOnes.length === 0) {
        e.preventDefault();
        setLookupStatus('<p>Add at least one new company before saving.</p>', 'bg-red-50 text-red-700');
        return;
      }
      for (var i = 0; i < newOnes.length; i++) {
        if (!newOnes[i].location || !newOnes[i].official_position) {
          e.preventDefault();
          setLookupStatus('<p>Complete location and official position for each new company.</p>', 'bg-red-50 text-red-700');
          return;
        }
      }
      return;
    }

    if (list.length === 0) {
      e.preventDefault();
      setLookupStatus('<p>Add at least one company before submitting.</p>', 'bg-red-50 text-red-700');
      if (tagInput) {
        tagInput.focus();
      }
      return;
    }

    for (var j = 0; j < list.length; j++) {
      if (!list[j].location || !list[j].official_position) {
        e.preventDefault();
        setLookupStatus('<p>Complete location and official position for company ' + (j + 1) + '.</p>', 'bg-red-50 text-red-700');
        return;
      }
    }
  });

  renderAll();
})();
