/**
 * Universal category mega-nav behavior (header).
 * Desktop hover megas; closes on scroll / Escape / outside click.
 */
(function () {
    'use strict';

    var catnav = document.querySelector('.lx-catnav');
    var header = document.querySelector('.sf-site-header');
    if (!catnav) return;

    var items = Array.prototype.slice.call(catnav.querySelectorAll('.lx-catnav__item'));
    var desktopMq = window.matchMedia('(min-width: 1024px)');
    var openTimer = null;
    var closeTimer = null;
    var lastScrollY = window.scrollY || 0;
    var stuckRaf = 0;

    function updateHeaderScroll() {
        if (!header) return;
        if (stuckRaf) return;
        stuckRaf = requestAnimationFrame(function () {
            stuckRaf = 0;
            header.classList.toggle('is-scrolled', window.scrollY > 6);
        });
    }

    window.addEventListener('scroll', updateHeaderScroll, { passive: true });
    updateHeaderScroll();

    function closeAllMegas() {
        items.forEach(function (item) {
            item.classList.remove('is-open');
            var link = item.querySelector('.lx-catnav__link');
            if (link) link.setAttribute('aria-expanded', 'false');
        });
    }

    function openMega(item) {
        if (!desktopMq.matches) return;
        if (!item.querySelector('.lx-catnav__mega')) return;
        clearTimeout(closeTimer);
        closeAllMegas();
        item.classList.add('is-open');
        var link = item.querySelector('.lx-catnav__link');
        if (link) link.setAttribute('aria-expanded', 'true');
    }

    items.forEach(function (item) {
        var link = item.querySelector('.lx-catnav__link');
        if (link && item.querySelector('.lx-catnav__mega')) {
            link.setAttribute('aria-haspopup', 'true');
            link.setAttribute('aria-expanded', 'false');
        }

        item.addEventListener('mouseenter', function () {
            if (!desktopMq.matches) return;
            clearTimeout(closeTimer);
            clearTimeout(openTimer);
            openTimer = setTimeout(function () { openMega(item); }, 40);
        });

        item.addEventListener('mouseleave', function () {
            if (!desktopMq.matches) return;
            clearTimeout(openTimer);
            closeTimer = setTimeout(closeAllMegas, 160);
        });

        if (link) {
            link.addEventListener('focus', function () {
                if (!desktopMq.matches) return;
                openMega(item);
            });
        }
    });

    window.addEventListener('scroll', function () {
        var y = window.scrollY || 0;
        var delta = Math.abs(y - lastScrollY);
        lastScrollY = y;
        if (delta < 6) return;
        clearTimeout(openTimer);
        clearTimeout(closeTimer);
        closeAllMegas();
    }, { passive: true });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllMegas();
    });

    document.addEventListener('click', function (e) {
        if (!catnav.contains(e.target)) closeAllMegas();
    });

    if (typeof desktopMq.addEventListener === 'function') {
        desktopMq.addEventListener('change', closeAllMegas);
    } else if (typeof desktopMq.addListener === 'function') {
        desktopMq.addListener(closeAllMegas);
    }
})();
