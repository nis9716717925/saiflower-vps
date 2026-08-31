'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import { apiSend } from '@/lib/api';
import { addProductToCart } from '@/lib/buy-now';
import { useCart } from '@/components/providers/AppProviders';
import { discountPercent, formatInr, productHref } from '@/lib/images';
import type { Product } from '@/lib/types';

/** Matches live PHP flowers.php product card (Tailwind), not shop-luxe sp-card. */
export function isDecorationProduct(product: Product): boolean {
  const hay = `${product.name} ${product.tag ?? ''}`.toLowerCase();
  return (
    /\bcar\s*decor/.test(hay) ||
    /\bwedding\s*decor/.test(hay) ||
    /\bfirst\s*night/.test(hay) ||
    /\broom\s*decor/.test(hay) ||
    /\bstage\s*decor/.test(hay) ||
    /\bevent\s*decor/.test(hay) ||
    /\bdecoration(s)?\b/.test(hay) ||
    /\bdecor\b/.test(hay) ||
    /\bvenue\b/.test(hay) ||
    /\bworkshop\b/.test(hay) ||
    /\bmandap\b/.test(hay) ||
    /\bstage\b/.test(hay) ||
    /\bbackdrop\b/.test(hay) ||
    /\bgarland\s*install/.test(hay)
  );
}

export function FlowerShopCard({ product }: { product: Product }) {
  const router = useRouter();
  const { refreshCart } = useCart();
  const [adding, setAdding] = useState(false);
  const [wished, setWished] = useState(false);

  const href = product.url ?? productHref(product.type, product.slug);
  const discount = discountPercent(product.price, product.originalPrice);
  const rating = product.rating && product.rating > 0 ? product.rating : 4.8;
  const inStock = product.inStock !== false;
  const sameDay = product.deliverySameday !== false;
  const nextDay = product.deliveryNextday === true;
  const isDecor = isDecorationProduct(product);

  async function handleWishlist(e: React.MouseEvent) {
    e.preventDefault();
    e.stopPropagation();
    try {
      const result = await apiSend<{ action: string }>('/wishlist/toggle', 'POST', {
        product_id: product.id,
        type: product.type,
      });
      setWished(result.action === 'added');
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Wishlist requires login';
      if (/login|auth|unauthorized|token/i.test(msg)) {
        window.location.href = `/login?redirect=${encodeURIComponent(href)}`;
        return;
      }
      alert(msg);
    }
  }

  async function handleBuyNow(e: React.MouseEvent) {
    e.preventDefault();
    e.stopPropagation();
    if (!inStock || adding) return;
    setAdding(true);
    try {
      await addProductToCart(product);
      await refreshCart();
      router.push('/cart');
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not add to cart');
      setAdding(false);
    }
  }

  return (
    <article className="sf-pcard group">
      <div className="sf-pcard__media">
        <button
          type="button"
          onClick={handleWishlist}
          className={`sf-pcard__wish${wished ? ' is-on' : ''}`}
          aria-label={wished ? 'Remove from wishlist' : 'Add to wishlist'}
          aria-pressed={wished}
        >
          <i className={`${wished ? 'fas' : 'far'} fa-heart`} aria-hidden="true" />
        </button>

        {discount > 0 ? (
          <span className="sf-pcard__badge sf-pcard__badge--sale">{discount}% OFF</span>
        ) : isDecor ? (
          <span className="sf-pcard__badge sf-pcard__badge--decor">Decor</span>
        ) : null}

        <Link href={href} className="sf-pcard__img-link" title={product.name}>
          <OptimizedImage
            src={product.image}
            width={400}
            height={500}
            alt={product.name}
            sizes={IMAGE_SIZE_PRESETS.productCard}
          />
        </Link>

        {!inStock ? <span className="sf-pcard__oos">Out of stock</span> : null}
      </div>

      <div className="sf-pcard__body">
        <div className="sf-pcard__meta">
          <span className="sf-pcard__rating" aria-label={`Rated ${rating.toFixed(1)}`}>
            <i className="fas fa-star" aria-hidden="true" /> {rating.toFixed(1)}
          </span>
          {sameDay && inStock ? (
            <span className="sf-pcard__eta sf-pcard__eta--fast">
              <i className="fas fa-bolt" aria-hidden="true" /> Same day
            </span>
          ) : nextDay ? (
            <span className="sf-pcard__eta">
              <i className="fas fa-truck" aria-hidden="true" /> Next day
            </span>
          ) : null}
        </div>

        <h3 className="sf-pcard__title">
          <Link href={href}>{product.name}</Link>
        </h3>

        <div className="sf-pcard__price-row">
          <span className="sf-pcard__price">{formatInr(product.price)}</span>
          {discount > 0 && product.originalPrice != null ? (
            <span className="sf-pcard__mrp">{formatInr(product.originalPrice)}</span>
          ) : null}
        </div>

        {inStock ? (
          <button
            type="button"
            onClick={handleBuyNow}
            disabled={adding}
            className={`sf-pcard__add${adding ? ' is-added' : ''}`}
          >
            {adding ? 'Buying…' : 'BUY'}
          </button>
        ) : (
          <button type="button" className="sf-pcard__add is-disabled" disabled>
            Sold out
          </button>
        )}
      </div>
    </article>
  );
}
