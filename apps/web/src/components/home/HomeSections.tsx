import Link from 'next/link';
import { HomeOccasionCard } from '@/components/home/HomeProductRail';
import type { Product } from '@/lib/types';

/** Static homepage sections mirrored from PHP partials (same classes / copy). */

const FAV_FLOWERS = [
  {
    title: 'Carnations',
    href: '/flowers/carnations',
    img: '/uploads/sections/img_6998729febff3_IMG3579scaled.webp',
  },
  {
    title: 'Orchids',
    href: '/flowers/orchids',
    img: '/uploads/sections/img_699872f585703_ob7q8ewBZK1758599912308.webp',
  },
  {
    title: 'Red Roses',
    href: '/flowers/roses',
    img: '/uploads/sections/img_699dccd658a6d_Screenshot20250911031911SamsungNotes.webp',
  },
  {
    title: 'Lilies',
    href: '/flowers/lilies',
    img: '/uploads/sections/img_699dcd3bc3e78_Screenshot20250906175129SamsungNotes.webp',
  },
  {
    title: 'Sunflower',
    href: '/flowers/sunflowers',
    img: '/uploads/sections/img_69b3fc46e7fd4_WhatsAppImage20260313at52923PM.webp',
  },
  {
    title: 'Tulip',
    href: '/flowers/tulips',
    img: '/uploads/sections/img_69bbcddb5096e_img69b909c6a9eb8WhatsAppImage20260316at54826PM.webp',
  },
];

const SAME_DAY = [
  {
    label: 'Flowers',
    href: '/collection/same-day-delivery',
    img: '/uploads/sections/img_69b00d7d6b073_img69a6aa4b1d253WhatsAppImage20260303at23112PM.webp',
  },
  {
    label: 'Cakes',
    href: '/cakes',
    img: '/uploads/circles/img_69c0d7a4a7e88_Cakewithflowing202603231132.webp',
  },
  {
    label: 'Plants',
    href: '/collection/plants',
    img: '/uploads/circles/img_69c0d06492a19_Untitleddesign8.webp',
  },
  {
    label: 'Chocolates',
    href: '/search-results?q=chocolate',
    img: 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?auto=format&fit=crop&w=400&h=400&q=80',
  },
  {
    label: 'Personalised',
    href: '/personalized',
    img: 'https://images.unsplash.com/photo-1513201099705-a9746e1e201f?auto=format&fit=crop&w=400&h=400&q=80',
  },
];

const RELATIONSHIPS = [
  { label: 'Him', slug: 'him', img: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&h=400&q=80' },
  { label: 'Her', slug: 'her', img: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&h=400&q=80' },
  { label: 'Kids', slug: 'kids', img: 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=400&h=400&q=80' },
  { label: 'Friend', slug: 'friends', img: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=400&h=400&q=80' },
  { label: 'Girlfriend', slug: 'girlfriend', img: 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=400&h=400&q=80' },
  { label: 'Boyfriend', slug: 'boyfriend', img: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&h=400&q=80' },
  { label: 'Wife', slug: 'wife', img: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&h=400&q=80' },
  { label: 'Husband', slug: 'husband', img: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&h=400&q=80' },
];

const LUXURY = [
  {
    label: 'Luxe Vibe',
    sub: 'Statement arrangements',
    href: '/collection/luxury-flowers',
    img: '/uploads/sections/img_69c127d60bae3_img69a6d18fd207dChatGPTImageMar32026054446PM.webp',
  },
  {
    label: 'Flowers',
    sub: 'Fresh designer bouquets',
    href: '/flowers',
    img: '/uploads/sections/img_69affbff9fce1_img69a6ad335b957WhatsAppImage20260303at23841PM.webp',
  },
  {
    label: 'Cakes',
    sub: 'Baked for celebrations',
    href: '/cakes',
    img: '/uploads/circles/img_69c0d7a4a7e88_Cakewithflowing202603231132.webp',
  },
  {
    label: 'Hampers',
    sub: 'Curated gift boxes',
    href: '/collection/hampers',
    img: '/uploads/circles/img_69c0d0e7c2d77_Untitleddesign9.webp',
  },
  {
    label: 'Plants',
    sub: 'Green & evergreen gifts',
    href: '/collection/plants',
    img: '/uploads/circles/img_69c0d06492a19_Untitleddesign8.webp',
  },
];

const CELEBRATIONS = [
  { date: '1ST JAN', title: "New Year's Day", image: '/celebrations/new-year.webp', slug: 'new-years-day' },
  { date: '7TH FEB', title: 'Rose Day', image: '/celebrations/rose-day.webp', slug: 'rose-day' },
  { date: '8TH FEB', title: 'Propose Day', image: '/celebrations/propose-day.webp', slug: 'propose-day' },
  { date: '9TH FEB', title: 'Chocolate Day', image: '/celebrations/chocolate-day.webp', slug: 'chocolate-day' },
  { date: '10TH FEB', title: 'Teddy Day', image: '/celebrations/teddy-day.webp', slug: 'teddy-day' },
  { date: '11TH FEB', title: 'Promise Day', image: '/celebrations/promise-day.webp', slug: 'promise-day' },
  { date: '12TH FEB', title: 'Hug Day', image: '/celebrations/hug-day.webp', slug: 'hug-day' },
  { date: '13TH FEB', title: 'Kiss Day', image: '/celebrations/kiss-day.webp', slug: 'kiss-day' },
  { date: '14TH FEB', title: "Valentine's Day", image: '/celebrations/valentines-day.webp', slug: 'valentines-day' },
  { date: '8TH MAR', title: "Women's Day", image: '/celebrations/womens-day.webp', slug: 'womens-day' },
  { date: '10TH MAY', title: "Mother's Day", image: '/celebrations/mothers-day.webp', slug: 'mothers-day' },
  { date: '21ST JUN', title: "Father's Day", image: '/celebrations/fathers-day.webp', slug: 'fathers-day' },
];

const OCCASION_TABS = [
  { key: 'birthday', label: 'Birthday', icon: 'fa-cake-candles', href: '/occasion/birthday' },
  { key: 'anniversary', label: 'Anniversary', icon: 'fa-ring', href: '/occasion/anniversary' },
  { key: 'love', label: 'Love', icon: 'fa-heart', href: '/occasion/love' },
  { key: 'wedding', label: 'Wedding', icon: 'fa-champagne-glasses', href: '/occasion/wedding' },
  { key: 'congratulations', label: 'Congratulations', icon: 'fa-party-horn', href: '/occasion/congratulations' },
  { key: 'sympathy', label: 'Sympathy', icon: 'fa-dove', href: '/occasion/sympathy' },
];

const GIFT_FINDER = [
  {
    label: 'Occasion',
    subtitle: 'Birthday, wedding & more',
    icon: 'fa-calendar-heart',
    link: '/celebration-calendar',
    image: 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=400&h=400&q=80',
  },
  {
    label: 'Gift Type',
    subtitle: 'Flowers, cakes & hampers',
    icon: 'fa-gift',
    link: '/collection/hampers',
    image: 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=400&h=400&q=80',
  },
  {
    label: 'Recipient',
    subtitle: 'For her, him & family',
    icon: 'fa-user-group',
    link: '/relation/her',
    image: 'https://images.unsplash.com/photo-1511988617509-a57c8a288659?auto=format&fit=crop&w=400&h=400&q=80',
  },
  {
    label: 'Budget',
    subtitle: 'Under ₹999 & more',
    icon: 'fa-indian-rupee-sign',
    link: '/collection/budget-flowers',
    image: 'https://images.unsplash.com/photo-1607083206968-13611e3d76db?auto=format&fit=crop&w=400&h=400&q=80',
  },
];

const CITIES = [
  { name: 'Delhi', link: '/flower-delivery-in-delhi', image: 'https://images.unsplash.com/photo-1587474260584-136574528ed5?auto=format&fit=crop&w=600&h=450&q=80' },
  { name: 'Mumbai', link: '/flowers', image: 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?auto=format&fit=crop&w=600&h=450&q=80' },
  { name: 'Ahmedabad', link: '/flowers', image: 'https://images.unsplash.com/photo-1604147495798-57beb5d6af73?auto=format&fit=crop&w=600&h=450&q=80' },
  { name: 'Bengaluru', link: '/flowers', image: 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?auto=format&fit=crop&w=600&h=450&q=80' },
  { name: 'Chennai', link: '/flowers', image: 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=600&h=450&q=80' },
  { name: 'Hyderabad', link: '/flowers', image: 'https://images.unsplash.com/photo-1533050487297-09b450131914?auto=format&fit=crop&w=600&h=450&q=80' },
  { name: 'Kolkata', link: '/flowers', image: 'https://images.unsplash.com/photo-1558431382-27e303142255?auto=format&fit=crop&w=600&h=450&q=80' },
  { name: 'Pune', link: '/flowers', image: 'https://images.unsplash.com/photo-1562979314-bee7453e911c?auto=format&fit=crop&w=600&h=450&q=80' },
];

const TESTIMONIALS = [
  {
    name: 'Priya Sharma',
    where: 'Lajpat Nagar, Delhi',
    source: 'fab fa-google',
    text: "Ordered a red rose bouquet for my parents' anniversary at 2 PM and it reached them by evening — fresher than anything I've bought from a store. The wrapping was gorgeous.",
  },
  {
    name: 'Rohit Verma',
    where: 'Gurugram',
    source: 'fab fa-google',
    text: "Sai Flower handled the entire décor for my sister's wedding — stage backdrop, centrepieces, everything. Guests kept asking who the florist was. Truly professional team.",
  },
  {
    name: 'Anjali Mehta',
    where: 'South Extension, Delhi',
    source: 'fab fa-instagram',
    text: 'The midnight birthday surprise was perfect. Cake and orchids arrived at 12 sharp with a handwritten note. My husband was speechless. Will order again and again!',
  },
  {
    name: 'Karan Singh',
    where: 'Noida',
    source: 'fab fa-google',
    text: "I've tried the big gifting sites — nothing matches the freshness here. You can tell the flowers are cut the same day. Checkout was quick and delivery updates were on point.",
  },
  {
    name: 'Sneha Kapoor',
    where: 'Defence Colony, Delhi',
    source: 'fab fa-facebook',
    text: 'Our office orders weekly arrangements from Sai Flower for the reception. Consistently beautiful, always on time, and the team is a pleasure to work with.',
  },
  {
    name: 'Amit Malhotra',
    where: 'Dwarka, Delhi',
    source: 'fab fa-google',
    text: 'Sent a sympathy arrangement on short notice. They were thoughtful about the flower choice and delivered within hours. Small gestures like this matter — thank you.',
  },
];

function initials(name: string): string {
  return name
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0] ?? '')
    .join('')
    .toUpperCase();
}

export function FavFlowersSection() {
  return (
    <section className="hp-section lx-tiles" aria-labelledby="lx-fav-flowers-title">
      <div className="hp-container">
        <div className="hp-section-head">
          <h2 id="lx-fav-flowers-title" className="hp-section-title">
            Pick Their Fav Flowers
          </h2>
          <p className="hp-section-sub">Shop by their favourite bloom — roses, orchids, lilies &amp; more.</p>
        </div>
        <div className="lx-tiles__track hide-scrollbar" role="list">
          {FAV_FLOWERS.map((flower) => (
            <Link key={flower.title} href={flower.href} className="lx-tile lx-tile--portrait" role="listitem">
              <span className="lx-tile__img">
                <img
                  src={flower.img}
                  alt={`${flower.title} bouquets`}
                  width={260}
                  height={347}
                  loading="eager"
                  decoding="async"
                />
              </span>
              <span className="lx-tile__label">{flower.title}</span>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}

export function TailoredOccasionsSection({ products = [] }: { products?: Product[] }) {
  return (
    <section className="hp-section hp-occasions" aria-labelledby="hp-occasions-title" id="hpOccasionsSection">
      <div className="hp-container">
        <div className="hp-section-head">
          <h2 id="hp-occasions-title" className="hp-section-title">
            Tailored For Your Occasions
          </h2>
          <p className="hp-section-sub">Handpicked bouquets for every celebration — switch tabs to explore.</p>
        </div>
        <div className="hp-occasion-tabs hide-scrollbar" role="tablist" aria-label="Occasions">
          {OCCASION_TABS.map((tab, i) => (
            <button
              key={tab.key}
              type="button"
              role="tab"
              className={`hp-occasion-tab${i === 0 ? ' is-active' : ''}`}
              data-occasion={tab.key}
              data-cta={`View All ${tab.label} Gifts`}
              data-link={tab.href}
              aria-selected={i === 0}
              id={`hp-tab-${tab.key}`}
            >
              <i className={`fas ${tab.icon}`} aria-hidden="true" />
              <span>{tab.label}</span>
            </button>
          ))}
        </div>

        <div className="hp-occasion-carousel-wrap">
          <button
            type="button"
            className="hp-occasion-nav hp-occasion-nav--prev"
            id="hpOccasionPrev"
            aria-label="Previous products"
          >
            <i className="fas fa-chevron-left" aria-hidden="true" />
          </button>
          <div className="hp-occasion-track-wrap">
            <div
              className="hp-occasion-track hide-scrollbar"
              id="hpOccasionTrack"
              role="tabpanel"
              aria-live="polite"
            >
              {products.length > 0 ? (
                products.map((product) => <HomeOccasionCard key={product.id} product={product} />)
              ) : (
                <p className="hp-occasion-empty">
                  No products found for this occasion yet. <Link href="/flowers">Browse all flowers</Link>.
                </p>
              )}
            </div>
            <div className="hp-occasion-skeleton" id="hpOccasionSkeleton" hidden aria-hidden="true">
              {Array.from({ length: 4 }).map((_, s) => (
                <div key={s} className="hp-skeleton-card" />
              ))}
            </div>
          </div>
          <button
            type="button"
            className="hp-occasion-nav hp-occasion-nav--next"
            id="hpOccasionNext"
            aria-label="Next products"
          >
            <i className="fas fa-chevron-right" aria-hidden="true" />
          </button>
        </div>

        <div className="hp-occasion-footer">
          <Link href="/occasion/birthday" className="hp-occasion-viewall" id="hpOccasionViewAll">
            View All Birthday Gifts <i className="fas fa-arrow-right" aria-hidden="true" />
          </Link>
        </div>
        <p className="hp-trust-strip">
          <span className="hp-trust-strip__stars" aria-hidden="true">
            ⭐
          </span>
          Rated <strong>4.8</strong> / 5 &nbsp;|&nbsp; Trusted by <strong>4,62,543</strong> Happy Customers
        </p>
      </div>
    </section>
  );
}

export function SameDayTilesSection() {
  return (
    <section className="hp-section lx-tiles" aria-labelledby="lx-sameday-title">
      <div className="hp-container">
        <div className="hp-section-head">
          <h2 id="lx-sameday-title" className="hp-section-title">
            Same Day Delights
          </h2>
          <p className="hp-section-sub">Last-minute? We&apos;ve got you — order by 6 PM for delivery today.</p>
        </div>
        <div className="lx-tiles__track hide-scrollbar" role="list">
          {SAME_DAY.map((tile) => (
            <Link key={tile.label} href={tile.href} className="lx-tile lx-tile--square" role="listitem">
              <span className="lx-tile__img">
                <img
                  src={tile.img}
                  alt={`Same day ${tile.label.toLowerCase()} delivery`}
                  width={240}
                  height={240}
                  loading="lazy"
                  decoding="async"
                />
              </span>
              <span className="lx-tile__label">{tile.label}</span>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}

export function CelebrationsCalendarSection() {
  return (
    <section className="hp-section hp-celebrations" aria-labelledby="hp-celebrations-title">
      <div className="hp-container">
        <div className="hp-section-head">
          <div className="hp-celebrations-head">
            <h2 id="hp-celebrations-title" className="hp-section-title">
              Celebrations Calendar
            </h2>
            <Link className="hp-celebrations-all" href="/celebration-calendar">
              View full calendar <i className="fas fa-arrow-right" aria-hidden="true" />
            </Link>
          </div>
        </div>
        <div className="hp-celebrations-carousel-wrap">
          <div className="hp-celebrations-track-wrap" id="hpCelebrationsTrackWrap">
            <div
              className="hp-celebrations__track hide-scrollbar"
              id="hpCelebrationsTrack"
              role="list"
              aria-label="Celebrations calendar carousel"
            >
              {CELEBRATIONS.map((item) => (
                <Link
                  key={item.slug}
                  href={`/occasion/${item.slug}`}
                  className="hp-celebration-card"
                  role="listitem"
                  aria-label={`${item.title} — ${item.date}`}
                >
                  <span className="hp-celebration-card__date">{item.date}</span>
                  <span className="hp-celebration-card__img-wrap">
                    <img
                      src={item.image}
                      alt={`${item.title} celebration gifts`}
                      width={320}
                      height={400}
                      loading="lazy"
                      decoding="async"
                    />
                  </span>
                  <span className="hp-celebration-card__title">{item.title}</span>
                </Link>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

export function RelationshipsSection() {
  return (
    <section className="hp-section lx-tiles lx-tiles--band" aria-labelledby="lx-relationships-title">
      <div className="hp-container">
        <div className="hp-section-head">
          <h2 id="lx-relationships-title" className="hp-section-title">
            For Every Relationship
          </h2>
          <p className="hp-section-sub">Thoughtful gifts for everyone you love — find theirs in a tap.</p>
        </div>
        <div className="lx-tiles__track hide-scrollbar" role="list">
          {RELATIONSHIPS.map((rel) => (
            <Link
              key={rel.slug}
              href={`/relation/${rel.slug}`}
              className="lx-tile lx-tile--square"
              role="listitem"
            >
              <span className="lx-tile__img">
                <img
                  src={rel.img}
                  alt={`Gifts for ${rel.label.toLowerCase()}`}
                  width={220}
                  height={220}
                  loading="lazy"
                  decoding="async"
                />
              </span>
              <span className="lx-tile__label">{rel.label}</span>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}

export function ExploreLuxurySection() {
  return (
    <section className="hp-section lx-tiles lx-tiles--band" aria-labelledby="lx-luxury-title">
      <div className="hp-container">
        <div className="hp-section-head">
          <h2 id="lx-luxury-title" className="hp-section-title">
            Explore Luxury
          </h2>
          <p className="hp-section-sub">
            Our most premium picks — for moments that deserve something extraordinary.
          </p>
        </div>
        <div className="lx-tiles__track hide-scrollbar" role="list">
          {LUXURY.map((tile) => (
            <Link key={tile.label} href={tile.href} className="lx-tile lx-tile--square" role="listitem">
              <span className="lx-tile__img">
                <img
                  src={tile.img}
                  alt={`${tile.label} — luxury collection`}
                  width={240}
                  height={240}
                  loading="lazy"
                  decoding="async"
                />
              </span>
              <span className="lx-tile__label">
                {tile.label}
                <span className="lx-tile__sub">{tile.sub}</span>
              </span>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}

export function GiftFinderSection() {
  return (
    <section className="hp-section hp-finder" aria-labelledby="hp-finder-title">
      <div className="hp-container">
        <div className="hp-finder-shell">
          <div className="hp-finder-head">
            <p className="hp-finder-kicker">
              Search Gifts Quicker <span aria-hidden="true">⚡</span>
            </p>
            <h2 id="hp-finder-title" className="hp-section-title hp-section-title--light">
              Gift Finder
            </h2>
            <p className="hp-finder-sub">Tap a path below — we&apos;ll take you to the perfect picks in seconds.</p>
          </div>
          <div className="hp-finder-grid">
            {GIFT_FINDER.map((opt) => (
              <Link key={opt.label} href={opt.link} className="hp-finder-card">
                <span className="hp-finder-card__img">
                  <img
                    src={opt.image}
                    alt={`${opt.label} gift finder option`}
                    width={120}
                    height={120}
                    loading="lazy"
                    decoding="async"
                  />
                </span>
                <span className="hp-finder-card__icon" aria-hidden="true">
                  <i className={`fas ${opt.icon}`} />
                </span>
                <span className="hp-finder-card__label">{opt.label}</span>
                <span className="hp-finder-card__sub">{opt.subtitle}</span>
              </Link>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}

export function SendGiftsAnywhereSection() {
  return (
    <section className="hp-section hp-abroad" id="hp-send-gifts-abroad" aria-labelledby="hp-abroad-title">
      <div className="hp-container">
        <div className="hp-section-head">
          <h2 id="hp-abroad-title" className="hp-section-title">
            Send Gifts Anywhere
          </h2>
          <p className="hp-section-sub">
            Surprise loved ones across India with premium flowers &amp; curated gifts from Sai Flowers.
          </p>
        </div>
        <div className="hp-abroad-scroll" role="list">
          {CITIES.map((city) => (
            <Link key={city.name} href={city.link} className="hp-abroad-card" role="listitem">
              <span className="hp-abroad-card__img-wrap">
                <img
                  src={city.image}
                  alt={`${city.name} gifting`}
                  width={150}
                  height={150}
                  loading="lazy"
                  decoding="async"
                />
              </span>
              <span className="hp-abroad-card__label">{city.name}</span>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}

export function AboutUsSection() {
  return (
    <section className="lx-about hp-seo" aria-labelledby="lx-about-title">
      <div className="lx-about__inner">
        <div className="lx-about__visual">
          <div className="lx-about__badge">
            <span className="lx-about__badge-dot">
              <i className="fas fa-award" aria-hidden="true" />
            </span>
            <span>
              25+ Years Of
              <br />
              Experience
            </span>
          </div>
          <div className="lx-about__main">
            <img
              src="/uploads/sections/img_69c12ca4d6812_img69b5447deae89WhatsAppImage20260314at31518PM.webp"
              alt="Sai Flower handcrafted bouquet"
              width={480}
              height={580}
              loading="lazy"
              decoding="async"
            />
          </div>
          <div className="lx-about__side">
            <span className="lx-about__side-img">
              <img
                src="/uploads/sections/img_69affbff9fce1_img69a6ad335b957WhatsAppImage20260303at23841PM.webp"
                alt="Elegant white rose bouquet"
                width={240}
                height={240}
                loading="lazy"
                decoding="async"
              />
            </span>
            <span className="lx-about__side-img">
              <img
                src="/uploads/sections/img_69b00d7d6b073_img69a6aa4b1d253WhatsAppImage20260303at23112PM.webp"
                alt="Premium red rose bouquet"
                width={240}
                height={240}
                loading="lazy"
                decoding="async"
              />
            </span>
          </div>
        </div>

        <div>
          <p className="lx-about__kicker">About Us</p>
          <h2 id="lx-about-title" className="lx-about__title">
            Welcome to Sai Flower
          </h2>
          <div className="lx-about__text">
            <p>
              Since 1998, Sai Flower has been handcrafting fresh floral arrangements for Delhi and the NCR. From our
              shop on Lodhi Road, we source premium roses, orchids, lilies, and seasonal blooms to create bouquets that
              feel personal — whether you are celebrating a birthday, marking an anniversary, or simply sending a
              thoughtful surprise.
            </p>
            <p>
              Our <Link href="/flowers">online flower shop</Link> makes ordering easy. Choose from curated bouquets,
              custom arrangements, and add-ons like <Link href="/cakes">cakes</Link> and{' '}
              <Link href="/gifts">gift hampers</Link>. We offer same-day delivery across Delhi, with express and
              midnight options available on select products.
            </p>
            <p>
              Beyond everyday gifting, Sai Flower specialises in <Link href="/flowers">wedding flowers</Link>,
              corporate events, and large-scale décor. Our team works closely with clients to design stage backdrops,
              table centrepieces, bridal bouquets, and venue styling that matches your vision and budget.
            </p>
            <p>
              Every arrangement is made to order with freshly cut flowers and careful packaging so it arrives looking
              its best. Browse our <Link href="/gallery">floral gallery</Link> for inspiration, read tips on our{' '}
              <Link href="/blog">blog</Link>, or <Link href="/contact">contact us</Link> — we are happy to help with
              delivery areas, timing, and custom requests. See our <Link href="/delivery-policy">delivery policy</Link>{' '}
              for full details.
            </p>
          </div>
          <ul className="lx-about__features">
            <li>
              <i className="fas fa-check" aria-hidden="true" /> Same-Day &amp; Midnight Delivery
            </li>
            <li>
              <i className="fas fa-check" aria-hidden="true" /> Freshly Cut, Handcrafted Blooms
            </li>
            <li>
              <i className="fas fa-check" aria-hidden="true" /> Fair Prices &amp; Easy Ordering
            </li>
            <li>
              <i className="fas fa-check" aria-hidden="true" /> Weddings, Events &amp; Décor
            </li>
          </ul>
          <Link href="/about" className="lx-btn-gold">
            Discover More <i className="fas fa-arrow-right" aria-hidden="true" />
          </Link>
        </div>
      </div>
    </section>
  );
}

export function HowItWorksSection() {
  return (
    <section className="lx-steps" aria-labelledby="lx-steps-title">
      <div className="lx-steps__inner">
        <div className="lx-section-head">
          <span className="lx-kicker">Effortless Gifting</span>
          <h2 id="lx-steps-title">How It Works</h2>
          <p>
            From browsing to their doorstep in three simple steps — most Delhi NCR orders arrive the same day.
          </p>
        </div>
        <div className="lx-steps__grid">
          <div className="lx-step">
            <span className="lx-step__num" aria-hidden="true">
              1
            </span>
            <h3 className="lx-step__title">Pick the Perfect Bloom</h3>
            <p className="lx-step__text">
              Browse curated bouquets, cakes and hampers — or use the Gift Finder to shop by occasion, recipient or
              budget.
            </p>
          </div>
          <div className="lx-step">
            <span className="lx-step__num" aria-hidden="true">
              2
            </span>
            <h3 className="lx-step__title">Checkout Securely</h3>
            <p className="lx-step__text">
              Add a personal message, choose your delivery slot and pay safely through trusted payment gateways.
            </p>
          </div>
          <div className="lx-step">
            <span className="lx-step__num" aria-hidden="true">
              3
            </span>
            <h3 className="lx-step__title">We Handcraft &amp; Deliver</h3>
            <p className="lx-step__text">
              Our florists arrange your order with freshly cut flowers and hand-deliver it — same-day, express or
              midnight.
            </p>
          </div>
        </div>
      </div>
    </section>
  );
}

export function FaqSection() {
  return (
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
              Yes — place your order before 6 PM and we deliver the same day across Delhi NCR. Express and midnight
              delivery slots are also available on select products.
            </div>
          </details>
          <details className="lx-faq__item">
            <summary className="lx-faq__q">How do you keep the flowers fresh during delivery?</summary>
            <div className="lx-faq__a">
              Every bouquet is made to order with freshly cut blooms, hydrated right up to dispatch and packaged
              carefully so it arrives looking its best. Freshness is guaranteed on every order.
            </div>
          </details>
          <details className="lx-faq__item">
            <summary className="lx-faq__q">Can I add a cake, chocolates or a personal note to my order?</summary>
            <div className="lx-faq__a">
              Absolutely. You can pair your flowers with <Link href="/cakes">cakes</Link> and{' '}
              <Link href="/gifts">gift hampers</Link> at checkout, and include a free personalised message card with
              every order.
            </div>
          </details>
          <details className="lx-faq__item">
            <summary className="lx-faq__q">Do you handle weddings and event décor?</summary>
            <div className="lx-faq__a">
              Yes — Sai Flower specialises in wedding flowers, corporate events and large-scale décor: stage
              backdrops, centrepieces, bridal bouquets and full venue styling.{' '}
              <Link href="/contact">Contact us</Link> for a custom quote.
            </div>
          </details>
          <details className="lx-faq__item">
            <summary className="lx-faq__q">Is online payment safe on your website?</summary>
            <div className="lx-faq__a">
              Completely. All payments are processed through secure, trusted payment gateways — we never store your
              card details.
            </div>
          </details>
        </div>
        <p className="lx-faq__footer">
          Still have questions? <Link href="/faq">Read all FAQs</Link> or{' '}
          <Link href="/contact">talk to our team</Link>.
        </p>
      </div>
    </section>
  );
}

export function TestimonialsSection() {
  return (
    <section className="lx-testimonials" aria-labelledby="lx-testimonials-title">
      <div className="lx-testimonials__inner">
        <div className="lx-section-head">
          <span className="lx-kicker">Loved Across Delhi NCR</span>
          <h2 id="lx-testimonials-title">What Our Customers Say</h2>
          <p>Real words from the people we&apos;ve helped celebrate, apologise, surprise and remember.</p>
        </div>
        <div className="lx-testimonials__wrap">
          <button
            type="button"
            className="lx-testimonials__nav lx-testimonials__nav--prev"
            id="lxTestimonialsPrev"
            aria-label="Previous testimonial"
          >
            <i className="fas fa-chevron-left" aria-hidden="true" />
          </button>
          <div
            className="lx-testimonials__track hide-scrollbar"
            id="lxTestimonialsTrack"
            role="list"
            aria-label="Customer testimonials"
          >
            {TESTIMONIALS.map((t) => (
              <figure key={t.name} className="lx-testimonial" role="listitem">
                <div className="lx-testimonial__stars" aria-label="Rated 5 out of 5">
                  {Array.from({ length: 5 }).map((_, i) => (
                    <i key={i} className="fas fa-star" aria-hidden="true" />
                  ))}
                </div>
                <blockquote className="lx-testimonial__text">&ldquo;{t.text}&rdquo;</blockquote>
                <figcaption className="lx-testimonial__meta">
                  <span className="lx-testimonial__avatar" aria-hidden="true">
                    {initials(t.name)}
                  </span>
                  <span>
                    <span className="lx-testimonial__name">{t.name}</span>
                    <span className="lx-testimonial__where">{t.where}</span>
                  </span>
                  <i className={`lx-testimonial__source ${t.source}`} aria-hidden="true" />
                </figcaption>
              </figure>
            ))}
          </div>
          <button
            type="button"
            className="lx-testimonials__nav lx-testimonials__nav--next"
            id="lxTestimonialsNext"
            aria-label="Next testimonial"
          >
            <i className="fas fa-chevron-right" aria-hidden="true" />
          </button>
        </div>
      </div>
    </section>
  );
}
