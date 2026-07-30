'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { apiGet, apiSend } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { formatInr, resolveImageSrc } from '@/lib/images';
import type { CartData } from '@/lib/types';

export default function CartPage() {
  const { refreshCart } = useCart();
  const [cart, setCart] = useState<CartData | null>(null);
  const [loading, setLoading] = useState(true);
  const [couponCode, setCouponCode] = useState('');
  const [couponBusy, setCouponBusy] = useState(false);
  const [couponMsg, setCouponMsg] = useState<{ type: 'ok' | 'err'; text: string } | null>(null);

  async function loadCart() {
    setLoading(true);
    try {
      const data = await apiGet<CartData>('/cart');
      setCart(data);
    } catch {
      setCart(null);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    void loadCart();
  }, []);

  async function updateQty(index: number, change: number) {
    try {
      const data = await apiSend<CartData>(`/cart/items/${index}`, 'PATCH', { change });
      setCart(data);
      await refreshCart();
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not update cart');
    }
  }

  async function removeItem(index: number) {
    try {
      const data = await apiSend<CartData>(`/cart/items/${index}`, 'DELETE');
      setCart(data);
      await refreshCart();
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not remove item');
    }
  }

  async function applyCoupon(e: React.FormEvent) {
    e.preventDefault();
    if (!couponCode.trim()) return;
    setCouponBusy(true);
    setCouponMsg(null);
    try {
      const data = await apiSend<CartData>('/cart/coupon', 'POST', { code: couponCode.trim() });
      setCart(data);
      await refreshCart();
      setCouponMsg({ type: 'ok', text: 'Coupon applied successfully!' });
      setCouponCode('');
    } catch (err) {
      setCouponMsg({
        type: 'err',
        text: err instanceof Error ? err.message : 'Could not apply coupon',
      });
    } finally {
      setCouponBusy(false);
    }
  }

  async function removeCoupon() {
    setCouponBusy(true);
    setCouponMsg(null);
    try {
      const data = await apiSend<CartData>('/cart/coupon', 'DELETE');
      setCart(data);
      await refreshCart();
      setCouponMsg({ type: 'ok', text: 'Promo code removed.' });
    } catch (err) {
      setCouponMsg({
        type: 'err',
        text: err instanceof Error ? err.message : 'Could not remove coupon',
      });
    } finally {
      setCouponBusy(false);
    }
  }

  if (loading) {
    return (
      <main className="container mx-auto px-4 py-16 text-center text-slate-500">
        Loading cart…
      </main>
    );
  }

  if (!cart || cart.items.length === 0) {
    return (
      <main className="container mx-auto px-4 py-16 text-center">
        <span className="material-icons-outlined text-6xl text-slate-300 mb-4">shopping_cart</span>
        <h1 className="text-2xl font-bold mb-2">Your cart is empty</h1>
        <p className="text-slate-500 mb-8">Add fresh flowers, cakes or gifts to get started.</p>
        <Link
          href="/flowers"
          className="inline-block bg-primary text-white font-bold px-8 py-3 rounded-xl"
        >
          Shop Flowers
        </Link>
      </main>
    );
  }

  const couponLabel =
    cart.coupon && typeof cart.coupon === 'object' && cart.coupon !== null && 'code' in cart.coupon
      ? String((cart.coupon as { code?: string }).code ?? '')
      : '';

  return (
    <main className="container mx-auto px-4 py-8 md:py-12 max-w-4xl">
      <h1 className="text-3xl font-bold mb-8">Your Cart</h1>
      <div className="space-y-4 mb-8">
        {cart.items.map((item, index) => (
          <div
            key={`${item.category}-${item.id}-${index}`}
            className="flex gap-4 bg-white rounded-2xl border border-slate-100 p-4 shadow-sm"
          >
            <div className="w-20 h-20 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0">
              <img
                src={resolveImageSrc(item.image)}
                alt={item.name}
                className="w-full h-full object-cover"
              />
            </div>
            <div className="flex-1 min-w-0">
              <h2 className="font-bold text-sm truncate">{item.name}</h2>
              <p className="text-primary font-bold mt-1">{formatInr(item.price)}</p>
              <div className="flex items-center gap-3 mt-3">
                <button
                  type="button"
                  className="w-8 h-8 rounded-full border border-slate-200 hover:bg-slate-50"
                  onClick={() => updateQty(index, -1)}
                  aria-label="Decrease quantity"
                >
                  −
                </button>
                <span className="font-bold text-sm w-6 text-center">{item.qty}</span>
                <button
                  type="button"
                  className="w-8 h-8 rounded-full border border-slate-200 hover:bg-slate-50"
                  onClick={() => updateQty(index, 1)}
                  aria-label="Increase quantity"
                >
                  +
                </button>
                <button
                  type="button"
                  className="ml-auto text-red-500 text-sm font-bold hover:underline"
                  onClick={() => removeItem(index)}
                >
                  Remove
                </button>
              </div>
            </div>
            <div className="font-bold text-sm">{formatInr(item.price * item.qty)}</div>
          </div>
        ))}
      </div>

      <div className="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
        <form onSubmit={applyCoupon} className="flex flex-col sm:flex-row gap-2 mb-4">
          <input
            type="text"
            value={couponCode}
            onChange={(e) => setCouponCode(e.target.value.toUpperCase())}
            placeholder="Promo code"
            className="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm outline-none"
            disabled={couponBusy || Boolean(couponLabel)}
          />
          {couponLabel ? (
            <button
              type="button"
              onClick={() => void removeCoupon()}
              disabled={couponBusy}
              className="px-5 py-3 rounded-lg border border-slate-200 font-bold text-sm text-red-500"
            >
              Remove {couponLabel}
            </button>
          ) : (
            <button
              type="submit"
              disabled={couponBusy || !couponCode.trim()}
              className="px-5 py-3 rounded-lg bg-slate-900 text-white font-bold text-sm disabled:opacity-50"
            >
              {couponBusy ? 'Applying…' : 'Apply'}
            </button>
          )}
        </form>
        {couponMsg && (
          <p
            className={`text-sm mb-4 ${couponMsg.type === 'ok' ? 'text-green-600' : 'text-red-600'}`}
          >
            {couponMsg.text}
          </p>
        )}

        <div className="flex justify-between text-sm mb-2">
          <span className="text-slate-500">Subtotal</span>
          <span>{formatInr(cart.subtotal)}</span>
        </div>
        {cart.discountAmount > 0 && (
          <div className="flex justify-between text-sm mb-2 text-green-600">
            <span>Discount{couponLabel ? ` (${couponLabel})` : ''}</span>
            <span>- {formatInr(cart.discountAmount)}</span>
          </div>
        )}
        <div className="flex justify-between font-bold text-lg pt-3 border-t border-slate-100">
          <span>Total</span>
          <span className="text-primary">{formatInr(cart.grandTotal)}</span>
        </div>
        <Link
          href="/checkout"
          className="block w-full mt-6 bg-primary text-white font-bold py-4 rounded-xl text-center shadow-lg hover:scale-[1.01] transition-all"
        >
          Proceed to Checkout
        </Link>
      </div>
    </main>
  );
}
