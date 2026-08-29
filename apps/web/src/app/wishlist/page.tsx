'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { apiGet, apiSend, getCustomer } from '@/lib/api';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import { formatInr, productHref } from '@/lib/images';
import type { CustomerProfile } from '@/lib/types';

interface WishlistItem {
  wishlistId: number;
  productId: number;
  type: string;
  name: string;
  slug: string;
  image: string;
  price: number;
  url?: string;
}

export default function WishlistPage() {
  const [customer, setCustomer] = useState<CustomerProfile | null>(null);
  const [items, setItems] = useState<WishlistItem[]>([]);
  const [loading, setLoading] = useState(true);

  async function load() {
    const local = getCustomer();
    setCustomer(local);
    if (!local) {
      setLoading(false);
      return;
    }
    try {
      const data = await apiGet<WishlistItem[]>('/wishlist');
      setItems(data ?? []);
    } catch {
      setItems([]);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    void load();
  }, []);

  async function removeItem(item: WishlistItem) {
    try {
      await apiSend('/wishlist/toggle', 'POST', {
        product_id: item.productId,
        type: item.type,
      });
      setItems((prev) => prev.filter((x) => x.wishlistId !== item.wishlistId));
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not update wishlist');
    }
  }

  if (loading) {
    return (
      <main className="container mx-auto px-4 py-16 text-center text-slate-500">
        Loading wishlist…
      </main>
    );
  }

  if (!customer) {
    return (
      <main className="container mx-auto px-4 py-16 text-center max-w-lg">
        <span className="material-icons-outlined text-6xl text-slate-300 mb-4">favorite_border</span>
        <h1 className="text-2xl font-bold mb-2">Your Wishlist</h1>
        <p className="text-slate-500 mb-8">Sign in to save your favourite bouquets and gifts.</p>
        <Link
          href="/login?redirect=/wishlist"
          className="inline-block bg-primary text-white font-bold px-8 py-3 rounded-xl"
        >
          Login
        </Link>
      </main>
    );
  }

  return (
    <main className="container mx-auto px-4 py-8 md:py-12 max-w-5xl">
      <h1 className="text-3xl font-bold mb-8">Your Wishlist</h1>
      {items.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-slate-500 mb-6">No saved items yet.</p>
          <Link href="/flowers" className="text-primary font-bold hover:underline">
            Browse flowers
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {items.map((item) => {
            const href = item.url || productHref(item.type, item.slug);
            return (
              <article
                key={item.wishlistId}
                className="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm"
              >
                <Link href={href}>
                  <OptimizedImage
                    src={item.image}
                    alt={item.name}
                    className="w-full h-56 object-cover"
                    width={400}
                    height={224}
                    sizes={IMAGE_SIZE_PRESETS.productCard}
                  />
                </Link>
                <div className="p-4">
                  <h2 className="font-bold text-sm mb-1">
                    <Link href={href}>{item.name}</Link>
                  </h2>
                  <p className="text-primary font-bold mb-3">{formatInr(item.price)}</p>
                  <button
                    type="button"
                    className="text-sm text-red-500 font-semibold hover:underline"
                    onClick={() => void removeItem(item)}
                  >
                    Remove
                  </button>
                </div>
              </article>
            );
          })}
        </div>
      )}
    </main>
  );
}
