'use client';

import { useEffect } from 'react';

function debounce<T extends (...args: never[]) => void>(fn: T, wait: number): T {
  let timer: ReturnType<typeof setTimeout> | null = null;
  return ((...args: Parameters<T>) => {
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => fn(...args), wait);
  }) as T;
}

/**
 * Homepage carousel, occasion tabs, testimonials, and scroll-reveal —
 * replaces legacy homepage-premium.js + homepage-luxe.js post-hydration scripts.
 */
export function HomepageInteractions() {
  useEffect(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isCoarse = window.matchMedia('(pointer: coarse)').matches;
    const isNarrow = window.matchMedia('(max-width: 899.98px)').matches;

    const cleanups: Array<() => void> = [];

    /* ---- Product carousel nav (homepage-premium.js) ---- */
    document.querySelectorAll('.hp-product-carousel-wrap').forEach((wrap) => {
      const productTrack = wrap.querySelector<HTMLElement>('.hp-occasion-track');
      const productPrev = wrap.querySelector<HTMLButtonElement>('.hp-occasion-nav--prev');
      const productNext = wrap.querySelector<HTMLButtonElement>('.hp-occasion-nav--next');
      if (!productTrack) return;

      const scrollProduct = (dir: number) => {
        const card = productTrack.querySelector<HTMLElement>('.hp-occasion-card');
        const gap = parseFloat(getComputedStyle(productTrack).gap) || 14;
        const step = card ? card.offsetWidth + gap : productTrack.clientWidth * 0.85;
        productTrack.scrollBy({ left: dir * step, behavior: 'smooth' });
      };

      const updateNav = () => {
        if (!productPrev || !productNext) return;
        const maxScroll = productTrack.scrollWidth - productTrack.clientWidth - 1;
        productPrev.disabled = productTrack.scrollLeft <= 0;
        productNext.disabled = productTrack.scrollLeft >= maxScroll;
      };

      const onPrev = () => scrollProduct(-1);
      const onNext = () => scrollProduct(1);
      productPrev?.addEventListener('click', onPrev);
      productNext?.addEventListener('click', onNext);
      productTrack.addEventListener('scroll', updateNav, { passive: true });
      window.addEventListener('resize', updateNav);
      updateNav();

      cleanups.push(() => {
        productPrev?.removeEventListener('click', onPrev);
        productNext?.removeEventListener('click', onNext);
        productTrack.removeEventListener('scroll', updateNav);
        window.removeEventListener('resize', updateNav);
      });
    });

    /* ---- Tailored occasions tab carousel ---- */
    const occasionCache: Record<string, { html: string; cta: string; link: string }> = {};
    const track = document.getElementById('hpOccasionTrack');
    const skeleton = document.getElementById('hpOccasionSkeleton');
    const viewAll = document.getElementById('hpOccasionViewAll') as HTMLAnchorElement | null;
    const tabs = document.querySelectorAll<HTMLButtonElement>('.hp-occasion-tab');
    const prevBtn = document.getElementById('hpOccasionPrev');
    const nextBtn = document.getElementById('hpOccasionNext');

    const setLoading = (on: boolean) => {
      if (!track) return;
      track.classList.toggle('is-loading', on);
      if (skeleton) {
        skeleton.hidden = !on;
        skeleton.setAttribute('aria-hidden', on ? 'false' : 'true');
      }
    };

    const scrollTrack = (dir: number) => {
      if (!track) return;
      const amount = Math.min(track.clientWidth * 0.85, 320);
      track.scrollBy({ left: dir * amount, behavior: 'smooth' });
    };

    const onOccasionPrev = () => scrollTrack(-1);
    const onOccasionNext = () => scrollTrack(1);
    prevBtn?.addEventListener('click', onOccasionPrev);
    nextBtn?.addEventListener('click', onOccasionNext);

    const loadOccasion = async (key: string) => {
      if (!track || !key) return;

      if (occasionCache[key]) {
        track.innerHTML = occasionCache[key].html;
        if (viewAll && occasionCache[key].link) {
          viewAll.href = occasionCache[key].link;
          viewAll.textContent = '';
          viewAll.append(`${occasionCache[key].cta} `);
          const icon = document.createElement('i');
          icon.className = 'fas fa-arrow-right';
          icon.setAttribute('aria-hidden', 'true');
          viewAll.append(icon);
        }
        track.scrollLeft = 0;
        return;
      }

      setLoading(true);
      try {
        const res = await fetch(`/ajax/homepage-occasion?occasion=${encodeURIComponent(key)}`, {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        });
        const data = (await res.json()) as {
          ok?: boolean;
          html?: string;
          cta?: string;
          link?: string;
          message?: string;
        };
        if (!data.ok || !data.html) throw new Error(data.message || 'Load failed');
        occasionCache[key] = {
          html: data.html,
          cta: data.cta || 'View All Gifts',
          link: data.link || '/flowers',
        };
        track.innerHTML = data.html;
        if (viewAll) {
          viewAll.href = data.link || '/flowers';
          viewAll.innerHTML = `${data.cta || 'View All Gifts'} <i class="fas fa-arrow-right" aria-hidden="true"></i>`;
        }
        track.scrollLeft = 0;
      } catch {
        track.innerHTML =
          '<p class="hp-occasion-empty">Unable to load products. <a href="/flowers">Browse all flowers</a>.</p>';
      } finally {
        setLoading(false);
      }
    };

    const tabHandlers: Array<{ btn: HTMLButtonElement; handler: () => void }> = [];
    tabs.forEach((btn) => {
      const handler = () => {
        const key = btn.getAttribute('data-occasion');
        if (!key || btn.classList.contains('is-active')) return;
        tabs.forEach((t) => {
          t.classList.remove('is-active');
          t.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('is-active');
        btn.setAttribute('aria-selected', 'true');
        void loadOccasion(key);
      };
      btn.addEventListener('click', handler);
      tabHandlers.push({ btn, handler });
    });

    if (track && tabs.length) {
      const firstKey = tabs[0]?.getAttribute('data-occasion');
      if (firstKey) {
        occasionCache[firstKey] = {
          html: track.innerHTML,
          cta: viewAll?.textContent?.trim() || '',
          link: viewAll?.getAttribute('href') || '/flowers',
        };
      }
    }

    cleanups.push(() => {
      prevBtn?.removeEventListener('click', onOccasionPrev);
      nextBtn?.removeEventListener('click', onOccasionNext);
      tabHandlers.forEach(({ btn, handler }) => btn.removeEventListener('click', handler));
    });

    /* ---- Testimonials slider (homepage-luxe.js) ---- */
    const testimonialTrack = document.getElementById('lxTestimonialsTrack');
    const testimonialPrev = document.getElementById('lxTestimonialsPrev');
    const testimonialNext = document.getElementById('lxTestimonialsNext');

    if (testimonialTrack && testimonialPrev && testimonialNext) {
      const cards = testimonialTrack.querySelectorAll('.lx-testimonial');
      const gap = 14;
      let autoTimer: ReturnType<typeof setInterval> | null = null;

      const getScrollAmount = () => {
        const card = cards[0] as HTMLElement | undefined;
        return card ? card.offsetWidth + gap : testimonialTrack.clientWidth;
      };

      const updateTestimonialNav = () => {
        const maxScroll = testimonialTrack.scrollWidth - testimonialTrack.clientWidth - 2;
        (testimonialPrev as HTMLButtonElement).disabled = testimonialTrack.scrollLeft <= 2;
        (testimonialNext as HTMLButtonElement).disabled = testimonialTrack.scrollLeft >= maxScroll;
      };

      const scrollTestimonials = (dir: number) => {
        testimonialTrack.scrollBy({
          left: dir * getScrollAmount(),
          behavior: reduceMotion ? 'auto' : 'smooth',
        });
      };

      const onTestPrev = () => scrollTestimonials(-1);
      const onTestNext = () => scrollTestimonials(1);
      const onResize = debounce(updateTestimonialNav, 150);

      testimonialPrev.addEventListener('click', onTestPrev);
      testimonialNext.addEventListener('click', onTestNext);
      testimonialTrack.addEventListener('scroll', updateTestimonialNav, { passive: true });
      window.addEventListener('resize', onResize, { passive: true });
      updateTestimonialNav();

      const stopAuto = () => {
        if (autoTimer) {
          clearInterval(autoTimer);
          autoTimer = null;
        }
      };

      const startAuto = () => {
        if (reduceMotion || isCoarse) return;
        stopAuto();
        autoTimer = setInterval(() => {
          const maxScroll = testimonialTrack.scrollWidth - testimonialTrack.clientWidth - 2;
          if (testimonialTrack.scrollLeft >= maxScroll) {
            testimonialTrack.scrollTo({ left: 0, behavior: 'smooth' });
          } else {
            scrollTestimonials(1);
          }
        }, 5500);
      };

      if (!reduceMotion && !isCoarse) {
        startAuto();
        testimonialTrack.addEventListener('pointerenter', stopAuto);
        testimonialTrack.addEventListener('pointerleave', startAuto);
        testimonialPrev.addEventListener('mouseenter', stopAuto);
        testimonialNext.addEventListener('mouseenter', stopAuto);
      }

      cleanups.push(() => {
        stopAuto();
        testimonialPrev.removeEventListener('click', onTestPrev);
        testimonialNext.removeEventListener('click', onTestNext);
        testimonialTrack.removeEventListener('scroll', updateTestimonialNav);
        window.removeEventListener('resize', onResize);
        testimonialTrack.removeEventListener('pointerenter', stopAuto);
        testimonialTrack.removeEventListener('pointerleave', startAuto);
        testimonialPrev.removeEventListener('mouseenter', stopAuto);
        testimonialNext.removeEventListener('mouseenter', stopAuto);
      });
    }

    /* ---- Scroll reveal ---- */
    if ('IntersectionObserver' in window && !reduceMotion) {
      const selector = isNarrow
        ? '.hp-section-head, .lx-section-head, .lx-trustbar__item, .lx-step, .lx-faq__item, .lx-final-cta__shell, .lx-promo-card, .lx-testimonial'
        : '.hp-section-head, .lx-section-head, .lx-trustbar__item, .lx-step, .lx-faq__item, ' +
          '.lx-final-cta__shell, .hp-finder-shell, .lx-story, .hp-dynamic-sections .container, ' +
          '.lx-promo-card, .lx-tile, .lx-testimonial, .lx-stats__item, .lx-about__visual';

      const targets = document.querySelectorAll(selector);
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-inview');
              observer.unobserve(entry.target);
            }
          });
        },
        {
          rootMargin: isNarrow ? '0px 0px -4% 0px' : '0px 0px -8% 0px',
          threshold: 0.06,
        },
      );

      targets.forEach((el, i) => {
        el.classList.add('lx-reveal');
        (el as HTMLElement).style.transitionDelay = `${Math.min((i % 3) * (isNarrow ? 40 : 60), isNarrow ? 120 : 180)}ms`;
        observer.observe(el);
      });

      cleanups.push(() => observer.disconnect());
    }

    return () => {
      cleanups.forEach((fn) => fn());
    };
  }, []);

  return null;
}
