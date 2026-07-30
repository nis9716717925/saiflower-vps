/**
 * Sitewide search suggestions — live while typing, race-safe, keyboard friendly.
 */
(function () {
  'use strict';

  const MIN_LEN = 1;
  const DEBOUNCE_MS = 160;
  const ENDPOINT = '/ajax_search.php';

  function esc(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function setupSearchAutocomplete(inputId, dropdownId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    if (!input || !dropdown) return;

    let timer = null;
    let controller = null;
    let requestSeq = 0;
    let activeIndex = -1;
    let currentItems = [];

    function hide() {
      dropdown.style.display = 'none';
      dropdown.innerHTML = '';
      activeIndex = -1;
      currentItems = [];
      input.removeAttribute('aria-activedescendant');
    }

    function showLoading(q) {
      dropdown.innerHTML = `<div class="sf-suggest-msg">Searching “${esc(q)}”…</div>`;
      dropdown.style.display = 'block';
    }

    function render(items, q) {
      currentItems = items;
      activeIndex = -1;

      if (!items.length) {
        dropdown.innerHTML = `<div class="sf-suggest-msg">No matches for “${esc(q)}”. Try roses, birthday, or cakes.</div>
          <a class="sf-suggest-foot" href="/search-results?q=${encodeURIComponent(q)}">Search all results</a>`;
        dropdown.style.display = 'block';
        return;
      }

      let html = '';
      items.forEach((item, i) => {
        const id = `${dropdownId}-opt-${i}`;
        html += `
          <a id="${id}" role="option" href="${esc(item.link)}" class="sf-suggest-item" data-index="${i}">
            <img src="${esc(item.image)}" alt="" width="40" height="40" loading="lazy" decoding="async"
                 onerror="this.src='/uploads/logo_transparent.png'">
            <span class="sf-suggest-copy">
              <span class="sf-suggest-name">${esc(item.name)}</span>
              <span class="sf-suggest-type">${esc(item.badge || item.type || '')}</span>
            </span>
          </a>`;
      });
      html += `<a class="sf-suggest-foot" href="/search-results?q=${encodeURIComponent(q)}">View all results for “${esc(q)}”</a>`;
      dropdown.innerHTML = html;
      dropdown.style.display = 'block';
    }

    function setActive(next) {
      const opts = dropdown.querySelectorAll('.sf-suggest-item');
      if (!opts.length) return;
      activeIndex = (next + opts.length) % opts.length;
      opts.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
      const active = opts[activeIndex];
      if (active) {
        input.setAttribute('aria-activedescendant', active.id);
        active.scrollIntoView({ block: 'nearest' });
      }
    }

    function fetchSuggestions(q) {
      if (controller) {
        try { controller.abort(); } catch (e) { /* noop */ }
      }
      controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
      const seq = ++requestSeq;
      showLoading(q);

      const url = `${ENDPOINT}?q=${encodeURIComponent(q)}&_=${Date.now()}`;
      fetch(url, {
        signal: controller ? controller.signal : undefined,
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      })
        .then((res) => {
          if (!res.ok) throw new Error('bad status');
          return res.json();
        })
        .then((data) => {
          if (seq !== requestSeq) return;
          if (input.value.trim() !== q) return;
          const items = data && data.success && Array.isArray(data.results) ? data.results : [];
          render(items, q);
        })
        .catch((err) => {
          if (err && err.name === 'AbortError') return;
          if (seq !== requestSeq) return;
          dropdown.innerHTML = `<div class="sf-suggest-msg">Couldn’t load suggestions. Press Enter to search.</div>`;
          dropdown.style.display = 'block';
        });
    }

    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-controls', dropdownId);
    dropdown.setAttribute('role', 'listbox');

    input.addEventListener('input', function () {
      clearTimeout(timer);
      const q = this.value.trim();
      if (q.length < MIN_LEN) {
        if (controller) {
          try { controller.abort(); } catch (e) { /* noop */ }
        }
        hide();
        input.setAttribute('aria-expanded', 'false');
        return;
      }
      input.setAttribute('aria-expanded', 'true');
      timer = setTimeout(() => fetchSuggestions(q), DEBOUNCE_MS);
    });

    input.addEventListener('keydown', function (e) {
      if (dropdown.style.display === 'none') return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        setActive(activeIndex + 1);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        setActive(activeIndex - 1);
      } else if (e.key === 'Enter' && activeIndex >= 0 && currentItems[activeIndex]) {
        e.preventDefault();
        window.location.href = currentItems[activeIndex].link;
      } else if (e.key === 'Escape') {
        hide();
        input.setAttribute('aria-expanded', 'false');
      }
    });

    input.addEventListener('focus', function () {
      const q = this.value.trim();
      if (q.length >= MIN_LEN) {
        fetchSuggestions(q);
        input.setAttribute('aria-expanded', 'true');
      }
    });

    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !dropdown.contains(e.target)) {
        hide();
        input.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function boot() {
    setupSearchAutocomplete('desktopSearchInput', 'desktopSearchSuggestions');
    setupSearchAutocomplete('mobileSearchInput', 'mobileSearchSuggestions');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
