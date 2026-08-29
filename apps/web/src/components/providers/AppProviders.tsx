'use client';

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';
import { usePathname } from 'next/navigation';
import { apiGet, setGuestId, type CartData } from '@/lib/api';

interface CartContextValue {
  cartCount: number;
  cart: CartData | null;
  guestId: string | null;
  refreshCart: () => Promise<void>;
}

const CartContext = createContext<CartContextValue | null>(null);

function sumQty(items: CartData['items']): number {
  return items.reduce((sum, item) => sum + (item.qty ?? 0), 0);
}

function needsCartImmediately(pathname: string): boolean {
  return (
    pathname === '/cart' ||
    pathname.startsWith('/checkout') ||
    pathname.startsWith('/profile') ||
    pathname.startsWith('/wishlist')
  );
}

export function AppProviders({ children }: { children: ReactNode }) {
  const pathname = usePathname() || '/';
  const [cart, setCart] = useState<CartData | null>(null);
  const [guestId, setGuestIdState] = useState<string | null>(null);

  const refreshCart = useCallback(async () => {
    try {
      const data = await apiGet<CartData>('/cart');
      setCart(data);
      if (data.guestId) {
        setGuestId(data.guestId);
        setGuestIdState(data.guestId);
      }
    } catch {
      setCart({ items: [], count: 0, subtotal: 0, discountAmount: 0, grandTotal: 0 });
    }
  }, []);

  useEffect(() => {
    if (needsCartImmediately(pathname)) {
      void refreshCart();
      return;
    }

    const run = () => void refreshCart();
    if (typeof window.requestIdleCallback === 'function') {
      const id = window.requestIdleCallback(run, { timeout: 4000 });
      return () => window.cancelIdleCallback(id);
    }

    const timer = window.setTimeout(run, 2000);
    return () => window.clearTimeout(timer);
  }, [pathname, refreshCart]);

  const value = useMemo(
    () => ({
      cartCount: cart?.count ?? (cart ? sumQty(cart.items) : 0),
      cart,
      guestId,
      refreshCart,
    }),
    [cart, guestId, refreshCart],
  );

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart(): CartContextValue {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error('useCart must be used within AppProviders');
  return ctx;
}
