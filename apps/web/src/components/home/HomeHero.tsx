'use client';

import Link from 'next/link';
import { useCallback, useEffect, useRef, useState } from 'react';

/** Primary shop destination for hero taps. */
const SHOP_HREF = '/flowers';

const HERO_SLIDES = [
  {
    id: 'make-today-beautiful',
    alt: 'Same-day flower delivery — Make Today Beautiful',
    desktop: '/assets/images/hero/hero-make-today-beautiful.webp',
    mobile: '/assets/images/hero/hero-make-today-beautiful-mobile.webp',
  },
  {
    id: 'midnight-surprises',
    alt: 'Midnight flower delivery — Because Some Surprises Can\'t Wait',
    desktop: '/assets/images/hero/hero-midnight-surprises.webp',
    mobile: '/assets/images/hero/hero-midnight-surprises-mobile.webp',
  },
  {
    id: 'beautiful-moments',
    alt: 'Fresh bouquets delivered fast — Beautiful Moments',
    desktop: '/assets/images/hero/hero-beautiful-moments.webp',
    mobile: '/assets/images/hero/hero-beautiful-moments-mobile.webp',
  },
];

const CATEGORY_ICONS = [
  { label: 'Birthday', href: '/occasion/birthday', icon: 'fa-cake-candles' },
  { label: 'Anniversary', href: '/occasion/anniversary', icon: 'fa-heart' },
  { label: 'Same Day', href: '/collection/same-day-delivery', icon: 'fa-bolt' },
  { label: 'Roses', href: '/flowers/roses', icon: 'fa-spa' },
  { label: 'Wedding', href: '/occasion/wedding', icon: 'fa-ring' },
  { label: 'Plants', href: '/collection/plants', icon: 'fa-leaf' },
  { label: 'Personalised', href: '/gifts', icon: 'fa-pen-nib' },
  { label: 'LUXE', href: '/collection/luxury-flowers', icon: 'fa-gem' },
  { label: 'Hampers', href: '/collection/hampers', icon: 'fa-gift' },
  { label: 'Occasions', href: '/celebration-calendar', icon: 'fa-calendar-days' },
];

const TRUST_ITEMS_DESKTOP = [
  { icon: 'fa-truck-fast', title: 'Same-day delivery', sub: 'Order by 6 PM in Delhi NCR' },
  { icon: 'fa-star', title: 'Rated 4.8 / 5', sub: 'Loved by 10,000+ customers' },
  { icon: 'fa-leaf', title: 'Freshness guaranteed', sub: 'Handcrafted since 1998' },
  { icon: 'fa-shield-halved', title: '100% secure payments', sub: 'Safe trusted checkout' },
];

export function HomeHero() {
  const trackRef = useRef<HTMLDivElement>(null);
  const [mobileCurrent, setMobileCurrent] = useState(0);
  const [desktopCurrent, setDesktopCurrent] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const applyMainTransform = useCallback((index: number) => {
    const track = trackRef.current;
    if (!track || !track.children.length) return;
    const first = track.children[0] as HTMLElement;
    const gap = parseFloat(getComputedStyle(track).gap) || 0;
    const step = first.offsetWidth + gap;
    track.style.transform = `translateX(-${index * step}px)`;
  }, []);

  const moveMobileSlide = useCallback((dir: number) => {
    setMobileCurrent((prev) => (prev + dir + HERO_SLIDES.length) % HERO_SLIDES.length);
  }, []);

  const goToMobile = useCallback((index: number) => {
    setMobileCurrent(((index % HERO_SLIDES.length) + HERO_SLIDES.length) % HERO_SLIDES.length);
  }, []);

  const moveDesktopSlide = useCallback((dir: number) => {
    setDesktopCurrent((prev) => (prev + dir + HERO_SLIDES.length) % HERO_SLIDES.length);
  }, []);

  const goToDesktop = useCallback((index: number) => {
    setDesktopCurrent(((index % HERO_SLIDES.length) + HERO_SLIDES.length) % HERO_SLIDES.length);
  }, []);

  const stopAuto = useCallback(() => {
    if (timerRef.current) {
      clearInterval(timerRef.current);
      timerRef.current = null;
    }
  }, []);

  const startAuto = useCallback(() => {
    stopAuto();
    timerRef.current = setInterval(() => {
      setMobileCurrent((prev) => (prev + 1) % HERO_SLIDES.length);
      setDesktopCurrent((prev) => (prev + 1) % HERO_SLIDES.length);
    }, 5000);
  }, [stopAuto]);

  useEffect(() => {
    applyMainTransform(mobileCurrent);
  }, [mobileCurrent, applyMainTransform]);

  useEffect(() => {
    startAuto();
    return stopAuto;
  }, [startAuto, stopAuto]);

  useEffect(() => {
    const onResize = () => applyMainTransform(mobileCurrent);
    window.addEventListener('resize', onResize);
    return () => window.removeEventListener('resize', onResize);
  }, [mobileCurrent, applyMainTransform]);

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

      {/* Mobile: full-bleed image slider → shop */}
      <div className="sf-firstview-mobile">
        <section className="hp-hero-carousel hp-hero-carousel--image-only relative bg-white overflow-hidden">
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
                    const start = (e.currentTarget as HTMLElement & { _tx?: number })._tx ?? 0;
                    const end = e.changedTouches[0].screenX;
                    if (end < start - 50) moveMobileSlide(1);
                    if (end > start + 50) moveMobileSlide(-1);
                    startAuto();
                  }}
                >
                  {HERO_SLIDES.map((slide, index) => (
                    <div key={slide.id} className="hp-hero-slide flex-shrink-0">
                      <Link
                        href={SHOP_HREF}
                        className="hp-hero-slide__mobile hp-hero-slide__link block w-full h-full"
                        aria-label={`${slide.alt} — Shop now`}
                      >
                        <img
                          src={slide.mobile}
                          className="w-full h-full block object-cover"
                          alt={slide.alt}
                          width={900}
                          height={1120}
                          decoding={index === 0 ? 'sync' : 'async'}
                          loading={index === 0 ? 'eager' : 'lazy'}
                          {...(index === 0 ? { fetchPriority: 'high' as const } : {})}
                        />
                      </Link>
                    </div>
                  ))}
                </div>
              </div>

              <div
                className="hp-hero-carousel__dots absolute bottom-2 left-0 right-0 flex justify-center items-center gap-2 z-20 pb-2"
                id="sliderTracker"
              >
                {HERO_SLIDES.map((slide, i) => (
                  <button
                    key={slide.id}
                    type="button"
                    className={i === mobileCurrent ? 'slider-dot active-pill' : 'slider-dot'}
                    aria-label={`Go to slide ${i + 1}`}
                    aria-current={i === mobileCurrent ? 'true' : undefined}
                    onClick={() => {
                      goToMobile(i);
                      startAuto();
                    }}
                  />
                ))}
              </div>
            </div>
          </div>
        </section>
      </div>

      {/* Desktop: full-bleed image banner → shop */}
      <div className="sf-firstview-desktop sf-shop">
        <div className="sf-shop__grid sf-shop__grid--banner-only">
          <section
            className="sf-banner sf-banner--image-only"
            aria-roledescription="carousel"
            aria-label="Featured promotions"
            onMouseEnter={stopAuto}
            onMouseLeave={startAuto}
          >
            <div className="sf-banner__viewport">
              <div
                className="sf-banner__track"
                style={{ transform: `translateX(-${desktopCurrent * 100}%)` }}
              >
                {HERO_SLIDES.map((slide, index) => (
                  <article
                    key={slide.id}
                    className="sf-banner__slide"
                    aria-hidden={index !== desktopCurrent}
                  >
                    <Link
                      href={SHOP_HREF}
                      className="sf-banner__full-link"
                      aria-label={`${slide.alt} — Shop now`}
                    >
                      <img
                        src={slide.desktop}
                        alt={slide.alt}
                        width={1920}
                        height={780}
                        decoding={index === 0 ? 'sync' : 'async'}
                        loading={index === 0 ? 'eager' : 'lazy'}
                        {...(index === 0 ? { fetchPriority: 'high' as const } : {})}
                      />
                    </Link>
                  </article>
                ))}
              </div>
            </div>

            <button
              type="button"
              className="sf-banner__nav sf-banner__nav--prev"
              aria-label="Previous offer"
              onClick={() => moveDesktopSlide(-1)}
            >
              <i className="fas fa-chevron-left" aria-hidden="true" />
            </button>
            <button
              type="button"
              className="sf-banner__nav sf-banner__nav--next"
              aria-label="Next offer"
              onClick={() => moveDesktopSlide(1)}
            >
              <i className="fas fa-chevron-right" aria-hidden="true" />
            </button>

            <div className="sf-banner__dots" role="tablist" aria-label="Promotion slides">
              {HERO_SLIDES.map((slide, i) => (
                <button
                  key={slide.id}
                  type="button"
                  role="tab"
                  aria-selected={i === desktopCurrent}
                  aria-label={`Show slide ${i + 1}`}
                  className={`sf-banner__dot${i === desktopCurrent ? ' is-active' : ''}`}
                  onClick={() => goToDesktop(i)}
                />
              ))}
            </div>
          </section>
        </div>

        <section className="sf-trust sf-trust--shop" aria-label="Why shop with Sai Flower">
          <div className="sf-trust__inner">
            {TRUST_ITEMS_DESKTOP.map((trust) => (
              <div key={trust.title} className="sf-trust__item">
                <span className="sf-trust__icon" aria-hidden="true">
                  <i className={`fas ${trust.icon}`} />
                </span>
                <div>
                  <p className="sf-trust__title">{trust.title}</p>
                  <p className="sf-trust__sub">{trust.sub}</p>
                </div>
              </div>
            ))}
          </div>
        </section>
      </div>
    </div>
  );
}
