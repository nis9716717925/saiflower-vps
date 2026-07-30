(function () {
    'use strict';

    var track = document.getElementById('hpCelebrationsTrack');
    var wrap = document.getElementById('hpCelebrationsTrackWrap');
    var prevBtn = document.getElementById('hpCelebrationsPrev');
    var nextBtn = document.getElementById('hpCelebrationsNext');

    if (!track) return;

    var isDragging = false;
    var dragStartX = 0;
    var dragScrollLeft = 0;
    var dragMoved = false;

    function getScrollStep() {
        var card = track.querySelector('.hp-celebration-card');
        if (!card) return track.clientWidth * 0.85;

        var gap = parseFloat(getComputedStyle(track).gap) || 16;
        var cardStep = card.offsetWidth + gap;
        var visible = Math.max(1, Math.floor((track.clientWidth + gap) / cardStep));
        return cardStep * Math.min(visible, 3);
    }

    function scrollCelebrations(dir) {
        track.scrollBy({ left: dir * getScrollStep(), behavior: 'smooth' });
    }

    function updateNav() {
        var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
        var atStart = track.scrollLeft <= 2;
        var atEnd = track.scrollLeft >= maxScroll - 2;
        var canScroll = maxScroll > 2;

        if (prevBtn) prevBtn.disabled = !canScroll || atStart;
        if (nextBtn) nextBtn.disabled = !canScroll || atEnd;

        if (wrap) {
            wrap.classList.toggle('is-scrollable-left', canScroll && !atStart);
            wrap.classList.toggle('is-scrollable-right', canScroll && !atEnd);
        }
    }

    function onPointerDown(e) {
        if (e.pointerType === 'mouse' && e.button !== 0) return;

        isDragging = true;
        dragMoved = false;
        dragStartX = e.clientX;
        dragScrollLeft = track.scrollLeft;
        track.classList.add('is-dragging');

        if (track.setPointerCapture && e.target !== track) {
            try { track.setPointerCapture(e.pointerId); } catch (err) { /* ignore */ }
        }
    }

    function onPointerMove(e) {
        if (!isDragging) return;

        var delta = e.clientX - dragStartX;
        if (Math.abs(delta) > 4) dragMoved = true;

        track.scrollLeft = dragScrollLeft - delta;
        e.preventDefault();
    }

    function endDrag(e) {
        if (!isDragging) return;

        isDragging = false;
        track.classList.remove('is-dragging');

        if (track.releasePointerCapture) {
            try { track.releasePointerCapture(e.pointerId); } catch (err) { /* ignore */ }
        }

        updateNav();
    }

    function blockClickWhileDragging(e) {
        if (dragMoved) {
            e.preventDefault();
            e.stopPropagation();
            dragMoved = false;
        }
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { scrollCelebrations(-1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { scrollCelebrations(1); });

    track.addEventListener('scroll', updateNav, { passive: true });
    track.addEventListener('pointerdown', onPointerDown);
    track.addEventListener('pointermove', onPointerMove);
    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);
    track.addEventListener('pointerleave', endDrag);
    track.addEventListener('click', blockClickWhileDragging, true);
    track.addEventListener('dragstart', function (e) { e.preventDefault(); });

    track.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            scrollCelebrations(-1);
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            scrollCelebrations(1);
        }
    });

    window.addEventListener('resize', updateNav);

    if (window.ResizeObserver) {
        var ro = new ResizeObserver(updateNav);
        ro.observe(track);
    }

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(updateNav);
    }

    track.querySelectorAll('img').forEach(function (img) {
        if (!img.complete) img.addEventListener('load', updateNav, { once: true });
    });

    updateNav();
})();
