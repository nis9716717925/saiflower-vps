import Link from 'next/link';
import Script from 'next/script';
import type { CSSProperties } from 'react';
import {
  COLLECTION_CITIES,
  COLLECTION_POPULAR,
  collectionBuildSeoHtml,
  collectionCrossKindLinks,
  collectionResolveRelated,
  collectionSplitGroups,
  collectionUrl,
  type CollectionEntry,
} from '@/lib/collection';
import { OptimizedImage } from '@/components/ui/OptimizedImage';
import { discountPercent, formatInr, productHref } from '@/lib/images';
import type { Product } from '@/lib/types';

function ClCard({ product }: { product: Product }) {
  const href = product.url ?? productHref(product.type, product.slug);
  const discount = discountPercent(product.price, product.originalPrice);
  const rating = product.rating ?? 0;
  const typeLabel = product.type.charAt(0).toUpperCase() + product.type.slice(1);

  return (
    <Link className="cl-card" href={href} title={product.name}>
      <span className="cl-card__media">
        <OptimizedImage src={product.image} alt={product.name} width={320} height={320} />
        <span className="cl-card__type">{typeLabel}</span>
      </span>
      <span className="cl-card__body">
        <span className="cl-card__name">{product.name}</span>
        <span className="cl-card__meta">
          {discount > 0 && product.originalPrice != null ? (
            <span className="cl-card__mrp">{formatInr(product.originalPrice)}</span>
          ) : null}
          <span className="cl-card__price">{formatInr(product.price)}</span>
          {discount > 0 ? <span className="cl-card__off">{discount}% OFF</span> : null}
          {rating > 0 ? (
            <span className="cl-card__rating" aria-label={`Rated ${rating.toFixed(1)} out of 5`}>
              <i className="fas fa-star" aria-hidden="true" /> {rating.toFixed(1)}
            </span>
          ) : null}
        </span>
      </span>
    </Link>
  );
}

function ProductRail({
  id,
  title,
  sub,
  products,
  asGrid,
}: {
  id: string;
  title: string;
  sub?: string;
  products: Product[];
  asGrid?: boolean;
}) {
  return (
    <section
      className={`cl-section${asGrid ? ' cl-section--grid' : ' cl-section--slider'}`}
      aria-labelledby={`${id}-title`}
    >
      <div className="cl-container">
        <div className="cl-section__head">
          <div>
            <h2 id={`${id}-title`} className="cl-section__title">
              {title}
            </h2>
            {sub ? <p className="cl-section__sub">{sub}</p> : null}
          </div>
        </div>

        {asGrid ? (
          <div className="cl-grid" id={id} role="list">
            {products.length === 0 ? (
              <div className="cl-empty" role="status">
                <p>
                  No matching products in this collection right now. Try{' '}
                  <Link href="/flowers">all flowers</Link>, <Link href="/cakes">cakes</Link>, or{' '}
                  <Link href="/gifts">gifts</Link>.
                </p>
              </div>
            ) : (
              products.map((p) => (
                <div key={`${p.type}-${p.id}`} className="cl-grid__item" role="listitem">
                  <ClCard product={p} />
                </div>
              ))
            )}
          </div>
        ) : (
          <div className="cl-slider" data-cl-slider>
            <button
              type="button"
              className="cl-slider__nav cl-slider__nav--prev"
              aria-label="Previous"
              data-cl-prev
            >
              <i className="fas fa-chevron-left" aria-hidden="true" />
            </button>
            <div className="cl-slider__track hide-scrollbar" id={id} role="list">
              {products.map((p) => (
                <div key={`${p.type}-${p.id}`} className="cl-slider__item" role="listitem">
                  <ClCard product={p} />
                </div>
              ))}
            </div>
            <button
              type="button"
              className="cl-slider__nav cl-slider__nav--next"
              aria-label="Next"
              data-cl-next
            >
              <i className="fas fa-chevron-right" aria-hidden="true" />
            </button>
          </div>
        )}
      </div>
    </section>
  );
}

interface CollectionLandingViewProps {
  entry: CollectionEntry;
  products: Product[];
}

export function CollectionLandingView({ entry, products }: CollectionLandingViewProps) {
  const groups = collectionSplitGroups(products);
  const related = collectionResolveRelated(entry.related ?? []);
  const cross = collectionCrossKindLinks(entry);
  const faqs = entry.faqs?.length ? entry.faqs : [];
  const seoHtml = collectionBuildSeoHtml(entry);
  const heroImg =
    entry.hero_image ||
    'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=1600&q=80';
  const waLink = 'https://wa.me/918802004527';

  const kindLabel =
    entry.kind === 'flower'
      ? 'Flower Type'
      : entry.kind === 'relation'
        ? 'Gifts For'
        : entry.kind === 'occasion'
          ? 'Occasion'
          : 'Collection';

  const breadParentHref =
    entry.kind === 'flower'
      ? '/flowers'
      : entry.kind === 'relation'
        ? '/collection/best-sellers'
        : entry.kind === 'occasion'
          ? '/occasion/birthday'
          : '/collection/best-sellers';

  const breadParentLabel =
    entry.kind === 'flower'
      ? 'Flowers'
      : entry.kind === 'relation'
        ? 'Relations'
        : entry.kind === 'occasion'
          ? 'Occasions'
          : 'Collections';

  return (
    <div
      className="cl-page"
      style={
        {
          ['--cl-primary' as string]: '#2f6f4e',
          ['--cl-accent' as string]: '#d4af37',
          ['--cl-hero-image' as string]: `url('${heroImg}')`,
        } as CSSProperties
      }
    >
      <header className="cl-hero">
        <div className="cl-hero__overlay" />
        <div className="cl-container cl-hero__inner">
          <nav className="cl-breadcrumb" aria-label="Breadcrumb">
            <ol>
              <li>
                <Link href="/">Home</Link>
              </li>
              <li>
                <Link href={breadParentHref}>{breadParentLabel}</Link>
              </li>
              <li aria-current="page">{entry.title}</li>
            </ol>
          </nav>
          <p className="cl-hero__badge">{entry.badge || kindLabel}</p>
          <h1 className="cl-hero__title">{entry.h1}</h1>
          <p className="cl-hero__desc">{entry.short_description}</p>
          <div className="cl-hero__actions">
            <a className="cl-btn cl-btn--primary" href="#cl-products">
              {entry.cta_label || 'Shop Now'}
            </a>
            <a
              className="cl-btn cl-btn--ghost"
              href={waLink}
              target="_blank"
              rel="noopener noreferrer"
            >
              WhatsApp Order
            </a>
          </div>
          <p className="cl-hero__promise">
            <i className="fas fa-truck-fast" aria-hidden="true" /> Same-day delivery across Delhi NCR
            · Order by 6 PM
          </p>
        </div>
      </header>

      <section className="cl-trust" aria-label="Trust badges">
        <div className="cl-container cl-trust__row">
          <div className="cl-trust__item">
            <i className="fas fa-leaf" aria-hidden="true" />
            <span>Freshness Guaranteed</span>
          </div>
          <div className="cl-trust__item">
            <i className="fas fa-bolt" aria-hidden="true" />
            <span>Same-Day Delivery</span>
          </div>
          <div className="cl-trust__item">
            <i className="fas fa-lock" aria-hidden="true" />
            <span>Secure Checkout</span>
          </div>
          <div className="cl-trust__item">
            <i className="fas fa-star" aria-hidden="true" />
            <span>4.8★ Rated</span>
          </div>
          <div className="cl-trust__item">
            <i className="fas fa-rotate-left" aria-hidden="true" />
            <span>Easy Replacements</span>
          </div>
        </div>
      </section>

      <main id="main-content">
        <div id="cl-products">
          <ProductRail
            id="cl-main-grid"
            title={`Shop ${entry.title}`}
            sub={`${products.length} fresh bouquets · décor services never shown here`}
            products={groups.all}
            asGrid
          />
        </div>

        {groups.featured.length > 0 ? (
          <ProductRail
            id="cl-featured"
            title={`Featured ${entry.title}`}
            sub="Handpicked highlights from this collection"
            products={groups.featured}
          />
        ) : null}

        {groups.bestsellers.length > 0 ? (
          <ProductRail
            id="cl-bestsellers"
            title="Best Sellers"
            sub="Highest-rated picks customers reorder"
            products={groups.bestsellers}
          />
        ) : null}

        {groups.recent.length > 0 ? (
          <ProductRail
            id="cl-recent"
            title="Recently Added"
            sub="Fresh arrivals in this category"
            products={groups.recent}
          />
        ) : null}

        {groups.sameday.length > 0 ? (
          <ProductRail
            id="cl-sameday"
            title="Same Day Delivery Picks"
            sub="Need it today? These ship fast across Delhi NCR"
            products={groups.sameday}
          />
        ) : null}

        <section className="cl-section" aria-labelledby="cl-related-title">
          <div className="cl-container">
            <div className="cl-section__head">
              <h2 id="cl-related-title" className="cl-section__title">
                Related Collections
              </h2>
              <p className="cl-section__sub">Keep exploring — more ways to find the perfect gift</p>
            </div>
            <div className="cl-chip-grid">
              {related.map((rel) => (
                <Link key={rel.canonical_path} className="cl-chip" href={rel.canonical_path}>
                  {rel.title}
                </Link>
              ))}
            </div>
          </div>
        </section>

        <section className="cl-section cl-section--muted" aria-labelledby="cl-cats-title">
          <div className="cl-container">
            <div className="cl-section__head">
              <h2 id="cl-cats-title" className="cl-section__title">
                Related Categories
              </h2>
            </div>
            <div className="cl-link-columns">
              <div>
                <h3>Flower Types</h3>
                {cross.flowers.map((item) => (
                  <Link key={item.slug} href={item.canonical_path}>
                    {item.title}
                  </Link>
                ))}
              </div>
              <div>
                <h3>Occasions</h3>
                {cross.occasions.map((item) => (
                  <Link key={item.slug} href={item.canonical_path}>
                    {item.title}
                  </Link>
                ))}
              </div>
              <div>
                <h3>For Someone</h3>
                {cross.relations.map((item) => (
                  <Link key={item.slug} href={item.canonical_path}>
                    {item.title}
                  </Link>
                ))}
              </div>
              <div>
                <h3>Collections</h3>
                {cross.collections.map((item) => (
                  <Link key={item.slug} href={item.canonical_path}>
                    {item.title}
                  </Link>
                ))}
              </div>
            </div>
          </div>
        </section>

        <section className="cl-section" aria-labelledby="cl-why-title">
          <div className="cl-container">
            <div className="cl-section__head">
              <h2 id="cl-why-title" className="cl-section__title">
                Why Choose Sai Flowers
              </h2>
              <p className="cl-section__sub">Premium floristry with marketplace convenience</p>
            </div>
            <div className="cl-why-grid">
              <article className="cl-why-card">
                <i className="fas fa-spa" aria-hidden="true" />
                <h3>Artisan Florists</h3>
                <p>Every bouquet styled by hand in our Delhi studio since 1998.</p>
              </article>
              <article className="cl-why-card">
                <i className="fas fa-truck" aria-hidden="true" />
                <h3>Reliable Delivery</h3>
                <p>Same-day slots across Delhi NCR with live order support.</p>
              </article>
              <article className="cl-why-card">
                <i className="fas fa-shield-halved" aria-hidden="true" />
                <h3>Secure Payments</h3>
                <p>Encrypted checkout with UPI, cards and trusted wallets.</p>
              </article>
              <article className="cl-why-card">
                <i className="fas fa-heart" aria-hidden="true" />
                <h3>Loved by Thousands</h3>
                <p>4.8★ average from customers who gift with us again.</p>
              </article>
            </div>
          </div>
        </section>

        <section className="cl-section cl-section--muted" aria-labelledby="cl-reviews-title">
          <div className="cl-container">
            <div className="cl-section__head">
              <h2 id="cl-reviews-title" className="cl-section__title">
                Customer Reviews
              </h2>
              <p className="cl-section__sub">Real words from people who sent love with Sai Flowers</p>
            </div>
            <div className="cl-reviews">
              <blockquote className="cl-review">
                <p>
                  “The rose bouquet arrived looking even better than the photos. Packaging was
                  gorgeous.”
                </p>
                <footer>— Ananya, Delhi</footer>
              </blockquote>
              <blockquote className="cl-review">
                <p>
                  “Same-day birthday delivery to Noida saved me. Mum loved the mixed flowers.”
                </p>
                <footer>— Rohan, Gurgaon</footer>
              </blockquote>
              <blockquote className="cl-review">
                <p>
                  “Premium quality without the drama. WhatsApp support was quick and kind.”
                </p>
                <footer>— Meera, Noida</footer>
              </blockquote>
            </div>
            <div className="cl-google-reviews">
              <p>
                <i className="fab fa-google" aria-hidden="true" /> <strong>Google Reviews</strong> —
                Rated 4.8 / 5 by happy customers across Delhi NCR.
              </p>
              <Link className="cl-btn cl-btn--ghost" href="/review">
                Read reviews
              </Link>
            </div>
          </div>
        </section>

        <section className="cl-section" aria-labelledby="cl-delivery-title">
          <div className="cl-container cl-split">
            <div>
              <h2 id="cl-delivery-title" className="cl-section__title">
                Delivery Information
              </h2>
              <ul className="cl-bullets">
                <li>Same-day delivery available across Delhi NCR (order by 6 PM)</li>
                <li>Scheduled date &amp; time slots at checkout</li>
                <li>Midnight delivery in select pin codes on special occasions</li>
                <li>Carefully packed to protect fresh blooms in transit</li>
              </ul>
              <p>
                <Link href="/delivery-policy">Read full delivery policy →</Link>
              </p>
            </div>
            <div>
              <h2 className="cl-section__title">Return Policy</h2>
              <ul className="cl-bullets">
                <li>Report damaged or incorrect orders promptly with photos</li>
                <li>Eligible replacements arranged as per our refund policy</li>
                <li>Perishable products cannot be returned after acceptance</li>
              </ul>
              <p>
                <Link href="/refund-policy">Read refund policy →</Link>
              </p>
            </div>
          </div>
        </section>

        <section className="cl-section cl-section--muted" aria-labelledby="cl-cities-title">
          <div className="cl-container">
            <div className="cl-section__head">
              <h2 id="cl-cities-title" className="cl-section__title">
                Flower Delivery Near You
              </h2>
            </div>
            <div className="cl-chip-grid">
              {COLLECTION_CITIES.map((city) => (
                <Link key={city.href} className="cl-chip" href={city.href}>
                  {city.name}
                </Link>
              ))}
            </div>
          </div>
        </section>

        <section className="cl-section" aria-labelledby="cl-popular-title">
          <div className="cl-container">
            <div className="cl-section__head">
              <h2 id="cl-popular-title" className="cl-section__title">
                Popular Searches
              </h2>
            </div>
            <div className="cl-chip-grid">
              {COLLECTION_POPULAR.map((ps) => (
                <Link key={ps.href} className="cl-chip cl-chip--outline" href={ps.href}>
                  {ps.label}
                </Link>
              ))}
            </div>
          </div>
        </section>

        <section className="cl-secure" aria-label="Secure checkout">
          <div className="cl-container cl-secure__inner">
            <p>
              <i className="fas fa-shield-halved" aria-hidden="true" /> Secure checkout · SSL
              encrypted
            </p>
            <div className="cl-pay-icons" aria-label="Payment methods">
              <span>UPI</span>
              <span>Visa</span>
              <span>Mastercard</span>
              <span>RuPay</span>
              <span>NetBanking</span>
            </div>
          </div>
        </section>

        {faqs.length > 0 ? (
          <section className="cl-section" id="faq" aria-labelledby="cl-faq-title">
            <div className="cl-container">
              <div className="cl-section__head">
                <h2 id="cl-faq-title" className="cl-section__title">
                  {entry.title} — FAQs
                </h2>
              </div>
              <div className="cl-faq">
                {faqs.map((faq, i) => (
                  <details key={faq.q} className="cl-faq__item" open={i === 0}>
                    <summary>{faq.q}</summary>
                    <p>{faq.a}</p>
                  </details>
                ))}
              </div>
            </div>
          </section>
        ) : null}

        <section className="cl-section cl-section--muted cl-seo" aria-label="About this collection">
          <div
            className="cl-container cl-seo__content"
            dangerouslySetInnerHTML={{ __html: seoHtml }}
          />
        </section>

        <section className="cl-final-cta" aria-labelledby="cl-final-title">
          <div className="cl-container cl-final-cta__inner">
            <h2 id="cl-final-title">Ready to send {entry.title}?</h2>
            <p>Same-day delivery across Delhi NCR. Fresh blooms, secure checkout, free message card.</p>
            <div className="cl-hero__actions">
              <a className="cl-btn cl-btn--primary" href="#cl-products">
                Shop {entry.title}
              </a>
              <a
                className="cl-btn cl-btn--ghost"
                href={waLink}
                target="_blank"
                rel="noopener noreferrer"
              >
                Chat on WhatsApp
              </a>
            </div>
          </div>
        </section>
      </main>

      {/* Desktop-only WhatsApp float — mobile uses footer bottom nav (no sticky Shop Now / WhatsApp bar) */}
      <a
        className="cl-wa-float"
        href={`${waLink}?text=${encodeURIComponent(`Hi Sai Flowers, I am browsing ${entry.title}`)}`}
        target="_blank"
        rel="noopener noreferrer"
        aria-label="WhatsApp Sai Flowers"
      >
        <i className="fab fa-whatsapp" aria-hidden="true" />
      </a>

      <Script src="/assets/js/collection-landing.js?v=1" strategy="afterInteractive" />
    </div>
  );
}

export function collectionKindLabel(kind: CollectionEntry['kind']) {
  return collectionUrl(kind, 'x').split('/')[1];
}
