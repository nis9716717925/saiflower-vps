(function () {
    const grid = document.getElementById('locResultsGrid') || document.querySelector('.loc-results-grid');
    const filters = document.getElementById('locFilters');
    const sortSelect = document.getElementById('locSort');
    const countEl = document.getElementById('locProductCount');
    const noMatch = document.getElementById('locNoMatch');

    if (!grid || !filters) {
        return;
    }

    const cards = () => Array.from(grid.querySelectorAll('.loc-card'));

    function applyFilter(filter) {
        let visible = 0;
        cards().forEach((card) => {
            const tags = (card.getAttribute('data-filters') || '').split(/\s+/);
            const show = filter === 'all' || tags.includes(filter);
            card.style.display = show ? '' : 'none';
            if (show) {
                visible++;
            }
        });

        if (countEl) {
            const total = countEl.getAttribute('data-total') || String(cards().length);
            const label = countEl.getAttribute('data-label') || countEl.textContent;
            if (filter === 'all') {
                countEl.textContent = label;
            } else {
                countEl.textContent = visible + ' of ' + total + ' shown · ' + (label.split('·')[1] || '').trim();
            }
        }

        if (noMatch) {
            noMatch.hidden = visible > 0;
        }
    }

    function applySort(mode) {
        const items = cards();
        items.sort((a, b) => {
            const pa = parseInt(a.getAttribute('data-price') || '0', 10);
            const pb = parseInt(b.getAttribute('data-price') || '0', 10);
            const ra = parseFloat(a.getAttribute('data-rating') || '0');
            const rb = parseFloat(b.getAttribute('data-rating') || '0');
            if (mode === 'price-asc') {
                return pa - pb;
            }
            if (mode === 'price-desc') {
                return pb - pa;
            }
            return rb - ra;
        });
        items.forEach((el) => grid.appendChild(el));
    }

    filters.addEventListener('click', (e) => {
        const btn = e.target.closest('.loc-filter');
        if (!btn) {
            return;
        }
        filters.querySelectorAll('.loc-filter').forEach((b) => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        applyFilter(btn.getAttribute('data-filter') || 'all');
    });

    document.querySelectorAll('.loc-reset').forEach((btn) => {
        btn.addEventListener('click', () => {
            const all = filters.querySelector('[data-filter="all"]');
            if (all) {
                all.click();
            }
        });
    });

    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            applySort(sortSelect.value);
            const active = filters.querySelector('.loc-filter.is-active');
            applyFilter(active ? active.getAttribute('data-filter') : 'all');
        });
    }
})();
