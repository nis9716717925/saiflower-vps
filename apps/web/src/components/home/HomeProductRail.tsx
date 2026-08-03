import Link from 'next/link';
import {
  discountPercent,
  formatInr,
  productHref,
  resolveImageSrc,
} from '@/lib/images';
import type { Product } from '@/lib/types';

/** Homepage product card — matches PHP `homepage_render_occasion_cards` markup. */
export function HomeOccasionCard({ product }: { product: Product }) {
  const href = product.url ?? productHref(product.type, product.slug);
  const img = resolveImageSrc(product.image);
  const discount = discountPercent(product.price, product.originalPrice);
  const ratingRaw = product.rating && product.rating > 0 ? product.rating : 4.8;
  const rating = ratingRaw.toFixed(1);
  const orig = product.originalPrice ?? 0;

  return (
    <article className="hp-occasion-card snap-start">
      <Link href={href} className="hp-occasion-card__media">
        <img src={img} alt={product.name} width={280} height={350} loading="lazy" decoding="async" />
        {discount > 0 ? <span className="hp-occasion-card__badge">{discount}% OFF</span> : null}
        <span className="hp-occasion-card__trust">
          <i className="fas fa-shield-halved" aria-hidden="true" /> Secure checkout
        </span>
      </Link>
      <div className="hp-occasion-card__body">
        <Link href={href} className="hp-occasion-card__title">
          {product.name}
        </Link>
        <div className="hp-occasion-card__rating" aria-label={`Rated ${rating} out of 5`}>
          <span className="hp-stars" aria-hidden="true">
            <i className="fas fa-star" />
          </span>
          <span>{rating}</span>
          <span className="hp-muted">· Verified buyers</span>
        </div>
        <div className="hp-occasion-card__price">
          <span className="hp-price-current">{formatInr(product.price)}</span>
          {orig > product.price ? <span className="hp-price-old">{formatInr(orig)}</span> : null}
        </div>
        <Link href={href} className="hp-occasion-card__cta">
          Buy Now
        </Link>
      </div>
    </article>
  );
}

interface HomeProductRailProps {
  sliderKey: string;
  title: string;
  subtitle?: string;
  viewAllHref?: string;
  viewAllLabel?: string;
  products: Product[];
}

/** Product carousel section — matches PHP `homepage_product_sliders.php`. */
export function HomeProductRail({
  sliderKey,
  title,
  subtitle,
  viewAllHref,
  viewAllLabel,
  products,
}: HomeProductRailProps) {
  if (products.length === 0) return null;

  return (
    <section className="hp-section hp-product-slider-section" aria-labelledby={`hp-slider-${sliderKey}`}>
      <div className="hp-container">
        <div className="hp-section-head">
          <h2 id={`hp-slider-${sliderKey}`} className="hp-section-title">
            {title}
          </h2>
          {subtitle ? <p className="hp-section-sub">{subtitle}</p> : null}
        </div>

        <div className="hp-occasion-carousel-wrap hp-product-carousel-wrap" data-hp-slider={sliderKey}>
          <button
            type="button"
            className="hp-occasion-nav hp-occasion-nav--prev"
            aria-label={`Previous in ${title}`}
          >
            <i className="fas fa-chevron-left" aria-hidden="true" />
          </button>
          <div className="hp-occasion-track-wrap">
            <div className="hp-occasion-track hide-scrollbar" role="list">
              {products.map((product) => (
                <HomeOccasionCard key={`${sliderKey}-${product.id}`} product={product} />
              ))}
            </div>
          </div>
          <button
            type="button"
            className="hp-occasion-nav hp-occasion-nav--next"
            aria-label={`Next in ${title}`}
          >
            <i className="fas fa-chevron-right" aria-hidden="true" />
          </button>
        </div>

        {viewAllHref ? (
          <div className="hp-product-slider-footer">
            <Link href={viewAllHref} className="hp-occasion-viewall">
              {viewAllLabel ?? `View all ${title}`}
              <i className="fas fa-arrow-right" aria-hidden="true" />
            </Link>
          </div>
        ) : null}
      </div>
    </section>
  );
}
