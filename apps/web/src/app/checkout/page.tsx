'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';
import { SHIPPING } from '@saiflower/shared';
import { apiGet, apiSend } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { formatInr, resolveImageSrc } from '@/lib/images';
import type { CartData, PlaceOrderResult, ShippingResult } from '@/lib/types';

type ShippingOk = {
  ok: true;
  fee: number;
  distanceKm: number;
  distanceText: string;
};

type ShippingFail = { ok: false };

function buildDeliveryAddress(addressLine: string, city: string, zip: string): string {
  // Must match shipping.service calculateShippingParts (appends India)
  return [addressLine.trim(), city.trim(), zip.trim(), 'India'].filter(Boolean).join(', ');
}

export default function CheckoutPage() {
  const router = useRouter();
  const { refreshCart } = useCart();
  const [cart, setCart] = useState<CartData | null>(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [shippingReady, setShippingReady] = useState(false);
  const [shippingFee, setShippingFee] = useState(0);
  const [distanceKm, setDistanceKm] = useState(0);
  const [distanceText, setDistanceText] = useState('');
  const [shippingMsg, setShippingMsg] = useState<{
    type: 'loading' | 'success' | 'error';
    text: string;
  } | null>(null);

  const [senderName, setSenderName] = useState('');
  const [senderPhone, setSenderPhone] = useState('');
  const [recipientName, setRecipientName] = useState('');
  const [recipientPhone, setRecipientPhone] = useState('');
  const [email, setEmail] = useState('');
  const [addressLine, setAddressLine] = useState('');
  const [city, setCity] = useState('');
  const [zip, setZip] = useState('');
  const [delDate, setDelDate] = useState('');
  const [delTime, setDelTime] = useState('Morning (9am - 12pm)');

  useEffect(() => {
    void (async () => {
      try {
        const data = await apiGet<CartData>('/cart');
        setCart(data);
        if (data.items.length === 0) router.replace('/flowers');
      } catch {
        router.replace('/flowers');
      } finally {
        setLoading(false);
      }
    })();
  }, [router]);

  useEffect(() => {
    const minDate = new Date().toISOString().slice(0, 10);
    setDelDate(minDate);
  }, []);

  const subtotal = cart?.subtotal ?? 0;
  const discount = cart?.discountAmount ?? 0;
  const grandTotal = Math.max(0, subtotal + shippingFee - discount);

  async function calculateShipping(): Promise<ShippingOk | ShippingFail> {
    if (!addressLine.trim() || !city.trim() || !zip.trim()) {
      setShippingReady(false);
      setShippingFee(0);
      setShippingMsg(null);
      return { ok: false };
    }
    setShippingMsg({ type: 'loading', text: 'Calculating delivery distance…' });
    try {
      const result = await apiSend<ShippingResult>('/shipping/calculate', 'POST', {
        address_line: addressLine,
        city,
        zip,
      });
      if (result.status === 'ok') {
        const fee = result.shipping_fee ?? 0;
        const km = result.distance_km ?? 0;
        const text = result.distance_text ?? `${km} km`;
        setShippingReady(true);
        setShippingFee(fee);
        setDistanceKm(km);
        setDistanceText(text);
        setShippingMsg({
          type: 'success',
          text: `Delivery distance: ${text} | Shipping: ${formatInr(fee)} (₹${SHIPPING.ratePerKmInr}/km)`,
        });
        return { ok: true, fee, distanceKm: km, distanceText: text };
      }
      setShippingReady(false);
      setShippingFee(0);
      setShippingMsg({ type: 'error', text: result.message ?? 'Could not calculate shipping.' });
      return { ok: false };
    } catch (err) {
      setShippingReady(false);
      setShippingFee(0);
      setShippingMsg({
        type: 'error',
        text: err instanceof Error ? err.message : 'Unable to calculate shipping.',
      });
      return { ok: false };
    }
  }

  useEffect(() => {
    const timer = setTimeout(() => void calculateShipping(), 600);
    return () => clearTimeout(timer);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [addressLine, city, zip]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!cart?.items.length) return;

    let fee = shippingFee;
    let km = distanceKm;
    if (!shippingReady) {
      const shipping = await calculateShipping();
      if (!shipping.ok) {
        alert('Please enter a valid delivery address so we can calculate shipping.');
        return;
      }
      fee = shipping.fee;
      km = shipping.distanceKm;
    }

    setSubmitting(true);
    const address = buildDeliveryAddress(addressLine, city, zip);
    const itemLines = cart.items.map(
      (item) => `• ${item.name} (x${item.qty}) - ${formatInr(item.price * item.qty)}`,
    );
    const payable = Math.max(0, subtotal + fee - discount);

    try {
      const result = await apiSend<PlaceOrderResult>('/checkout/place-order', 'POST', {
        name: senderName,
        phone: senderPhone,
        email,
        address,
        date: delDate,
        delivery_time: delTime,
        recipient_name: recipientName,
        recipient_phone: recipientPhone,
        items: itemLines.join('\n'),
        total: payable,
        shipping_fee: fee,
        distance_km: km,
        discount_amount: discount,
      });
      await refreshCart();
      if (result.whatsappUrl) window.open(result.whatsappUrl, '_blank');
      router.push(`/?order_success=1&oid=${result.order_id}`);
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not place order');
      setSubmitting(false);
    }
  }

  if (loading || !cart) {
    return (
      <main className="container mx-auto px-4 py-16 text-center text-slate-500">
        Loading checkout…
      </main>
    );
  }

  return (
    <main className="container mx-auto px-4 py-8 md:py-12">
      <form id="checkoutForm" onSubmit={handleSubmit}>
        <div className="max-w-6xl mx-auto flex flex-col lg:flex-row gap-8">
          <div className="lg:w-7/12">
            <h1 className="text-3xl font-bold mb-8">Checkout</h1>

            <div className="section-card bg-white border border-slate-100 rounded-2xl p-6 md:p-8 mb-6">
              <div className="flex items-center gap-3 mb-6">
                <span className="material-icons-outlined text-primary">local_shipping</span>
                <h2 className="text-xl font-bold">Delivery Information</h2>
              </div>

              <div className="mb-6 p-4 bg-blue-50 text-blue-800 rounded-xl flex items-center gap-3 border border-blue-100">
                <span className="material-icons-outlined">info</span>
                <p className="text-sm">
                  Already a member?{' '}
                  <Link href="/login" className="font-bold underline">
                    Login here
                  </Link>{' '}
                  for a faster experience.
                </p>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    Sender Name
                  </label>
                  <input
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm outline-none border"
                    id="sender_name"
                    placeholder="Your name"
                    type="text"
                    value={senderName}
                    onChange={(e) => setSenderName(e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    Sender Phone
                  </label>
                  <input
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm outline-none border"
                    id="sender_phone"
                    placeholder="Your phone number"
                    type="tel"
                    value={senderPhone}
                    onChange={(e) => setSenderPhone(e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    Recipient Name
                  </label>
                  <input
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm outline-none border"
                    id="recipient_name"
                    placeholder="Who is this for?"
                    type="text"
                    value={recipientName}
                    onChange={(e) => setRecipientName(e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    Recipient Phone
                  </label>
                  <input
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm outline-none border"
                    id="recipient_phone"
                    placeholder="Their phone number"
                    type="tel"
                    value={recipientPhone}
                    onChange={(e) => setRecipientPhone(e.target.value)}
                    required
                  />
                </div>
                <div className="md:col-span-2 space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    Email Address
                  </label>
                  <input
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm outline-none border"
                    id="cust_email"
                    placeholder="For receipt..."
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                  />
                </div>

                <div className="md:col-span-2 p-4 bg-slate-50 rounded-xl border border-slate-100">
                  <div className="flex items-start gap-2 text-xs text-slate-600">
                    <span className="material-icons-outlined text-primary text-sm mt-0.5">
                      storefront
                    </span>
                    <div>
                      <p className="font-bold text-slate-800">Dispatching from Sai Flower</p>
                      <p className="mt-1 leading-relaxed">{SHIPPING.storeAddress}</p>
                      <p className="mt-2 text-primary font-semibold">
                        Shipping: ₹{SHIPPING.ratePerKmInr} per km (based on driving distance)
                      </p>
                    </div>
                  </div>
                </div>

                <div className="md:col-span-2 space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    Delivery Address
                  </label>
                  <input
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm outline-none border"
                    id="cust_address_line"
                    placeholder="Start typing your address..."
                    type="text"
                    value={addressLine}
                    onChange={(e) => setAddressLine(e.target.value)}
                    required
                    autoComplete="off"
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    City
                  </label>
                  <input
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm outline-none border"
                    id="cust_city"
                    placeholder="e.g. New Delhi"
                    type="text"
                    value={city}
                    onChange={(e) => setCity(e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    Postal Code
                  </label>
                  <input
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm outline-none border"
                    id="cust_zip"
                    placeholder="e.g. 110003"
                    type="text"
                    value={zip}
                    onChange={(e) => setZip(e.target.value)}
                    required
                  />
                </div>

                {shippingMsg && (
                  <div
                    className={`md:col-span-2 p-4 rounded-xl border text-sm ${
                      shippingMsg.type === 'success'
                        ? 'bg-green-50 border-green-100 text-green-800'
                        : shippingMsg.type === 'error'
                          ? 'bg-red-50 border-red-100 text-red-700'
                          : 'bg-amber-50 border-amber-100 text-amber-800'
                    }`}
                  >
                    {shippingMsg.type === 'loading' ? (
                      <span className="inline-flex items-center gap-2">
                        <span className="material-icons-outlined text-sm animate-spin">sync</span>
                        {shippingMsg.text}
                      </span>
                    ) : (
                      shippingMsg.text
                    )}
                  </div>
                )}
              </div>
            </div>

            <div className="section-card bg-white border border-slate-100 rounded-2xl p-6 md:p-8 mb-6">
              <div className="flex items-center gap-3 mb-6">
                <span className="material-icons-outlined text-primary">calendar_month</span>
                <h2 className="text-xl font-bold">Delivery Schedule</h2>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    Preferred Date
                  </label>
                  <input
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm border outline-none"
                    id="del_date"
                    type="date"
                    min={new Date().toISOString().slice(0, 10)}
                    value={delDate}
                    onChange={(e) => setDelDate(e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
                    Preferred Time Slot
                  </label>
                  <select
                    className="checkout-input w-full bg-white border-slate-200 rounded-lg px-4 py-3 text-sm border outline-none"
                    id="del_time"
                    value={delTime}
                    onChange={(e) => setDelTime(e.target.value)}
                  >
                    <option>Morning (9am - 12pm)</option>
                    <option>Afternoon (12pm - 4pm)</option>
                    <option>Evening (4pm - 8pm)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div className="lg:w-5/12">
            <div className="sticky top-24">
              <div className="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-xl">
                <div className="p-6 md:p-8">
                  <h2 className="text-xl font-bold mb-6">Order Summary</h2>
                  <div className="space-y-6 mb-8">
                    {cart.items.map((item) => (
                      <div key={`${item.category}-${item.id}`} className="flex gap-4">
                        <div className="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0">
                          <img
                            alt={item.name}
                            className="w-full h-full object-cover"
                            src={resolveImageSrc(item.image)}
                          />
                        </div>
                        <div className="flex-1">
                          <h4 className="font-bold text-xs">{item.name}</h4>
                          <div className="flex justify-between mt-1">
                            <span className="text-[10px] text-slate-400">Qty: {item.qty}</span>
                            <span className="font-bold text-primary text-sm">
                              {formatInr(item.price * item.qty)}
                            </span>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>

                  <div className="space-y-3 pt-4 border-t border-slate-100">
                    <div className="flex justify-between text-xs text-slate-500">
                      <span>Subtotal</span>
                      <span>{formatInr(subtotal)}</span>
                    </div>
                    <div className="flex justify-between text-xs text-slate-500">
                      <span>Shipping {shippingReady ? `(${distanceText})` : ''}</span>
                      <span className="font-bold text-primary">
                        {shippingReady ? formatInr(shippingFee) : '—'}
                      </span>
                    </div>
                    {discount > 0 && (
                      <div className="flex justify-between text-xs text-green-600 font-bold">
                        <span>Discount</span>
                        <span>- {formatInr(discount)}</span>
                      </div>
                    )}
                    <div className="flex justify-between pt-3 border-t">
                      <span className="text-base font-bold">Total Payable</span>
                      <span className="text-xl font-bold text-primary">{formatInr(grandTotal)}</span>
                    </div>
                  </div>
                </div>
              </div>

              <button
                type="submit"
                id="placeOrderBtn"
                disabled={submitting}
                className="w-full mt-6 bg-primary text-white font-bold py-4 rounded-xl shadow-lg hover:scale-[1.01] transition-all flex items-center justify-center gap-3 disabled:opacity-60"
              >
                <i className="fab fa-whatsapp text-2xl" />{' '}
                {submitting ? 'Placing order…' : 'Confirm Order'}
              </button>
            </div>
          </div>
        </div>
      </form>
    </main>
  );
}
