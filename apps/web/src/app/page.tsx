import Link from 'next/link';
import Script from 'next/script';
import { HomeHero } from '@/components/home/HomeHero';
import { HomeProductRail } from '@/components/home/HomeProductRail';
import {
  AboutUsSection,
  CelebrationsCalendarSection,
  ExploreLuxurySection,
  FaqSection,
  FavFlowersSection,
  GiftFinderSection,
  HowItWorksSection,
  RelationshipsSection,
  SameDayTilesSection,
  SendGiftsAnywhereSection,
  TailoredOccasionsSection,
  TestimonialsSection,
} from '@/components/home/HomeSections';
import { fetchLandingBouquets } from '@/lib/bouquet';
import type { Product } from '@/lib/types';

const PROMO_CARDS = [
  {
    badge: 'Gift Box',
    title: 'Awesome Gift Box Collections',
    cta: 'Shop Now',
    href: '/collection/hampers',
    theme: 'sky',
    img: '/uploads/sections/img_69affbff9fce1_img69a6ad335b957WhatsAppImage20260303at23841PM.webp',
    alt: 'Gift box bouquet collection',
  },
  {
    badge: 'Occasion Gift',
    title: 'Best Occasion Gift Collections',
    cta: 'Discover Now',
    href: '/occasion/birthday',
    theme: 'mint',
    img: '/uploads/sections/img_69c123f37096e_Screenshot20260313001120SamsungNotes.webp',
    alt: 'Colourful occasion bouquet',
  },
  {
    badge: 'Hot Sale',
    title: 'Combo Sets Up To 50% Off',
    cta: 'Discover Now',
    href: '/collection/flower-combos',
    theme: 'blush',
    img: '/uploads/sections/img_69c12ca4d6812_img69b5447deae89WhatsAppImage20260314at31518PM.webp',
    alt: 'Pink tulip combo set',
  },
];

const STATS = [
  { value: '10K+', label: 'Happy Customers' },
  { value: '4.8★', label: 'Average Rating' },
  { value: '25+', label: 'Years of Craft' },
  { value: 'Same Day', label: 'Delhi NCR Delivery' },
];

async function loadProducts(
  params: Record<string, string | number | undefined>,
): Promise<Product[]> {
  return fetchLandingBouquets({
    limit: Number(params.limit ?? 12),
    sort: String(params.sort ?? 'bestseller'),
    search: params.search != null ? String(params.search) : undefined,
  });
}

export default async function HomePage() {
  const [bestSellers, sameDay, occasions, everyOccasion, onDemand, newlyAdded, birthday] =
    await Promise.all([
      loadProducts({ type: 'flower', limit: 12, sort: 'bestseller' }),
      loadProducts({ type: 'flower', limit: 10, sort: 'bestseller', search: 'same' }),
      loadProducts({ type: 'flower', limit: 10, sort: 'bestseller', search: 'birthday' }),
      loadProducts({ type: 'flower', limit: 10, sort: 'bestseller', search: 'anniversary' }),
      loadProducts({ type: 'flower', limit: 10, sort: 'bestseller', search: 'express' }),
      loadProducts({ type: 'flower', limit: 10, sort: 'newest' }),
      loadProducts({ type: 'flower', limit: 10, sort: 'bestseller', search: 'birthday' }),
    ]);

  const sameDayRail = sameDay.length > 0 ? sameDay : bestSellers.slice(0, 10);
  const occasionsRail = occasions.length > 0 ? occasions : bestSellers.slice(0, 10);
  const everyOccasionRail = everyOccasion.length > 0 ? everyOccasion : bestSellers.slice(0, 10);
  const onDemandRail = onDemand.length > 0 ? onDemand : bestSellers.slice().reverse().slice(0, 10);
  const newlyAddedRail = newlyAdded.length > 0 ? newlyAdded : bestSellers.slice(0, 10);
  const birthdayProducts = birthday.length > 0 ? birthday : bestSellers.slice(0, 10);

  return (
    <div className="homepage-premium">
      <HomeHero />

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

      <div className="hp-product-sliders">
        <HomeProductRail
          sliderKey="best-sellers"
          title="Best Sellers"
          subtitle="Our most loved bouquets — trusted by thousands of customers."
          viewAllHref="/collection/best-sellers"
          products={bestSellers}
        />
        <HomeProductRail
          sliderKey="same-day-surprises"
          title="Same Day Surprises"
          subtitle="Last-minute blooms delivered today — surprise them before sunset."
          viewAllHref="/collection/same-day-delivery"
          products={sameDayRail}
        />
        <HomeProductRail
          sliderKey="occasions"
          title="Occasions"
          subtitle="Perfect picks for birthdays, anniversaries, weddings & more."
          viewAllHref="/occasion/birthday"
          products={occasionsRail}
        />
        <HomeProductRail
          sliderKey="for-every-occasions"
          title="For Every Occasions"
          subtitle="Thoughtful gifts for birthdays, love, weddings & every celebration."
          viewAllHref="/occasion/anniversary"
          products={everyOccasionRail}
        />
      </div>

      <FavFlowersSection />
      <TailoredOccasionsSection products={birthdayProducts} />

      <div className="hp-product-sliders">
        <HomeProductRail
          sliderKey="on-demand"
          title="On Demand"
          subtitle="Same-day & express delivery when timing matters most."
          viewAllHref="/collection/same-day-delivery"
          products={onDemandRail}
        />
      </div>

      <SameDayTilesSection />
      <CelebrationsCalendarSection />
      <RelationshipsSection />
      <ExploreLuxurySection />

      <div className="hp-product-sliders">
        <HomeProductRail
          sliderKey="newly-added"
          title="Newly Added"
          subtitle="Fresh arrivals — discover the latest from our studio."
          viewAllHref="/collection/new-arrivals"
          products={newlyAddedRail}
        />
      </div>

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
      <HowItWorksSection />
      <FaqSection />

      <section className="lx-final-cta" aria-labelledby="lx-final-cta-title">
        <div className="lx-final-cta__shell">
          <p className="lx-final-cta__kicker">Handcrafted Since 1998</p>
          <h2 id="lx-final-cta-title" className="lx-final-cta__title">
            Make Someone&apos;s Day Bloom Today
          </h2>
          <p className="lx-final-cta__sub">
            Order before 6 PM for same-day delivery in Delhi NCR. Fresh, handcrafted bouquets — delivered with love
            and a personal note.
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
    </div>
  );
}
