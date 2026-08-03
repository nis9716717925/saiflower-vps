'use client';

import Link from 'next/link';
import { useState } from 'react';
import { apiSend } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { discountPercent, formatInr, productHref, resolveImageSrc } from '@/lib/images';
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
  const { refreshCart } = useCart();
  const [adding, setAdding] = useState(false);
  const [wished, setWished] = useState(false);

  const href = product.url ?? productHref(product.type, product.slug);
  const img = resolveImageSrc(product.image);
  const discount = discountPercent(product.price, product.originalPrice);
  const rating = product.rating ?? 5;
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

  async function handleAddToCart(e: React.MouseEvent) {
    e.preventDefault();
    e.stopPropagation();
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
    <div className="group bg-white rounded-2xl overflow-hidden border border-slate-100 hover:border-primary/20 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full">
      <div className="relative overflow-hidden aspect-[4/5] bg-slate-100 group-hover:opacity-90 transition-opacity">
        <button
          type="button"
          onClick={handleWishlist}
          className={`absolute top-3 right-3 w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow-sm transition-colors z-10 ${
            wished ? 'text-red-500' : 'text-gray-400 hover:text-red-500'
          }`}
          aria-label={wished ? 'Remove from wishlist' : 'Add to wishlist'}
        >
          <span className="material-icons-outlined text-lg">
            {wished ? 'favorite' : 'favorite_border'}
          </span>
        </button>

        <Link href={href}>
          <img
            className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            src={img}
            width={400}
            height={500}
            loading="lazy"
            decoding="async"
            alt={product.name}
          />
          {isDecor ? (
            <div className="absolute top-3 left-3 bg-slate-800/90 backdrop-blur text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
              Decor
            </div>
          ) : !inStock ? (
            <div className="absolute top-3 left-3 bg-red-500/90 backdrop-blur text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
              Out of Stock
            </div>
          ) : null}
        </Link>

        {inStock ? (
          <button
            type="button"
            onClick={handleAddToCart}
            disabled={adding}
            className="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity translate-y-2 group-hover:translate-y-0 duration-300 w-10 h-10 bg-white hover:bg-primary hover:text-white text-slate-900 rounded-full shadow-lg flex items-center justify-center transition-colors"
            aria-label="Add to cart"
          >
            <span className="material-icons-outlined text-lg">add_shopping_cart</span>
          </button>
        ) : null}
      </div>

      <div className="p-3 md:p-5 flex flex-col flex-grow">
        <h3 className="font-bold text-sm md:text-base mb-1 text-slate-900 group-hover:text-primary transition-colors line-clamp-2 h-10 md:h-12">
          <Link href={href}>{product.name}</Link>
        </h3>

        <div className="flex items-center gap-1 text-yellow-400 text-xs mb-2">
          <i className="fas fa-star" />
          <span className="text-slate-500 font-bold ml-1">{rating.toFixed(1)}</span>
        </div>

        <div className="flex flex-col gap-1 mb-2 text-[10px] font-medium text-slate-500">
          {sameDay && inStock ? (
            <div className="flex items-center gap-1 text-green-600">
              <span className="material-icons-outlined text-[12px]">local_shipping</span> Same Day
              Delivery
            </div>
          ) : nextDay ? (
            <div className="flex items-center gap-1 text-blue-600">
              <span className="material-icons-outlined text-[12px]">event_available</span> Next Day
              Delivery
            </div>
          ) : null}
        </div>

        <div className="flex flex-wrap items-center gap-1 mb-2 mt-auto min-w-0">
          {discount > 0 && product.originalPrice != null ? (
            <>
              <p className="font-bold text-slate-400 text-xs line-through shrink-0">
                {formatInr(product.originalPrice)}
              </p>
              <p className="font-bold text-primary text-lg shrink-0">{formatInr(product.price)}</p>
              <span className="bg-red-50 text-red-500 text-[10px] font-bold px-1.5 py-0.5 rounded border border-red-100 shrink-0">
                {discount}% OFF
              </span>
            </>
          ) : (
            <p className="font-bold text-primary text-lg">{formatInr(product.price)}</p>
          )}
        </div>

        <div className="mt-auto md:hidden">
          {inStock ? (
            <button
              type="button"
              onClick={handleAddToCart}
              disabled={adding}
              className="w-full bg-[#d4af37] text-white font-bold text-xs py-2.5 rounded-[8px] shadow-md hover:bg-[#c5a028] hover:shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2"
            >
              {adding ? 'Adding…' : 'Buy Now'}
            </button>
          ) : (
            <button
              type="button"
              disabled
              className="w-full bg-slate-100 text-slate-400 font-bold text-xs py-3 rounded-xl cursor-not-allowed"
            >
              Sold Out
            </button>
          )}
        </div>
      </div>
    </div>
  );
}
