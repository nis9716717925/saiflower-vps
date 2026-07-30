(function () {
    'use strict';

    var occasionCache = Object.create(null);
    var track = document.getElementById('hpOccasionTrack');
    var skeleton = document.getElementById('hpOccasionSkeleton');
    var viewAll = document.getElementById('hpOccasionViewAll');
    var tabs = document.querySelectorAll('.hp-occasion-tab');
    var prevBtn = document.getElementById('hpOccasionPrev');
    var nextBtn = document.getElementById('hpOccasionNext');

    function scrollTrack(dir) {
        if (!track) return;
        var amount = Math.min(track.clientWidth * 0.85, 320);
        track.scrollBy({ left: dir * amount, behavior: 'smooth' });
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { scrollTrack(-1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { scrollTrack(1); });

    document.querySelectorAll('.hp-product-carousel-wrap').forEach(function (wrap) {
        var productTrack = wrap.querySelector('.hp-occasion-track');
        var productPrev = wrap.querySelector('.hp-occasion-nav--prev');
        var productNext = wrap.querySelector('.hp-occasion-nav--next');
        if (!productTrack) return;

        function scrollProduct(dir) {
            var card = productTrack.querySelector('.hp-occasion-card');
            var gap = parseFloat(getComputedStyle(productTrack).gap) || 14;
            var step = card ? card.offsetWidth + gap : productTrack.clientWidth * 0.85;
            productTrack.scrollBy({ left: dir * step, behavior: 'smooth' });
        }

        function updateNav() {
            if (!productPrev || !productNext) return;
            var maxScroll = productTrack.scrollWidth - productTrack.clientWidth - 1;
            productPrev.disabled = productTrack.scrollLeft <= 0;
            productNext.disabled = productTrack.scrollLeft >= maxScroll;
        }

        if (productPrev) productPrev.addEventListener('click', function () { scrollProduct(-1); });
        if (productNext) productNext.addEventListener('click', function () { scrollProduct(1); });
        productTrack.addEventListener('scroll', updateNav, { passive: true });
        window.addEventListener('resize', updateNav);
        updateNav();
    });

    function setLoading(on) {
        if (!track) return;
        track.classList.toggle('is-loading', on);
        if (skeleton) {
            skeleton.hidden = !on;
            skeleton.setAttribute('aria-hidden', on ? 'false' : 'true');
        }
    }

    function loadOccasion(key, btn) {
        if (!track || !key) return;

        if (occasionCache[key]) {
            track.innerHTML = occasionCache[key].html;
            if (viewAll && occasionCache[key].link) {
                viewAll.href = occasionCache[key].link;
                viewAll.textContent = '';
                viewAll.appendChild(document.createTextNode(occasionCache[key].cta + ' '));
                var icon = document.createElement('i');
                icon.className = 'fas fa-arrow-right';
                icon.setAttribute('aria-hidden', 'true');
                viewAll.appendChild(icon);
            }
            track.scrollLeft = 0;
            return;
        }

        setLoading(true);
        fetch('/ajax/homepage-occasion.php?occasion=' + encodeURIComponent(key), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.ok) throw new Error(data.message || 'Load failed');
                occasionCache[key] = { html: data.html, cta: data.cta, link: data.link };
                track.innerHTML = data.html;
                if (viewAll) {
                    viewAll.href = data.link || '/flowers.php';
                    viewAll.innerHTML = (data.cta || 'View All Gifts') + ' <i class="fas fa-arrow-right" aria-hidden="true"></i>';
                }
                track.scrollLeft = 0;
            })
            .catch(function () {
                track.innerHTML = '<p class="hp-occasion-empty">Unable to load products. <a href="/flowers.php">Browse all flowers</a>.</p>';
            })
            .finally(function () {
                setLoading(false);
            });
    }

    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.getAttribute('data-occasion');
            if (!key || btn.classList.contains('is-active')) return;

            tabs.forEach(function (t) {
                t.classList.remove('is-active');
                t.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-selected', 'true');

            loadOccasion(key, btn);
        });
    });

    if (track && tabs.length) {
        var firstKey = tabs[0].getAttribute('data-occasion');
        if (firstKey) {
            occasionCache[firstKey] = {
                html: track.innerHTML,
                cta: viewAll ? viewAll.textContent.replace(/\s*$/, '').trim() : '',
                link: viewAll ? viewAll.getAttribute('href') : '/flowers.php'
            };
        }
    }

})();
