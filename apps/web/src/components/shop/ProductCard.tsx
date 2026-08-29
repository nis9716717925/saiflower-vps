'use client';

import Link from 'next/link';
import { useState } from 'react';
import { OptimizedImage } from '@/components/ui/OptimizedImage';
import { apiSend } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import {
  discountPercent,
  formatInr,
  productHref,
  reviewCountEstimate,
} from '@/lib/images';
import type { Product } from '@/lib/types';

interface ProductCardProps {
  product: Product;
  variant?: 'grid' | 'rail';
}

export function ProductCard({ product, variant = 'grid' }: ProductCardProps) {
  const { refreshCart } = useCart();
  const [adding, setAdding] = useState(false);
  const [wished, setWished] = useState(false);

  async function handleWishlist(e: React.MouseEvent) {
    e.preventDefault();
    try {
      const result = await apiSend<{ action: string }>('/wishlist/toggle', 'POST', {
        product_id: product.id,
        type: product.type,
      });
      setWished(result.action === 'added');
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Wishlist requires login';
      if (/login|auth|unauthorized|token/i.test(msg)) {
        window.location.href = `/login?redirect=${encodeURIComponent(productHref(product.type, product.slug))}`;
        return;
      }
      alert(msg);
    }
  }

  const href = product.url ?? productHref(product.type, product.slug);
  const discount = discountPercent(product.price, product.originalPrice);
  const rating = product.rating ?? 4.8;
  const reviews = reviewCountEstimate(product.id);
  const inStock = product.inStock !== false;
  const sameDay = product.deliverySameday !== false;
  const isLuxe = product.price >= 2499;

  async function handleAddToCart(e: React.MouseEvent) {
    e.preventDefault();
    if (!inStock || adding) return;
    setAdding(true);
    try {
      await apiSend('/cart/items', 'POST', {
        productId: product.id,
        category: product.type,
        quantity: 1,
        name: product.name,
        price: product.price,
        image: product.image,
      });
      await refreshCart();
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not add to cart');
    } finally {
      setAdding(false);
    }
  }

  return (
    <article className={`sp-card sp-card--${variant}`}>
      <div className="sp-card__media">
        {discount > 0 ? (
          <span className="sp-card__badge sp-card__badge--sale">{discount}% OFF</span>
        ) : isLuxe ? (
          <span className="sp-card__badge sp-card__badge--luxe">Luxe</span>
        ) : null}

        <button
          type="button"
          className="sp-card__wish"
          aria-label={wished ? 'Remove from wishlist' : 'Add to wishlist'}
          aria-pressed={wished}
          onClick={handleWishlist}
        >
          <i className={`${wished ? 'fas' : 'far'} fa-heart`} aria-hidden="true" />
        </button>

        <Link className="sp-card__img-link" href={href} title={product.name}>
          <OptimizedImage
            src={product.image}
            alt={product.name}
            width={420}
            height={520}
          />
        </Link>

        {!inStock && <span className="sp-card__oos">Out of stock</span>}
      </div>

      <div className="sp-card__body">
        <div
          className="sp-card__rating"
          aria-label={`Rated ${rating.toFixed(1)} from ${reviews} reviews`}
        >
          <i className="fas fa-star" aria-hidden="true" />
          <span>{rating.toFixed(1)}</span>
          <span className="sp-card__reviews">({reviews.toLocaleString('en-IN')})</span>
        </div>

        <h3 className="sp-card__title">
          <Link href={href}>{product.name}</Link>
        </h3>

        <p className="sp-card__delivery">
          {sameDay && inStock ? (
            <>
              <i className="fas fa-bolt" aria-hidden="true" /> Same day delivery
            </>
          ) : (
            <>
              <i className="fas fa-truck" aria-hidden="true" /> Scheduled delivery
            </>
          )}
        </p>

        <div className="sp-card__price-row">
          <span className="sp-card__price">{formatInr(product.price)}</span>
          {discount > 0 && product.originalPrice != null && (
            <>
              <span className="sp-card__mrp">{formatInr(product.originalPrice)}</span>
              <span className="sp-card__save">Save {discount}%</span>
            </>
          )}
        </div>

        <p className="sp-card__trust">
          <i className="fas fa-shield-halved" aria-hidden="true" /> Freshness guaranteed
        </p>

        <div className="sp-card__actions">
          {inStock ? (
            <>
              <Link className="sp-card__cta sp-card__cta--ghost" href={href}>
                View
              </Link>
              <button
                type="button"
                className="sp-card__cta sp-card__cta--primary"
                onClick={handleAddToCart}
                disabled={adding}
              >
                {adding ? 'Adding…' : 'Add to cart'}
              </button>
            </>
          ) : (
            <button type="button" className="sp-card__cta sp-card__cta--disabled" disabled>
              Sold out
            </button>
          )}
        </div>
      </div>
    </article>
  );
}
