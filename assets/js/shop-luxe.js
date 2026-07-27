/**
 * Shop page interactions — drawers, rails, quick view.
 */
(function () {
  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function openDrawer() {
    var drawer = qs('#spDrawer');
    var overlay = qs('#spDrawerOverlay');
    var btn = qs('#spOpenFilters');
    if (!drawer) return;
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    if (overlay) {
      overlay.hidden = false;
      overlay.classList.add('is-open');
    }
    if (btn) btn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    var drawer = qs('#spDrawer');
    var overlay = qs('#spDrawerOverlay');
    var btn = qs('#spOpenFilters');
    if (!drawer) return;
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    if (overlay) {
      overlay.classList.remove('is-open');
      overlay.hidden = true;
    }
    if (btn) btn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  function initDrawer() {
    var openBtn = qs('#spOpenFilters');
    var closeBtn = qs('#spCloseFilters');
    var overlay = qs('#spDrawerOverlay');
    if (openBtn) openBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay) overlay.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeDrawer();
        closeQuickView();
      }
    });
  }

  function initRails() {
    qsa('[data-sp-rail]').forEach(function (root) {
      var track = qs('.sp-rail__track', root);
      var prev = qs('[data-sp-rail-prev]', root);
      var next = qs('[data-sp-rail-next]', root);
      if (!track) return;
      function move(dir) {
        track.scrollBy({ left: dir * Math.max(240, track.clientWidth * 0.8), behavior: 'smooth' });
      }
      if (prev) prev.addEventListener('click', function () { move(-1); });
      if (next) next.addEventListener('click', function () { move(1); });
    });
  }

  function openQuickView(btn) {
    var modal = qs('#spQuickView');
    if (!modal) return;
    qs('#spQvTitle').textContent = btn.getAttribute('data-name') || '';
    qs('#spQvPrice').textContent = '₹' + (btn.getAttribute('data-price') || '');
    qs('#spQvRating').textContent = (btn.getAttribute('data-rating') || '') + ' ★';
    var img = qs('#spQvImg');
    img.src = btn.getAttribute('data-img') || '';
    img.alt = btn.getAttribute('data-name') || '';
    qs('#spQvLink').href = btn.getAttribute('data-link') || '#';
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeQuickView() {
    var modal = qs('#spQuickView');
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if (!qs('#spDrawer') || !qs('#spDrawer').classList.contains('is-open')) {
      document.body.style.overflow = '';
    }
  }

  function initQuickView() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-sp-quickview]');
      if (btn) {
        e.preventDefault();
        openQuickView(btn);
        return;
      }
      if (e.target.closest('[data-sp-qv-close]')) {
        closeQuickView();
      }
    });
  }

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function () {
    initDrawer();
    initRails();
    initQuickView();
  });
})();
