import { HomeHero } from '@/components/home/HomeHero';
import { HomeProductRail } from '@/components/home/HomeProductRail';
import '@/styles/bundled-homepage';
import {
  AboutUsSection,
  CelebrationsCalendarSection,
  FaqSection,
  FavFlowersSection,
  GiftFinderSection,
  HowItWorksSection,
  RelationshipsSection,
  SendGiftsAnywhereSection,
  TailoredOccasionsSection,
  TestimonialsSection,
} from '@/components/home/HomeSections';
import { loadHomepageRails } from '@/lib/homepage-rails';
import Link from 'next/link';
import Script from 'next/script';

/** Cache the homepage HTML + catalog fetches for two minutes. */
export const revalidate = 120;

const STATS = [
  { value: '10K+', label: 'Happy Customers' },
  { value: '4.8★', label: 'Average Rating' },
  { value: '25+', label: 'Years of Craft' },
  { value: 'Same Day', label: 'Delhi NCR Delivery' },
];

const MOBILE_TRUST = [
  {
    icon: 'fa-truck-fast',
    title: 'Same-Day Delivery',
    sub: 'Order by 6 PM, delivered today in Delhi NCR',
  },
  { icon: 'fa-star', title: 'Rated 4.8 / 5', sub: 'Loved by 10,000+ happy customers' },
  { icon: 'fa-leaf', title: 'Freshness Guaranteed', sub: 'Handcrafted with fresh blooms since 1998' },
  { icon: 'fa-shield-halved', title: '100% Secure Payments', sub: 'Safe checkout with trusted gateways' },
];

export default async function HomePage() {
  const rails = await loadHomepageRails();

  return (
    <div className="homepage-premium">
      <div className="hp-home-flow">
        <HomeHero />

        <div className="hp-product-sliders">
          <HomeProductRail
            sliderKey="best-sellers"
            title="Best Sellers"
            viewAllHref="/collection/best-sellers"
            products={rails.bestSellers}
          />
          <HomeProductRail
            sliderKey="same-day-surprises"
            title="Same Day Surprises"
            viewAllHref="/collection/same-day-delivery"
            products={rails.sameDay}
          />
        </div>

        <FavFlowersSection />

        <div className="hp-product-sliders">
          <HomeProductRail
            sliderKey="on-demand"
            title="On Demand"
            viewAllHref="/collection/same-day-delivery"
            products={rails.onDemand}
          />
        </div>

        <CelebrationsCalendarSection />
        <TailoredOccasionsSection products={rails.birthday} />

        {/* Mobile only — desktop keeps trust in hero first-view */}
        <div className="hp-flow--trust-mobile">
          <section className="lx-trustbar" aria-label="Why shop with Sai Flower">
            <div className="lx-trustbar__inner">
              {MOBILE_TRUST.map((trust) => (
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

        <div className="hp-product-sliders">
          <HomeProductRail
            sliderKey="newly-added"
            title="Newly Added"
            viewAllHref="/collection/new-arrivals"
            products={rails.newlyAdded}
          />
        </div>

        <RelationshipsSection />

        <GiftFinderSection />

        <div className="hp-product-sliders">
          <HomeProductRail
            sliderKey="occasions"
            title="Occasions"
            viewAllHref="/occasion/birthday"
            products={rails.occasions}
          />
        </div>

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
      </div>

      <Script src="/assets/js/homepage-premium.js?v=1" strategy="afterInteractive" />
      <Script src="/assets/js/homepage-luxe.js?v=1" strategy="afterInteractive" />
    </div>
  );
}
