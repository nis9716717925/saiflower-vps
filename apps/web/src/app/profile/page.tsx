'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { apiGet, clearAuth, getCustomer } from '@/lib/api';
import { formatInr } from '@/lib/images';
import type { CustomerProfile } from '@/lib/types';

interface OrderRow {
  id: number;
  customer_name: string;
  delivery_address: string;
  delivery_date: string | Date | null;
  order_items: string;
  total_amount: number;
  status: string | null;
  created_at: string | Date | null;
}

export default function ProfilePage() {
  const [customer, setCustomer] = useState<CustomerProfile | null>(null);
  const [orders, setOrders] = useState<OrderRow[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const local = getCustomer();
    if (!local) {
      setLoading(false);
      return;
    }
    void (async () => {
      try {
        const profile = await apiGet<CustomerProfile>('/auth/me');
        setCustomer(profile);
        try {
          const mine = await apiGet<OrderRow[]>('/orders/mine');
          setOrders(mine ?? []);
        } catch {
          setOrders([]);
        }
      } catch {
        setCustomer(local);
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  if (loading) {
    return (
      <main className="container mx-auto px-4 py-16 text-center text-slate-500">
        Loading profile…
      </main>
    );
  }

  if (!customer) {
    return (
      <main className="container mx-auto px-4 py-16 text-center max-w-lg">
        <h1 className="text-2xl font-bold mb-2">My Profile</h1>
        <p className="text-slate-500 mb-8">Please sign in to view your account.</p>
        <Link
          href="/login?redirect=/profile"
          className="inline-block bg-primary text-white font-bold px-8 py-3 rounded-xl"
        >
          Login
        </Link>
      </main>
    );
  }

  return (
    <main className="container mx-auto px-4 py-8 md:py-12 max-w-3xl">
      <h1 className="text-3xl font-bold mb-8">My Profile</h1>
      <div className="bg-white rounded-2xl border border-slate-100 p-6 md:p-8 shadow-sm space-y-4 mb-10">
        <div>
          <p className="text-xs font-bold uppercase tracking-widest text-slate-400">Name</p>
          <p className="font-bold text-lg">{customer.name}</p>
        </div>
        <div>
          <p className="text-xs font-bold uppercase tracking-widest text-slate-400">Email</p>
          <p className="text-slate-700">{customer.email}</p>
        </div>
        {customer.phone && (
          <div>
            <p className="text-xs font-bold uppercase tracking-widest text-slate-400">Phone</p>
            <p className="text-slate-700">{customer.phone}</p>
          </div>
        )}
        {customer.address && (
          <div>
            <p className="text-xs font-bold uppercase tracking-widest text-slate-400">Address</p>
            <p className="text-slate-700">{customer.address}</p>
          </div>
        )}
        <div className="pt-4 flex flex-wrap gap-3">
          <Link href="/flowers" className="bg-primary text-white font-bold px-6 py-2 rounded-xl text-sm">
            Continue Shopping
          </Link>
          <button
            type="button"
            className="border border-slate-200 font-bold px-6 py-2 rounded-xl text-sm text-red-500"
            onClick={() => {
              clearAuth();
              window.location.href = '/';
            }}
          >
            Sign Out
          </button>
        </div>
      </div>

      <section aria-labelledby="orders-title">
        <h2 id="orders-title" className="text-2xl font-bold mb-4">
          My Orders
        </h2>
        {orders.length === 0 ? (
          <p className="text-slate-500 text-sm">No orders found for this account yet.</p>
        ) : (
          <ul className="space-y-4">
            {orders.map((order) => (
              <li
                key={order.id}
                className="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm"
              >
                <div className="flex flex-wrap justify-between gap-2 mb-2">
                  <p className="font-bold">Order #{order.id}</p>
                  <p className="text-primary font-bold">{formatInr(order.total_amount)}</p>
                </div>
                <p className="text-xs text-slate-500 mb-2">
                  Status: {order.status ?? 'pending'}
                  {order.created_at
                    ? ` · ${new Date(order.created_at).toLocaleDateString('en-IN')}`
                    : ''}
                </p>
                <pre className="text-xs text-slate-600 whitespace-pre-wrap font-sans">
                  {order.order_items}
                </pre>
              </li>
            ))}
          </ul>
        )}
      </section>
    </main>
  );
}
