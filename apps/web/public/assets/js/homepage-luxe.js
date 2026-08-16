/**
 * Luxe homepage enhancements — testimonials + scroll reveal.
 * Category mega-nav lives in /assets/js/catnav.js (universal header).
 */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isCoarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
    var isNarrow = window.matchMedia && window.matchMedia('(max-width: 899.98px)').matches;

    function debounce(fn, wait) {
        var t = null;
        return function () {
            var ctx = this;
            var args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    /* ---- Testimonial slider ---- */
    var track = document.getElementById('lxTestimonialsTrack');
    var prevBtn = document.getElementById('lxTestimonialsPrev');
    var nextBtn = document.getElementById('lxTestimonialsNext');

    if (track && prevBtn && nextBtn) {
        var cards = track.querySelectorAll('.lx-testimonial');
        var autoTimer = null;
        var gap = 14;

        function getScrollAmount() {
            var card = cards[0];
            if (!card) return track.clientWidth;
            return card.offsetWidth + gap;
        }

        function updateNavState() {
            var maxScroll = track.scrollWidth - track.clientWidth - 2;
            prevBtn.disabled = track.scrollLeft <= 2;
            nextBtn.disabled = track.scrollLeft >= maxScroll;
        }

        function scrollByDir(dir) {
            track.scrollBy({ left: dir * getScrollAmount(), behavior: reduceMotion ? 'auto' : 'smooth' });
        }

        prevBtn.addEventListener('click', function () { scrollByDir(-1); });
        nextBtn.addEventListener('click', function () { scrollByDir(1); });
        track.addEventListener('scroll', updateNavState, { passive: true });
        window.addEventListener('resize', debounce(updateNavState, 150), { passive: true });
        updateNavState();

        function startAuto() {
            if (reduceMotion || isCoarse) return;
            stopAuto();
            autoTimer = setInterval(function () {
                var maxScroll = track.scrollWidth - track.clientWidth - 2;
                if (track.scrollLeft >= maxScroll) {
                    track.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    scrollByDir(1);
                }
            }, 5500);
        }

        function stopAuto() {
            if (autoTimer) {
                clearInterval(autoTimer);
                autoTimer = null;
            }
        }

        if (!reduceMotion && !isCoarse) {
            startAuto();
            track.addEventListener('pointerenter', stopAuto);
            track.addEventListener('pointerleave', startAuto);
            prevBtn.addEventListener('mouseenter', stopAuto);
            nextBtn.addEventListener('mouseenter', stopAuto);
        }
    }

    /* ---- Scroll reveal (lighter on mobile) ---- */
    if (!('IntersectionObserver' in window)) return;
    if (reduceMotion) return;

    var selector = isNarrow
        ? '.hp-section-head, .lx-section-head, .lx-trustbar__item, .lx-step, .lx-faq__item, .lx-final-cta__shell, .lx-promo-card, .lx-testimonial'
        : '.hp-section-head, .lx-section-head, .lx-trustbar__item, .lx-step, .lx-faq__item, ' +
          '.lx-final-cta__shell, .hp-finder-shell, .lx-story, .hp-dynamic-sections .container, ' +
          '.lx-promo-card, .lx-tile, .lx-testimonial, .lx-stats__item, .lx-about__visual';

    var targets = document.querySelectorAll(selector);
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-inview');
                observer.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: isNarrow ? '0px 0px -4% 0px' : '0px 0px -8% 0px',
        threshold: 0.06
    });

    targets.forEach(function (el, i) {
        el.classList.add('lx-reveal');
        el.style.transitionDelay = Math.min((i % 3) * (isNarrow ? 40 : 60), isNarrow ? 120 : 180) + 'ms';
        observer.observe(el);
    });
})();
