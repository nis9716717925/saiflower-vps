'use client';

import Link from 'next/link';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { HomepageSlide } from '@/lib/homepage-slides';

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
    img: '/assets/images/hero/side-pink-roses.webp',
  },
  {
    theme: 'mint',
    kicker: 'Same Day',
    title: 'Surprise Them<br>Before Sunset',
    cta: 'Order Now',
    href: '/collection/same-day-delivery',
    img: '/assets/images/hero/side-same-day.webp',
  },
  {
    theme: 'blush',
    kicker: 'LUXE',
    title: 'Premium Bouquets<br>For Special Moments',
    cta: 'Explore Luxe',
    href: '/collection/luxury-flowers',
    img: '/assets/images/hero/side-luxe-bouquet.webp',
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
    img: '/assets/images/hero/main-same-day.webp',
  },
  {
    theme: 'cream',
    kicker: 'Sai Flower',
    title: 'Premium Blooms,<br>Curated for You',
    subtitle: 'Handpicked luxury bouquets for every special moment.',
    cta: 'Order Now',
    href: '/collection/premium-bouquets',
    img: '/assets/images/hero/main-premium-blooms.webp',
  },
  {
    theme: 'blush',
    kicker: 'Sai Flower',
    title: 'Birthday Joy,<br>Gift-Wrapped',
    subtitle: 'Curated blooms, cakes & more for thoughtful celebrations.',
    cta: 'Shop Now',
    href: '/occasion/birthday',
    img: '/assets/images/hero/main-birthday-joy.webp',
  },
];

const MAIN_BANNERS = [
  {
    badge: 'Same-day delivery',
    title: 'Order by 6 PM, delivered today',
    subtitle: 'Fresh handcrafted bouquets across Delhi NCR.',
    offer: 'Free message card',
    cta: 'Shop same-day',
    href: '/collection/same-day-delivery',
    img: '/assets/images/hero/main-same-day.webp',
    tone: 'mint',
  },
  {
    badge: 'Birthday bestsellers',
    title: 'Make birthdays unforgettable',
    subtitle: 'Bouquets, cakes & combos starting under ₹999.',
    offer: 'Rated 4.8★',
    cta: 'Shop birthday gifts',
    href: '/occasion/birthday',
    img: '/assets/images/hero/main-birthday-joy.webp',
    tone: 'blush',
  },
  {
    badge: 'Premium collection',
    title: 'Luxe blooms for special moments',
    subtitle: 'Designer arrangements crafted to impress.',
    offer: 'Handcrafted fresh',
    cta: 'Explore luxe',
    href: '/collection/luxury-flowers',
    img: '/assets/images/hero/main-premium-blooms.webp',
    tone: 'cream',
  },
];

const SIDE_OFFERS = [
  {
    badge: 'Best seller',
    title: 'Rose bouquets',
    sub: 'Classic & romantic picks',
    cta: 'Shop now',
    href: '/flowers/roses',
    img: '/assets/images/hero/side-pink-roses.webp',
    tone: 'rose',
  },
  {
    badge: 'Combo deals',
    title: 'Flowers + cake',
    sub: 'Ready-to-gift sets',
    cta: 'View combos',
    href: '/collection/flower-combos',
    img: '/assets/images/hero/side-luxe-bouquet.webp',
    tone: 'gold',
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

function plainTitle(html: string): string {
  return html.replace(/<br\s*\/?>/gi, ' ').replace(/<[^>]+>/g, '');
}

function buildSlidesFromDb(dbSlides: HomepageSlide[]) {
  if (!dbSlides.length) {
    return { mainSlides: MAIN_SLIDES, mainBanners: MAIN_BANNERS };
  }

  const mainSlides = dbSlides.map((slide, index) => {
    const defaults = MAIN_SLIDES[index % MAIN_SLIDES.length];
    return {
      ...defaults,
      img: slide.mobileImage || slide.image,
      href: slide.link || defaults.href,
    };
  });

  const mainBanners = dbSlides.map((slide, index) => {
    const defaults = MAIN_BANNERS[index % MAIN_BANNERS.length];
    return {
      ...defaults,
      img: slide.image,
      href: slide.link || defaults.href,
    };
  });

  return { mainSlides, mainBanners };
}

type HomeHeroProps = {
  slides?: HomepageSlide[];
};

export function HomeHero({ slides = [] }: HomeHeroProps) {
  const { mainSlides, mainBanners } = useMemo(() => buildSlidesFromDb(slides), [slides]);
  const trackRef = useRef<HTMLDivElement>(null);
  const sideTrackRef = useRef<HTMLDivElement>(null);
  const [mobileCurrent, setMobileCurrent] = useState(0);
  const [desktopCurrent, setDesktopCurrent] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const sideIndex = mobileCurrent % SIDE_SLIDES.length;

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

  const moveMobileSlide = useCallback((dir: number) => {
    setMobileCurrent((prev) => (prev + dir + mainSlides.length) % mainSlides.length);
  }, [mainSlides.length]);

  const goToMobile = useCallback((index: number) => {
    setMobileCurrent(((index % mainSlides.length) + mainSlides.length) % mainSlides.length);
  }, [mainSlides.length]);

  const moveDesktopSlide = useCallback((dir: number) => {
    setDesktopCurrent((prev) => (prev + dir + mainBanners.length) % mainBanners.length);
  }, [mainBanners.length]);

  const goToDesktop = useCallback((index: number) => {
    setDesktopCurrent(
      ((index % mainBanners.length) + mainBanners.length) % mainBanners.length,
    );
  }, [mainBanners.length]);

  const stopAuto = useCallback(() => {
    if (timerRef.current) {
      clearInterval(timerRef.current);
      timerRef.current = null;
    }
  }, []);

  const startAuto = useCallback(() => {
    stopAuto();
    timerRef.current = setInterval(() => moveMobileSlide(1), 3000);
  }, [moveMobileSlide, stopAuto]);

  useEffect(() => {
    applyMainTransform(mobileCurrent);
    applySideTransform(sideIndex);
  }, [mobileCurrent, sideIndex, applyMainTransform, applySideTransform]);

  useEffect(() => {
    const onResize = () => {
      applyMainTransform(mobileCurrent);
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
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) return;
    const timer = setInterval(() => moveDesktopSlide(1), 4500);
    return () => clearInterval(timer);
  }, [moveDesktopSlide]);

  useEffect(() => {
    (window as unknown as { moveSlide?: (dir: number) => void }).moveSlide = moveMobileSlide;
    return () => {
      delete (window as unknown as { moveSlide?: (dir: number) => void }).moveSlide;
    };
  }, [moveMobileSlide]);

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

      {/* Mobile: previous image carousel + trust cards */}
      <div className="sf-firstview-mobile">
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
                        if (end < start - 50) moveMobileSlide(1);
                        if (end > start + 50) moveMobileSlide(-1);
                        startAuto();
                      }}
                    >
                      {mainSlides.map((slide, index) => (
                        <div
                          key={`${slide.img}-${index}`}
                          className="hp-hero-slide flex-shrink-0"
                          data-theme={slide.theme}
                        >
                          <Link
                            href={slide.href}
                            className="hp-hero-slide__mobile block w-full h-full"
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
                        </div>
                      ))}
                    </div>
                  </div>

                  <div
                    className="hp-hero-carousel__dots lx-hero-split__dots absolute bottom-2 left-0 right-0 flex justify-center items-center gap-2 z-20 pb-2"
                    id="sliderTracker"
                  >
                    {mainSlides.map((slide, i) => (
                      <div
                        key={slide.img}
                        className={i === mobileCurrent ? 'slider-dot active-pill' : 'slider-dot'}
                        onClick={() => {
                          if (i !== mobileCurrent) {
                            goToMobile(i);
                            startAuto();
                          }
                        }}
                        role="button"
                        tabIndex={0}
                        aria-label={`Go to slide ${i + 1}`}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            goToMobile(i);
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

      </div>

      {/* Desktop: updated shop promo grid */}
      <div className="sf-firstview-desktop sf-shop">
        <div className="sf-shop__grid">
          <aside className="sf-shop__sides" aria-label="Featured offers">
            {SIDE_OFFERS.map((offer) => (
              <Link
                key={offer.href}
                href={offer.href}
                className={`sf-offer sf-offer--${offer.tone}`}
              >
                <span className="sf-offer__copy">
                  <span className="sf-offer__badge">{offer.badge}</span>
                  <span className="sf-offer__title">{offer.title}</span>
                  <span className="sf-offer__sub">{offer.sub}</span>
                  <span className="sf-offer__cta">
                    {offer.cta} <i className="fas fa-arrow-right" aria-hidden="true" />
                  </span>
                </span>
                <span className="sf-offer__media">
                  <img src={offer.img} alt={offer.title} width={320} height={320} loading="lazy" />
                </span>
              </Link>
            ))}
          </aside>

          <section
            className="sf-banner"
            aria-roledescription="carousel"
            aria-label="Main promotions"
          >
            <div className="sf-banner__viewport">
              <div
                className="sf-banner__track"
                style={{ transform: `translateX(-${desktopCurrent * 100}%)` }}
              >
                {mainBanners.map((banner, index) => (
                  <article
                    key={`${banner.img}-${index}`}
                    className={`sf-banner__slide sf-banner__slide--${banner.tone}`}
                    aria-hidden={index !== desktopCurrent}
                  >
                    <div className="sf-banner__copy">
                      <span className="sf-banner__badge">{banner.badge}</span>
                      <h2 className="sf-banner__title">{banner.title}</h2>
                      <p className="sf-banner__sub">{banner.subtitle}</p>
                      <div className="sf-banner__meta">
                        <span>
                          <i className="fas fa-bolt" aria-hidden="true" /> {banner.offer}
                        </span>
                        <span>
                          <i className="fas fa-truck-fast" aria-hidden="true" /> Delhi NCR
                        </span>
                      </div>
                      <Link href={banner.href} className="sf-banner__cta">
                        {banner.cta}
                        <i className="fas fa-arrow-right" aria-hidden="true" />
                      </Link>
                    </div>
                    <div className="sf-banner__media">
                      <img
                        src={banner.img}
                        alt={banner.title}
                        width={900}
                        height={700}
                        decoding={index === 0 ? 'sync' : 'async'}
                        loading={index === 0 ? 'eager' : 'lazy'}
                        {...(index === 0 ? { fetchPriority: 'high' as const } : {})}
                      />
                    </div>
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
              {mainBanners.map((banner, i) => (
                <button
                  key={banner.title}
                  type="button"
                  role="tab"
                  aria-selected={i === desktopCurrent}
                  aria-label={`Show offer ${i + 1}`}
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
