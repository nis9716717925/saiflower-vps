'use client';

import { usePathname } from 'next/navigation';
import { useEffect } from 'react';

/** Match PHP body classes per route so homepage CSS doesn't bleed into shop/landings. */
export function BodyClass() {
  const pathname = usePathname() || '/';

  useEffect(() => {
    const body = document.body;
    const isHome = pathname === '/';
    const isCollection =
      pathname.startsWith('/collection/') ||
      pathname.startsWith('/occasion/') ||
      pathname.startsWith('/relation/') ||
      pathname.startsWith('/flowers/') ||
      pathname.startsWith('/personalized') ||
      pathname.startsWith('/celebration-calendar') ||
      pathname.startsWith('/flower-delivery-in-');
    const isShopListing =
      pathname === '/flowers' || pathname === '/cakes' || pathname === '/gifts';
    const isCheckoutFunnel =
      pathname === '/cart' ||
      pathname.startsWith('/checkout') ||
      pathname.startsWith('/login') ||
      pathname.startsWith('/register');

    body.classList.toggle('homepage-premium', isHome);
    body.classList.toggle('cl-page-body', isCollection);
    body.classList.toggle('shop-listing-body', isShopListing);
    body.classList.toggle('checkout-funnel-body', isCheckoutFunnel);

    if (isCheckoutFunnel) {
      body.style.backgroundColor = '#f3f5f4';
    } else if (isHome) {
      body.style.backgroundColor = '#fdfcf9';
    } else if (isCollection) {
      body.style.backgroundColor = '#f6f2ea';
    } else if (isShopListing) {
      body.style.backgroundColor = '#f7f4ee';
    } else {
      body.style.backgroundColor = '#fdfcf9';
    }
  }, [pathname]);

  return null;
}
