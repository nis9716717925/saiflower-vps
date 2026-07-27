/**
 * Collection landing interactions — sliders, sticky search polish.
 */
(function () {
  function initSliders() {
    document.querySelectorAll('[data-cl-slider]').forEach(function (root) {
      var track = root.querySelector('.cl-slider__track');
      var prev = root.querySelector('[data-cl-prev]');
      var next = root.querySelector('[data-cl-next]');
      if (!track) return;

      function scrollByDir(dir) {
        var amount = Math.max(220, Math.floor(track.clientWidth * 0.8));
        track.scrollBy({ left: dir * amount, behavior: 'smooth' });
      }

      if (prev) prev.addEventListener('click', function () { scrollByDir(-1); });
      if (next) next.addEventListener('click', function () { scrollByDir(1); });
    });
  }

  function enhanceStickySearch() {
    var bar = document.getElementById('clStickySearch');
    if (!bar || !('IntersectionObserver' in window)) return;
    var sentinel = document.createElement('div');
    sentinel.style.position = 'absolute';
    sentinel.style.top = '0';
    sentinel.style.height = '1px';
    sentinel.style.width = '1px';
    bar.parentNode.insertBefore(sentinel, bar);
    var io = new IntersectionObserver(function (entries) {
      bar.classList.toggle('is-stuck', !entries[0].isIntersecting);
    });
    io.observe(sentinel);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initSliders();
      enhanceStickySearch();
    });
  } else {
    initSliders();
    enhanceStickySearch();
  }
})();
