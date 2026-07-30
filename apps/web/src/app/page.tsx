import Link from 'next/link';
import Script from 'next/script';
import {
  AboutUsSection,
  CelebrationsCalendarSection,
  ExploreLuxurySection,
  FavFlowersSection,
  GiftFinderSection,
  RelationshipsSection,
  SameDayTilesSection,
  SendGiftsAnywhereSection,
  TailoredOccasionsSection,
  TestimonialsSection,
} from '@/components/home/HomeSections';
import { ProductCard } from '@/components/shop/ProductCard';
import { fetchProducts } from '@/lib/api';

const CATEGORY_ICONS = [
  { label: 'Birthday', href: '/occasion/birthday', icon: 'fa-cake-candles' },
  { label: 'Anniversary', href: '/occasion/anniversary', icon: 'fa-heart' },
  { label: 'Same Day', href: '/collection/same-day-delivery', icon: 'fa-bolt' },
  { label: 'Roses', href: '/search-results?q=roses', icon: 'fa-spa' },
  { label: 'Wedding', href: '/occasion/wedding', icon: 'fa-ring' },
  { label: 'Plants', href: '/collection/plants', icon: 'fa-leaf' },
  { label: 'Personalised', href: '/personalized', icon: 'fa-pen-nib' },
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

const PROMO_CARDS = [
  {
    badge: 'Gift Box',
    title: 'Awesome Gift Box Collections',
    cta: 'Shop Now',
    href: '/collection/hampers',
    theme: 'sky',
    img: '/assets/images/hero/side-luxe-bouquet.jpg',
    alt: 'Gift box bouquet collection',
  },
  {
    badge: 'Occasion Gift',
    title: 'Best Occasion Gift Collections',
    cta: 'Discover Now',
    href: '/occasion/birthday',
    theme: 'mint',
    img: '/assets/images/hero/main-birthday-joy.jpg',
    alt: 'Colourful occasion bouquet',
  },
  {
    badge: 'Hot Sale',
    title: 'Combo Sets Up To 50% Off',
    cta: 'Discover Now',
    href: '/collection/flower-combos',
    theme: 'blush',
    img: '/assets/images/hero/main-premium-blooms.jpg',
    alt: 'Pink tulip combo set',
  },
];

const SIDE_SLIDES = [
  {
    theme: 'lavender',
    kicker: 'Sai Flower',
    title: 'Fresh Flowers,<br>Delivered with Love',
    cta: 'Shop Now',
    href: '/flowers',
    img: '/assets/images/hero/side-pink-roses.jpg',
    bg: 'linear-gradient(165deg, #ece4f8 0%, #ddd5ee 55%, #d4cbe8 100%)',
  },
  {
    theme: 'mint',
    kicker: 'Same Day',
    title: 'Surprise Them<br>Before Sunset',
    cta: 'Order Now',
    href: '/collection/same-day-delivery',
    img: '/assets/images/hero/side-same-day.jpg',
    bg: 'linear-gradient(165deg, #e4f3ec 0%, #d4ebe0 55%, #c8e4d6 100%)',
  },
];

const MAIN_BANNER = {
  kicker: 'Sai Flower',
  title: 'Same Day Delivery<br>in Delhi',
  subtitle: 'Handpicked luxury bouquets for all special moments.',
  cta: 'Shop Now',
  href: '/collection/same-day-delivery',
  img: '/assets/images/hero/main-same-day.jpg',
  bg: 'linear-gradient(135deg, #fdf0e8 0%, #fce4d6 100%)',
};

const STATS = [
  { value: '10K+', label: 'Happy Customers' },
  { value: '4.8★', label: 'Average Rating' },
  { value: '25+', label: 'Years of Craft' },
  { value: 'Same Day', label: 'Delhi NCR Delivery' },
];

export default async function HomePage() {
  let bestSellers: Awaited<ReturnType<typeof fetchProducts>>['items'] = [];
  try {
    const data = await fetchProducts({ type: 'flower', limit: 12, sort: 'bestseller' });
    bestSellers = data.items;
  } catch {
    bestSellers = [];
  }

  return (
    <>
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
                <div className="lx-hero-side-slider__track" id="sideSliderTrack">
                  {SIDE_SLIDES.map((slide) => (
                    <div key={slide.theme} className="lx-hero-side-slide">
                      <Link
                        href={slide.href}
                        className="lx-hero-side-card"
                        style={{ background: slide.bg }}
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
                          <img src={slide.img} alt={slide.kicker} width={400} height={400} loading="eager" />
                        </span>
                      </Link>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </aside>

          <div className="lx-hero-split__main">
            <Link
              href={MAIN_BANNER.href}
              className="lx-hero-banner"
              style={{ background: MAIN_BANNER.bg }}
            >
              <span className="lx-hero-banner__copy">
                <span className="lx-hero-banner__kicker">{MAIN_BANNER.kicker}</span>
                <span
                  className="lx-hero-banner__title"
                  dangerouslySetInnerHTML={{ __html: MAIN_BANNER.title }}
                />
                <span className="lx-hero-banner__sub">{MAIN_BANNER.subtitle}</span>
                <span className="lx-hero-banner__cta">
                  {MAIN_BANNER.cta} <i className="fas fa-arrow-right" aria-hidden="true" />
                </span>
              </span>
              <span
                className="lx-hero-banner__img"
                style={{ backgroundImage: `url('${MAIN_BANNER.img}')` }}
              >
                <img src={MAIN_BANNER.img} alt="Same day flower delivery" width={800} height={600} />
              </span>
            </Link>
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

      <section className="lx-promo-trio" aria-label="Featured offers">
        <div className="lx-promo-trio__inner">
          {PROMO_CARDS.map((card) => (
            <Link
              key={card.badge}
              href={card.href}
              className={`lx-promo-card lx-promo-card--${card.theme}`}
            >
              <span className="lx-promo-card__copy">
                <span className="lx-promo-card__badge">{card.badge}</span>
                <span className="lx-promo-card__title">{card.title}</span>
                <span className="lx-promo-card__cta">{card.cta}</span>
              </span>
              <span className="lx-promo-card__img">
                <img src={card.img} alt={card.alt} width={280} height={280} loading="lazy" />
              </span>
            </Link>
          ))}
        </div>
      </section>

      <section className="hp-section" aria-labelledby="hp-best-sellers">
        <div className="hp-container">
          <div className="hp-section-head">
            <h2 id="hp-best-sellers" className="hp-section-title">
              Best Sellers
            </h2>
            <p className="hp-section-sub">Our most-loved bouquets — handpicked for Delhi NCR delivery.</p>
          </div>
          {bestSellers.length > 0 ? (
            <div className="sp-grid">
              {bestSellers.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          ) : (
            <p className="text-center text-slate-500 py-8">Browse our full collection of fresh flowers.</p>
          )}
          <div className="hp-product-slider-footer mt-8 text-center">
            <Link href="/flowers" className="hp-occasion-viewall">
              View all flowers <i className="fas fa-arrow-right" aria-hidden="true" />
            </Link>
          </div>
        </div>
      </section>

      <FavFlowersSection />
      <TailoredOccasionsSection />
      <SameDayTilesSection />
      <CelebrationsCalendarSection />
      <RelationshipsSection />
      <ExploreLuxurySection />
      <GiftFinderSection />
      <SendGiftsAnywhereSection />
      <AboutUsSection />

      <section className="lx-stats" aria-label="Sai Flower at a glance">
        <div className="lx-stats__inner">
          {STATS.map((stat) => (
            <div key={stat.label} className="lx-stats__item">
              <span className="lx-stats__value">{stat.value}</span>
              <span className="lx-stats__label">{stat.label}</span>
            </div>
          ))}
        </div>
      </section>

      <TestimonialsSection />

      <section className="lx-faq" aria-labelledby="lx-faq-title">
        <div className="lx-faq__inner">
          <div className="lx-section-head">
            <span className="lx-kicker">Good to Know</span>
            <h2 id="lx-faq-title">Frequently Asked Questions</h2>
          </div>
          <div className="lx-faq__list">
            <details className="lx-faq__item">
              <summary className="lx-faq__q">Do you offer same-day flower delivery in Delhi NCR?</summary>
              <div className="lx-faq__a">
                Yes — place your order before 6 PM and we deliver the same day across Delhi NCR. Express and
                midnight delivery slots are also available on select products.
              </div>
            </details>
            <details className="lx-faq__item">
              <summary className="lx-faq__q">How do you keep the flowers fresh during delivery?</summary>
              <div className="lx-faq__a">
                Every bouquet is made to order with freshly cut blooms, hydrated right up to dispatch and
                packaged carefully so it arrives looking its best.
              </div>
            </details>
            <details className="lx-faq__item">
              <summary className="lx-faq__q">Can I add a cake or personal note to my order?</summary>
              <div className="lx-faq__a">
                Absolutely. Pair your flowers with <Link href="/cakes">cakes</Link> and{' '}
                <Link href="/gifts">gift hampers</Link> at checkout, and include a free personalised message
                card.
              </div>
            </details>
          </div>
        </div>
      </section>

      <section className="lx-final-cta" aria-labelledby="lx-final-cta-title">
        <div className="lx-final-cta__shell">
          <p className="lx-final-cta__kicker">Handcrafted Since 1998</p>
          <h2 id="lx-final-cta-title" className="lx-final-cta__title">
            Make Someone&apos;s Day Bloom Today
          </h2>
          <p className="lx-final-cta__sub">
            Order before 6 PM for same-day delivery in Delhi NCR. Fresh, handcrafted bouquets — delivered
            with love and a personal note.
          </p>
          <div className="lx-final-cta__actions">
            <Link href="/flowers" className="lx-btn-primary">
              Shop Fresh Flowers <i className="fas fa-arrow-right" aria-hidden="true" />
            </Link>
            <Link href="/contact" className="lx-btn-secondary">
              Plan a Wedding or Event
            </Link>
          </div>
          <p className="lx-final-cta__note">
            <i className="fas fa-shield-halved" aria-hidden="true" /> 100% secure payments &nbsp;·&nbsp;
            Freshness guaranteed &nbsp;·&nbsp; Rated 4.8/5 by 10,000+ customers
          </p>
        </div>
      </section>

      <Script src="/assets/js/homepage-premium.js?v=1" strategy="afterInteractive" />
      <Script src="/assets/js/homepage-luxe.js?v=1" strategy="afterInteractive" />
    </>
  );
}
