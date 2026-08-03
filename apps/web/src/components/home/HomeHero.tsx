'use client';

import Link from 'next/link';
import { useCallback, useEffect, useRef, useState } from 'react';

const THEMES: Record<string, string> = {
  lavender: 'linear-gradient(165deg, #ece4f8 0%, #ddd5ee 55%, #d4cbe8 100%)',
  mint: 'linear-gradient(165deg, #e4f3ec 0%, #d4ebe0 55%, #c8e4d6 100%)',
  blush: 'linear-gradient(165deg, #fdeef0 0%, #fce0e5 55%, #f8d4dc 100%)',
  cream: 'linear-gradient(135deg, #faf6ee 0%, #f3ebe0 100%)',
  peach: 'linear-gradient(135deg, #fdf0e8 0%, #fce4d6 100%)',
  green: 'linear-gradient(135deg, #f0f7f2 0%, #e4f0e8 100%)',
};

const SIDE_SLIDES = [
  {
    theme: 'lavender',
    kicker: 'Sai Flower',
    title: 'Fresh Flowers,<br>Delivered with Love',
    cta: 'Shop Now',
    href: '/flowers',
    img: '/assets/images/hero/side-pink-roses.jpg',
  },
  {
    theme: 'mint',
    kicker: 'Same Day',
    title: 'Surprise Them<br>Before Sunset',
    cta: 'Order Now',
    href: '/collection/same-day-delivery',
    img: '/assets/images/hero/side-same-day.jpg',
  },
  {
    theme: 'blush',
    kicker: 'LUXE',
    title: 'Premium Bouquets<br>For Special Moments',
    cta: 'Explore Luxe',
    href: '/collection/luxury-flowers',
    img: '/assets/images/hero/side-luxe-bouquet.jpg',
  },
];

const MAIN_SLIDES = [
  {
    theme: 'peach',
    kicker: 'Sai Flower',
    title: 'Same Day Delivery<br>in Delhi',
    subtitle: 'Handpicked luxury bouquets for all special moments.',
    cta: 'Shop Now',
    href: '/collection/same-day-delivery',
    img: '/assets/images/hero/main-same-day.jpg',
  },
  {
    theme: 'cream',
    kicker: 'Sai Flower',
    title: 'Premium Blooms,<br>Curated for You',
    subtitle: 'Handpicked luxury bouquets for every special moment.',
    cta: 'Order Now',
    href: '/collection/premium-bouquets',
    img: '/assets/images/hero/main-premium-blooms.jpg',
  },
  {
    theme: 'blush',
    kicker: 'Sai Flower',
    title: 'Birthday Joy,<br>Gift-Wrapped',
    subtitle: 'Curated blooms, cakes & more for thoughtful celebrations.',
    cta: 'Shop Now',
    href: '/occasion/birthday',
    img: '/assets/images/hero/main-birthday-joy.jpg',
  },
];

const CATEGORY_ICONS = [
  { label: 'Birthday', href: '/occasion/birthday', icon: 'fa-cake-candles' },
  { label: 'Anniversary', href: '/occasion/anniversary', icon: 'fa-heart' },
  { label: 'Same Day', href: '/collection/same-day-delivery', icon: 'fa-bolt' },
  { label: 'Roses', href: '/flower/roses', icon: 'fa-spa' },
  { label: 'Wedding', href: '/occasion/wedding', icon: 'fa-ring' },
  { label: 'Plants', href: '/collection/plants', icon: 'fa-leaf' },
  { label: 'Personalised', href: '/gifts', icon: 'fa-pen-nib' },
  { label: 'LUXE', href: '/collection/luxury-flowers', icon: 'fa-gem' },
  { label: 'Hampers', href: '/collection/hampers', icon: 'fa-gift' },
  { label: 'Occasions', href: '/celebration-calendar', icon: 'fa-calendar-days' },
];

const TRUST_ITEMS = [
  {
    icon: 'fa-truck-fast',
    title: 'Same-Day Delivery',
    sub: 'Order by 6 PM, delivered today in Delhi NCR',
  },
  { icon: 'fa-star', title: 'Rated 4.8 / 5', sub: 'Loved by 10,000+ happy customers' },
  { icon: 'fa-leaf', title: 'Freshness Guaranteed', sub: 'Handcrafted with fresh blooms since 1998' },
  { icon: 'fa-shield-halved', title: '100% Secure Payments', sub: 'Safe checkout with trusted gateways' },
];

function plainTitle(html: string): string {
  return html.replace(/<br\s*\/?>/gi, ' ').replace(/<[^>]+>/g, '');
}

export function HomeHero() {
  const trackRef = useRef<HTMLDivElement>(null);
  const sideTrackRef = useRef<HTMLDivElement>(null);
  const [current, setCurrent] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const sideIndex = current % SIDE_SLIDES.length;

  const applyMainTransform = useCallback((index: number) => {
    const track = trackRef.current;
    if (!track || !track.children.length) return;
    const first = track.children[0] as HTMLElement;
    const gap = parseFloat(getComputedStyle(track).gap) || 0;
    const step = first.offsetWidth + gap;
    track.style.transform = `translateX(-${index * step}px)`;
  }, []);

  const applySideTransform = useCallback((index: number) => {
    const sideTrack = sideTrackRef.current;
    if (!sideTrack) return;
    sideTrack.style.transform = `translateX(-${index * 100}%)`;
  }, []);

  const moveSlide = useCallback(
    (dir: number) => {
      setCurrent((prev) => {
        const next = (prev + dir + MAIN_SLIDES.length) % MAIN_SLIDES.length;
        return next;
      });
    },
    [],
  );

  const goTo = useCallback((index: number) => {
    setCurrent(((index % MAIN_SLIDES.length) + MAIN_SLIDES.length) % MAIN_SLIDES.length);
  }, []);

  const stopAuto = useCallback(() => {
    if (timerRef.current) {
      clearInterval(timerRef.current);
      timerRef.current = null;
    }
  }, []);

  const startAuto = useCallback(() => {
    stopAuto();
    timerRef.current = setInterval(() => moveSlide(1), 3000);
  }, [moveSlide, stopAuto]);

  useEffect(() => {
    applyMainTransform(current);
    applySideTransform(sideIndex);
  }, [current, sideIndex, applyMainTransform, applySideTransform]);

  useEffect(() => {
    const onResize = () => {
      applyMainTransform(current);
      applySideTransform(sideIndex);
    };
    window.addEventListener('resize', onResize);
    startAuto();
    return () => {
      window.removeEventListener('resize', onResize);
      stopAuto();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps -- mount once for autoplay/resize
  }, []);

  useEffect(() => {
    (window as unknown as { moveSlide?: (dir: number) => void }).moveSlide = moveSlide;
    return () => {
      delete (window as unknown as { moveSlide?: (dir: number) => void }).moveSlide;
    };
  }, [moveSlide]);

  return (
    <div className="hp-fnp-firstview">
      <h1 className="sr-only">Sai Flower — Online flower delivery in Delhi NCR</h1>

      <section className="hp-fnp-icons" aria-label="Popular categories">
        <div className="hp-fnp-icons__scroll hide-scrollbar">
          {CATEGORY_ICONS.map((icon) => (
            <Link key={icon.label} href={icon.href} className="hp-fnp-icons__item">
              <span className="hp-fnp-icons__img hp-fnp-icons__img--icon">
                <i className={`fas ${icon.icon}`} aria-hidden="true" />
              </span>
              <span className="hp-fnp-icons__label">{icon.label}</span>
            </Link>
          ))}
        </div>
      </section>

      <div className="lx-hero-split">
        <aside className="lx-hero-split__side" aria-label="Featured promotions">
          <div className="lx-hero-side-slider" id="sideHeroSlider">
            <div className="lx-hero-side-slider__viewport">
              <div className="lx-hero-side-slider__track" id="sideSliderTrack" ref={sideTrackRef}>
                {SIDE_SLIDES.map((slide, i) => (
                  <div key={slide.theme} className="lx-hero-side-slide">
                    <Link
                      href={slide.href}
                      className="lx-hero-side-card"
                      style={{ background: THEMES[slide.theme] }}
                    >
                      <span className="lx-hero-side-card__copy">
                        <span className="lx-hero-side-card__kicker">{slide.kicker}</span>
                        <span
                          className="lx-hero-side-card__title"
                          dangerouslySetInnerHTML={{ __html: slide.title }}
                        />
                        <span className="lx-hero-side-card__cta">
                          {slide.cta} <i className="fas fa-arrow-right" aria-hidden="true" />
                        </span>
                      </span>
                      <span
                        className="lx-hero-side-card__img"
                        style={{ backgroundImage: `url('${slide.img}')` }}
                      >
                        <img
                          src={slide.img}
                          alt={plainTitle(slide.title)}
                          width={400}
                          height={400}
                          loading={i === 0 ? 'eager' : 'lazy'}
                          decoding="async"
                        />
                      </span>
                    </Link>
                  </div>
                ))}
              </div>
            </div>
            <div className="lx-hero-side-slider__dots" id="sideSliderTracker" aria-hidden="true">
              {SIDE_SLIDES.map((slide, i) => (
                <div
                  key={slide.theme}
                  className={i === sideIndex ? 'slider-dot active-pill' : 'slider-dot'}
                />
              ))}
            </div>
          </div>
        </aside>

        <div className="lx-hero-split__main">
          <section className="hp-hero-carousel relative bg-white overflow-hidden">
            <div className="hp-hero-carousel__outer w-full">
              <div
                className="relative w-full group/slider hp-hero-carousel__wrap"
                id="heroSlider"
                onMouseEnter={stopAuto}
                onMouseLeave={startAuto}
              >
                <div className="hp-hero-carousel__viewport relative w-full overflow-hidden">
                  <div
                    className="flex flex-nowrap h-full transition-transform duration-500 ease-in-out hp-hero-carousel__track"
                    id="sliderTrack"
                    ref={trackRef}
                    onTouchStart={(e) => {
                      (e.currentTarget as HTMLElement & { _tx?: number })._tx =
                        e.changedTouches[0].screenX;
                      stopAuto();
                    }}
                    onTouchEnd={(e) => {
                      const start =
                        (e.currentTarget as HTMLElement & { _tx?: number })._tx ?? 0;
                      const end = e.changedTouches[0].screenX;
                      if (end < start - 50) moveSlide(1);
                      if (end > start + 50) moveSlide(-1);
                      startAuto();
                    }}
                  >
                    {MAIN_SLIDES.map((slide, index) => (
                      <div
                        key={slide.img}
                        className="hp-hero-slide flex-shrink-0"
                        data-theme={slide.theme}
                      >
                        <Link
                          href={slide.href}
                          className="hp-hero-slide__mobile block w-full h-full md:hidden"
                        >
                          <picture className="w-full h-full block">
                            <img
                              src={slide.img}
                              className="w-full h-full block object-cover"
                              alt={plainTitle(slide.title)}
                              width={1920}
                              height={685}
                              decoding="sync"
                              loading={index === 0 ? 'eager' : 'lazy'}
                              {...(index === 0 ? { fetchPriority: 'high' as const } : {})}
                            />
                          </picture>
                        </Link>

                        <div
                          className="hp-hero-slide__card hp-hero-slide__card--cover hidden md:flex"
                          style={{ backgroundImage: `url('${slide.img}')` }}
                        >
                          <div className="hp-hero-slide__copy hp-hero-slide__copy--overlay">
                            <span className="hp-hero-slide__kicker">{slide.kicker}</span>
                            <h2
                              className="hp-hero-slide__title"
                              dangerouslySetInnerHTML={{ __html: slide.title }}
                            />
                            <p className="hp-hero-slide__sub">{slide.subtitle}</p>
                            <span className="hp-hero-slide__cta">{slide.cta}</span>
                          </div>
                          <Link
                            href={slide.href}
                            className="hp-hero-slide__card-link"
                            aria-label={plainTitle(slide.title)}
                          />
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

                <button
                  type="button"
                  onClick={() => {
                    moveSlide(-1);
                    startAuto();
                  }}
                  className="hp-hero-carousel__nav hp-hero-carousel__nav--prev hidden md:flex"
                  aria-label="Previous slide"
                >
                  <i className="fas fa-chevron-left" aria-hidden="true" />
                </button>
                <button
                  type="button"
                  onClick={() => {
                    moveSlide(1);
                    startAuto();
                  }}
                  className="hp-hero-carousel__nav hp-hero-carousel__nav--next hidden md:flex"
                  aria-label="Next slide"
                >
                  <i className="fas fa-chevron-right" aria-hidden="true" />
                </button>

                <div
                  className="hp-hero-carousel__dots lx-hero-split__dots absolute bottom-2 left-0 right-0 flex justify-center items-center gap-2 z-20 pb-2"
                  id="sliderTracker"
                >
                  {MAIN_SLIDES.map((slide, i) => (
                    <div
                      key={slide.img}
                      className={i === current ? 'slider-dot active-pill' : 'slider-dot'}
                      onClick={() => {
                        if (i !== current) {
                          goTo(i);
                          startAuto();
                        }
                      }}
                      role="button"
                      tabIndex={0}
                      aria-label={`Go to slide ${i + 1}`}
                      onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                          e.preventDefault();
                          goTo(i);
                          startAuto();
                        }
                      }}
                    />
                  ))}
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>

      <section className="lx-trustbar" aria-label="Why shop with Sai Flower">
        <div className="lx-trustbar__inner">
          {TRUST_ITEMS.map((trust) => (
            <div key={trust.title} className="lx-trustbar__item">
              <span className="lx-trustbar__icon" aria-hidden="true">
                <i className={`fas ${trust.icon}`} />
              </span>
              <div>
                <p className="lx-trustbar__title">{trust.title}</p>
                <p className="lx-trustbar__sub">{trust.sub}</p>
              </div>
            </div>
          ))}
        </div>
      </section>

    </div>
  );
}
