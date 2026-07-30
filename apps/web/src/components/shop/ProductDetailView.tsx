'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import Script from 'next/script';
import { useState } from 'react';
import { apiSend } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { ProductCard } from '@/components/shop/ProductCard';
import { discountPercent, formatInr, resolveImageSrc } from '@/lib/images';
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
  const img = resolveImageSrc(product.image);
  const discount = discountPercent(product.price, product.originalPrice);
  const inStock = product.inStock !== false;
  const rating = product.rating ?? 5.0;
  const related = product.related ?? [];
  const gallery = product.galleryImages?.length
    ? product.galleryImages
    : product.imagesGallery
      ? product.imagesGallery.split(',').map((s) => s.trim()).filter(Boolean)
      : [];

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
      router.push('/checkout');
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not add to cart');
      setBuying(false);
    }
  }

  return (
    <>
      <Script src="/assets/js/product-detail-premium.js" strategy="afterInteractive" />
      <div className="pd-container pd-page">
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
              <span className="pd-zoom-hint">Tap to enlarge</span>
              <img
                src={img}
                id="mainView"
                width={800}
                height={800}
                fetchPriority="high"
                className="visible-media"
                alt={product.name}
              />
            </div>
            {gallery.length > 1 && (
              <div className="pd-thumbs mt-3 flex gap-2 overflow-x-auto">
                {gallery.map((src) => (
                  <button
                    key={src}
                    type="button"
                    className="w-16 h-16 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0"
                    onClick={() => {
                      const el = document.getElementById('mainView') as HTMLImageElement | null;
                      if (el) el.src = resolveImageSrc(src);
                    }}
                  >
                    <img src={resolveImageSrc(src)} alt="" className="w-full h-full object-cover" />
                  </button>
                ))}
              </div>
            )}
          </div>

          <div className="pd-buy-col pd-buy-sticky product-details">
            <div className="pd-badge-row">
              {inStock ? (
                <>
                  <span className="pd-badge">Signature selection</span>
                  {discount > 0 && <span className="pd-badge pd-badge-sale">Limited offer</span>}
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
            </div>

            {product.description && (
              <div
                className="pd-desc prose prose-sm max-w-none text-slate-600"
                dangerouslySetInnerHTML={{ __html: product.description }}
              />
            )}

            <div className="pd-qty-row">
              <label htmlFor="pd-qty" className="pd-qty-label">
                Quantity
              </label>
              <div className="pd-qty-control">
                <button
                  type="button"
                  onClick={() => setQty((q) => Math.max(1, q - 1))}
                  aria-label="Decrease"
                >
                  −
                </button>
                <input id="pd-qty" type="number" min={1} value={qty} readOnly />
                <button type="button" onClick={() => setQty((q) => q + 1)} aria-label="Increase">
                  +
                </button>
              </div>
            </div>

            <div className="pd-actions">
              {inStock ? (
                <>
                  <button
                    type="button"
                    className="pd-btn pd-btn--primary"
                    onClick={handleAddToCart}
                    disabled={adding || buying}
                  >
                    {adding ? 'Adding…' : 'Add to cart'}
                  </button>
                  <button
                    type="button"
                    className="pd-btn pd-btn--secondary"
                    onClick={handleBuyNow}
                    disabled={adding || buying}
                  >
                    {buying ? 'Redirecting…' : 'Buy now'}
                  </button>
                </>
              ) : (
                <button type="button" className="pd-btn pd-btn--disabled" disabled>
                  Sold out
                </button>
              )}
            </div>

            <ul className="pd-trust-list">
              <li>
                <i className="fas fa-bolt" aria-hidden="true" /> Same-day delivery in Delhi NCR
              </li>
              <li>
                <i className="fas fa-leaf" aria-hidden="true" /> Freshness guaranteed
              </li>
              <li>
                <i className="fas fa-shield-halved" aria-hidden="true" /> Secure checkout
              </li>
            </ul>
          </div>
        </div>

        {related.length > 0 && (
          <section className="mt-12 mb-8" aria-labelledby="pd-related-title">
            <h2 id="pd-related-title" className="text-2xl font-bold mb-6">
              You may also like
            </h2>
            <div className="sp-grid">
              {related.map((item) => (
                <ProductCard key={`${item.type}-${item.id}`} product={item} />
              ))}
            </div>
          </section>
        )}
      </div>
    </>
  );
}
