'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { apiGet, apiSend, getAccessToken } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { CheckoutProgress } from '@/components/checkout/CheckoutProgress';
import { formatInr, resolveImageSrc } from '@/lib/images';
import type { CartData } from '@/lib/types';

const CHECKOUT_REDIRECT = encodeURIComponent('/checkout');

export default function CartPage() {
  const { refreshCart } = useCart();
  const [cart, setCart] = useState<CartData | null>(null);
  const [loading, setLoading] = useState(true);
  const [loggedIn, setLoggedIn] = useState(false);
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
    setLoggedIn(Boolean(getAccessToken()));
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
      <main className="qc-shell">
        <div className="qc-skeleton" />
      </main>
    );
  }

  if (!cart || cart.items.length === 0) {
    return (
      <main className="qc-shell">
        <div className="qc-card qc-empty">
          <span className="material-icons-outlined">shopping_cart</span>
          <h1>Your cart is empty</h1>
          <p>Add fresh flowers, cakes or gifts to get started.</p>
          <Link href="/flowers" className="qc-cta" style={{ maxWidth: 240, margin: '0 auto' }}>
            Shop Flowers
          </Link>
        </div>
      </main>
    );
  }

  const couponLabel =
    cart.coupon && typeof cart.coupon === 'object' && cart.coupon !== null && 'code' in cart.coupon
      ? String((cart.coupon as { code?: string }).code ?? '')
      : '';

  const continueHref = loggedIn ? '/checkout' : `/login?redirect=${CHECKOUT_REDIRECT}`;
  const guestCheckoutHref = '/checkout?guest=1';

  const checkoutActions = !loggedIn ? (
    <div className="qc-auth-actions">
      <Link href={continueHref} className="qc-cta">
        Log in to continue
        <span className="material-icons-outlined" style={{ fontSize: '1.1rem' }}>
          login
        </span>
      </Link>
      <Link
        href={`/register?redirect=${CHECKOUT_REDIRECT}`}
        className="qc-cta qc-cta--ghost"
      >
        Create account
      </Link>
      <Link href={guestCheckoutHref} className="qc-cta qc-cta--guest">
        Continue as guest
        <span className="material-icons-outlined" style={{ fontSize: '1.1rem' }}>
          arrow_forward
        </span>
      </Link>
      <p className="qc-muted qc-auth-actions__hint">
        Guest checkout skips saving your address — you can still pay on WhatsApp.
      </p>
    </div>
  ) : (
    <Link href={continueHref} className="qc-cta qc-cta--desktop-only" style={{ marginTop: '1rem' }}>
      Continue
      <span className="material-icons-outlined" style={{ fontSize: '1.1rem' }}>
        arrow_forward
      </span>
    </Link>
  );

  const mobileContinue = !loggedIn ? (
    <div className="qc-mobile-bar__actions">
      <Link href={guestCheckoutHref} className="qc-cta qc-cta--guest qc-cta--compact">
        Guest
      </Link>
      <Link href={continueHref} className="qc-cta qc-cta--compact">
        Log in
      </Link>
    </div>
  ) : (
    <Link href={continueHref} className="qc-cta">
      Continue
    </Link>
  );

  const billCard = (
    <div className="qc-card">
      <div className="qc-card__head">
        <h2 className="qc-card__title">
          <span className="material-icons-outlined">receipt_long</span>
          Estimated bill
        </h2>
      </div>

      <form onSubmit={applyCoupon} className="qc-grid" style={{ marginBottom: '0.9rem' }}>
        <div className="qc-field">
          <label className="qc-label">Promo code</label>
          <div style={{ display: 'flex', gap: '0.5rem' }}>
            <input
              type="text"
              value={couponCode}
              onChange={(e) => setCouponCode(e.target.value.toUpperCase())}
              placeholder="Enter code"
              className="qc-input"
              disabled={couponBusy || Boolean(couponLabel)}
            />
            {couponLabel ? (
              <button
                type="button"
                onClick={() => void removeCoupon()}
                disabled={couponBusy}
                className="qc-cta--ghost"
                style={{
                  minWidth: 110,
                  borderRadius: '0.85rem',
                  border: '1px solid #f3d0cd',
                  color: '#b42318',
                  fontWeight: 800,
                  background: '#fff',
                  cursor: 'pointer',
                }}
              >
                Remove
              </button>
            ) : (
              <button
                type="submit"
                disabled={couponBusy || !couponCode.trim()}
                className="qc-cta"
                style={{ minWidth: 96, boxShadow: 'none' }}
              >
                {couponBusy ? '…' : 'Apply'}
              </button>
            )}
          </div>
        </div>
      </form>

      {couponMsg && (
        <div
          className={`qc-alert ${couponMsg.type === 'ok' ? 'qc-alert--ok' : 'qc-alert--err'}`}
          style={{ marginBottom: '0.85rem' }}
        >
          {couponMsg.text}
        </div>
      )}

      {couponLabel ? (
        <div className="qc-alert qc-alert--ok" style={{ marginBottom: '0.85rem' }}>
          Applied: <strong>{couponLabel}</strong>
        </div>
      ) : null}

      <div className="qc-bill">
        <div className="qc-bill__row">
          <span>Item total</span>
          <span>{formatInr(cart.subtotal)}</span>
        </div>
        {cart.discountAmount > 0 && (
          <div className="qc-bill__row qc-bill__row--discount">
            <span>Discount</span>
            <span>- {formatInr(cart.discountAmount)}</span>
          </div>
        )}
        <div className="qc-bill__row">
          <span>Delivery fee</span>
          <span>After address</span>
        </div>
        <div className="qc-bill__total">
          <span>To pay (est.)</span>
          <strong>{formatInr(cart.grandTotal)}</strong>
        </div>
      </div>

      {!loggedIn && (
        <div className="qc-alert qc-alert--info" style={{ marginTop: '0.9rem' }}>
          Log in to save addresses, or continue as guest to checkout on WhatsApp.
        </div>
      )}

      {checkoutActions}
    </div>
  );

  return (
    <main className="qc-shell">
      <CheckoutProgress current="cart" />

      <div className="qc-title-row">
        <div>
          <h1 className="qc-title">Your cart</h1>
          <p className="qc-subtitle">
            {cart.count} item{cart.count === 1 ? '' : 's'} · Same-day delivery across Delhi NCR
          </p>
        </div>
        <Link href="/flowers" className="qc-link-btn">
          + Add more
        </Link>
      </div>

      <div className="qc-trust" style={{ marginBottom: '1rem' }}>
        <div className="qc-trust__item">
          <span className="material-icons-outlined">bolt</span>
          <div>
            <strong>Fast checkout</strong>
            <span>Confirm on WhatsApp</span>
          </div>
        </div>
        <div className="qc-trust__item">
          <span className="material-icons-outlined">local_shipping</span>
          <div>
            <strong>Live delivery fee</strong>
            <span>Calculated by distance</span>
          </div>
        </div>
        <div className="qc-trust__item">
          <span className="material-icons-outlined">verified_user</span>
          <div>
            <strong>Secure account</strong>
            <span>Saved addresses ready</span>
          </div>
        </div>
      </div>

      <div className="qc-layout">
        <div className="qc-stack">
          <div className="qc-card">
            <div className="qc-card__head">
              <h2 className="qc-card__title">
                <span className="material-icons-outlined">shopping_bag</span>
                Items
              </h2>
            </div>
            {cart.items.map((item, index) => (
              <div key={`${item.category}-${item.id}-${index}`} className="qc-item">
                <div className="qc-item__img">
                  <img
                    src={resolveImageSrc(item.image)}
                    alt={item.name}
                    width={96}
                    height={96}
                    loading="lazy"
                    decoding="async"
                  />
                </div>
                <div className="qc-item__body">
                  <h2 className="qc-item__name">{item.name}</h2>
                  <p className="qc-item__meta">{formatInr(item.price)} each</p>
                  <div className="qc-item__row">
                    <div className="qc-qty">
                      <button
                        type="button"
                        onClick={() => updateQty(index, -1)}
                        aria-label="Decrease quantity"
                      >
                        −
                      </button>
                      <span>{item.qty}</span>
                      <button
                        type="button"
                        onClick={() => updateQty(index, 1)}
                        aria-label="Increase quantity"
                      >
                        +
                      </button>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                      <span className="qc-price">{formatInr(item.price * item.qty)}</span>
                      <button type="button" className="qc-remove" onClick={() => removeItem(index)}>
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        <aside className="qc-sticky-summary">{billCard}</aside>
      </div>

      <div className="qc-mobile-bar">
        <div className="qc-mobile-bar__inner">
          <div className="qc-mobile-bar__meta">
            <small>Estimated total</small>
            <strong>{formatInr(cart.grandTotal)}</strong>
          </div>
          {mobileContinue}
        </div>
      </div>
    </main>
  );
}
