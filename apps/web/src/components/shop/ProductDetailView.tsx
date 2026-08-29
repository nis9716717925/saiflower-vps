'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import Script from 'next/script';
import { useMemo, useRef, useState } from 'react';
import { apiSend } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { ProductCard } from '@/components/shop/ProductCard';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import { discountPercent, formatInr, productGalleryUrls, resolveImageSrc } from '@/lib/images';
import type { Product } from '@/lib/types';

interface ProductDetailViewProps {
  product: Product;
  listLabel: string;
  listHref: string;
}

export function ProductDetailView({ product, listLabel, listHref }: ProductDetailViewProps) {
  const router = useRouter();
  const { refreshCart } = useCart();
  const [adding, setAdding] = useState(false);
  const [buying, setBuying] = useState(false);
  const [qty, setQty] = useState(1);
  const [descriptionExpanded, setDescriptionExpanded] = useState(false);
  const galleryImages = useMemo(() => productGalleryUrls(product), [product]);
  const img = galleryImages[0] ?? resolveImageSrc(product.image);
  const [selectedImage, setSelectedImage] = useState(img);
  const relatedTrackRef = useRef<HTMLDivElement>(null);
  const discount = discountPercent(product.price, product.originalPrice);
  const inStock = product.inStock !== false;
  const rating = product.rating ?? 5.0;
  const related = product.related ?? [];

  function scrollRelated(direction: number) {
    const track = relatedTrackRef.current;
    if (!track) return;
    track.scrollBy({ left: direction * Math.min(track.clientWidth * 0.82, 820), behavior: 'smooth' });
  }

  async function addToCartRequest() {
    await apiSend('/cart/items', 'POST', {
      productId: product.id,
      category: product.type,
      quantity: qty,
      name: product.name,
      price: product.price,
      image: product.image,
    });
    await refreshCart();
  }

  async function handleAddToCart() {
    if (!inStock || adding) return;
    setAdding(true);
    try {
      await addToCartRequest();
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not add to cart');
    } finally {
      setAdding(false);
    }
  }

  async function handleBuyNow() {
    if (!inStock || buying) return;
    setBuying(true);
    try {
      await addToCartRequest();
      router.push('/cart');
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not add to cart');
      setBuying(false);
    }
  }

  return (
    <>
      <Script src="/assets/js/product-detail-premium.js" strategy="afterInteractive" />
      <div className="pd-page">
        <div className="pd-container">
        <nav className="pd-breadcrumb" aria-label="Breadcrumb">
          <Link href="/">Home</Link>
          <span aria-hidden="true"> / </span>
          <Link href={listHref}>{listLabel}</Link>
          <span aria-hidden="true"> / </span>
          <span className="pd-bc-current">{product.name}</span>
        </nav>

        <div className="pd-grid">
          <div className="pd-gallery-col pd-gallery-sticky">
            <div className="pd-media-wrap is-zoomable" id="mediaContainer">
              {discount > 0 ? <span className="pd-media-sale">{discount}% OFF</span> : null}
              <span className="pd-zoom-hint">Tap to enlarge</span>
              <OptimizedImage
                key={selectedImage}
                src={selectedImage}
                id="mainView"
                width={800}
                height={800}
                priority
                sizes={IMAGE_SIZE_PRESETS.productDetail}
                className="visible-media"
                alt={product.name}
              />
            </div>
            {galleryImages.length > 1 && (
              <div className="pd-thumbs" aria-label="Product images">
                {galleryImages.map((src, index) => (
                  <button
                    key={`${src}-${index}`}
                    type="button"
                    className={`pd-thumb${selectedImage === src ? ' is-active' : ''}`}
                    onClick={() => setSelectedImage(src)}
                    aria-label="Show product image"
                    aria-pressed={selectedImage === src}
                  >
                    <OptimizedImage src={src} alt="" width={80} height={80} webp={false} />
                  </button>
                ))}
              </div>
            )}
          </div>

          <div className="pd-buy-col pd-buy-sticky product-details">
            <div className="pd-badge-row">
              {inStock ? (
                <>
                  <span className="pd-badge">
                    <i className="fas fa-bolt" aria-hidden="true" /> Same-day eligible
                  </span>
                  {discount > 0 && <span className="pd-badge pd-badge-sale">Save {discount}%</span>}
                </>
              ) : (
                <span className="pd-badge" style={{ background: '#fee2e2', color: '#991b1b' }}>
                  Out of stock
                </span>
              )}
            </div>

            <h1 className="pd-title">{product.name}</h1>

            <div className="pd-rating-row">
              <span className="pd-stars" aria-hidden="true">
                <i className="fas fa-star" />
                <i className="fas fa-star" />
                <i className="fas fa-star" />
                <i className="fas fa-star" />
                <i className="fas fa-star-half-stroke" />
              </span>
              <span className="pd-rating-val">{rating.toFixed(1)}</span>
              <span className="pd-rating-meta">Customer favourite in Delhi</span>
            </div>

            <div className="pd-price-block">
              <span className="pd-price">{formatInr(product.price)}</span>
              {discount > 0 && product.originalPrice != null && (
                <span className="pd-mrp">{formatInr(product.originalPrice)}</span>
              )}
              <span className="pd-tax-note">Inclusive of all taxes</span>
            </div>

            {product.description && (
              <div className="pd-description-wrap">
                <div
                  id="pd-description"
                  className={`pd-desc${descriptionExpanded ? ' is-expanded' : ''}`}
                  dangerouslySetInnerHTML={{ __html: product.description }}
                />
                <button
                  type="button"
                  className="pd-readmore"
                  aria-expanded={descriptionExpanded}
                  aria-controls="pd-description"
                  onClick={() => setDescriptionExpanded((expanded) => !expanded)}
                >
                  {descriptionExpanded ? 'Show less' : 'Read more'}
                  <i
                    className={`fas fa-chevron-${descriptionExpanded ? 'up' : 'down'}`}
                    aria-hidden="true"
                  />
                </button>
              </div>
            )}

            <div className="pd-delivery-card">
              <div className="pd-delivery-card__icon" aria-hidden="true">
                <i className="fas fa-truck-fast" />
              </div>
              <div>
                <strong>Delivery in Delhi NCR</strong>
                <span>Order before 6 PM for same-day delivery</span>
              </div>
              <span className="pd-delivery-card__status">Available</span>
            </div>

            <div className="pd-purchase-card">
              <div className="pd-qty-row">
                <label htmlFor="pd-qty" className="pd-qty-label">Qty</label>
                <div className="pd-qty">
                  <button type="button" onClick={() => setQty((q) => Math.max(1, q - 1))} aria-label="Decrease">−</button>
                  <input id="pd-qty" type="number" min={1} value={qty} readOnly />
                  <button type="button" onClick={() => setQty((q) => q + 1)} aria-label="Increase">+</button>
                </div>
              </div>

              <div className="pd-cta-row pd-cta-row--main">
                {inStock ? (
                  <>
                    <button type="button" className="pd-btn pd-btn--outline" onClick={handleAddToCart} disabled={adding || buying}>
                      <i className="fas fa-cart-plus" aria-hidden="true" />
                      {adding ? 'Adding…' : 'Add to cart'}
                    </button>
                    <button type="button" className="pd-btn pd-btn--primary" onClick={handleBuyNow} disabled={adding || buying}>
                      <i className="fas fa-bolt" aria-hidden="true" />
                      {buying ? 'Redirecting…' : 'Buy now'}
                    </button>
                  </>
                ) : (
                  <button type="button" className="pd-btn pd-btn--disabled" disabled>Sold out</button>
                )}
              </div>
            </div>

            <div className="pd-trust-strip">
              <div className="pd-trust-item"><i className="fas fa-leaf" aria-hidden="true" /><span>Freshness<br />guaranteed</span></div>
              <div className="pd-trust-item"><i className="fas fa-shield-halved" aria-hidden="true" /><span>100% secure<br />checkout</span></div>
              <div className="pd-trust-item"><i className="fas fa-message" aria-hidden="true" /><span>Free message<br />card</span></div>
              <div className="pd-trust-item"><i className="fas fa-star" aria-hidden="true" /><span>Rated 4.8<br />by customers</span></div>
            </div>
          </div>
        </div>

        {related.length > 0 && (
          <section className="pd-related" aria-labelledby="pd-related-title">
            <div className="pd-related__head">
              <div>
                <p className="pd-related__eyebrow">More fresh picks</p>
                <h2 id="pd-related-title">You may also like</h2>
              </div>
              <div className="pd-related__nav" aria-label="Recommendation controls">
                <button type="button" onClick={() => scrollRelated(-1)} aria-label="Previous products">
                  <i className="fas fa-chevron-left" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => scrollRelated(1)} aria-label="Next products">
                  <i className="fas fa-chevron-right" aria-hidden="true" />
                </button>
              </div>
            </div>
            <div className="pd-related__track hide-scrollbar" ref={relatedTrackRef}>
              {related.map((item) => (
                <ProductCard key={`${item.type}-${item.id}`} product={item} variant="rail" />
              ))}
            </div>
          </section>
        )}
        </div>

        <div className="pd-sticky-cta" aria-label="Purchase actions">
          <div className="pd-sticky-inner">
            {inStock ? (
              <>
                <button type="button" className="pd-btn pd-btn--outline" onClick={handleAddToCart} disabled={adding || buying}>
                  {adding ? 'Adding…' : 'Add to cart'}
                </button>
                <button type="button" className="pd-btn pd-btn--primary" onClick={handleBuyNow} disabled={adding || buying}>
                  {buying ? 'Redirecting…' : 'Buy now'}
                </button>
              </>
            ) : (
              <button type="button" className="pd-btn pd-btn--disabled" disabled>Sold out</button>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
