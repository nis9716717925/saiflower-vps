(function () {
    'use strict';

    window.pdSubmitPurchase = function (mode) {
        var form = document.getElementById('productPurchaseForm');
        if (!form) return;

        var ghost = form.querySelectorAll('input[name="add_to_cart"], input[name="buy_now"]');
        for (var i = 0; i < ghost.length; i++) ghost[i].remove();

        var h = document.createElement('input');
        h.type = 'hidden';
        if (mode === 'buy') {
            h.name = 'buy_now';
            h.value = '1';
        } else {
            h.name = 'add_to_cart';
            h.value = '1';
        }
        form.appendChild(h);
        form.submit();
    };

    function initImageZoom() {
        var main = document.getElementById('mainView');
        var backdrop = document.getElementById('pdImageZoom');
        var zoomImg = document.getElementById('pdImageZoomImg');
        var closeBtn = document.getElementById('pdImageZoomClose');

        if (!main || !backdrop || !zoomImg) return;

        function openZoom() {
            if (!main.src) return;
            zoomImg.src = main.src;
            zoomImg.alt = main.alt || '';
            backdrop.classList.add('is-open');
            backdrop.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeZoom() {
            backdrop.classList.remove('is-open');
            backdrop.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        main.addEventListener('click', function () {
            if (main.classList.contains('hidden-media')) return;
            openZoom();
        });

        if (closeBtn) closeBtn.addEventListener('click', closeZoom);
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) closeZoom();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && backdrop.classList.contains('is-open')) closeZoom();
        });
    }

    function syncStickyButtons() {
        var stickyAdd = document.getElementById('pdStickyAdd');
        var stickyBuy = document.getElementById('pdStickyBuy');
        var mainAdd = document.getElementById('pdBtnAddCart');
        var mainBuy = document.getElementById('pdBtnBuyNow');

        if (stickyAdd && mainAdd) stickyAdd.disabled = mainAdd.disabled;
        if (stickyBuy && mainBuy) stickyBuy.disabled = mainBuy.disabled;
    }

    function wireSticky() {
        var sAdd = document.getElementById('pdStickyAdd');
        var sBuy = document.getElementById('pdStickyBuy');
        if (sAdd) sAdd.addEventListener('click', function () { window.pdSubmitPurchase('add'); });
        if (sBuy) sBuy.addEventListener('click', function () { window.pdSubmitPurchase('buy'); });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initImageZoom();
        syncStickyButtons();
        wireSticky();
    });
})();
