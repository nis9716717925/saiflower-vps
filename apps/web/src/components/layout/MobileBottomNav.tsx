'use client';

import Link from 'next/link';
import { useEffect, useRef, useState } from 'react';
import { SiteIcon } from '@/components/icons/SiteIcon';

const WA_HREF = 'https://wa.me/918802004527';
const TEL_HREF = 'tel:918802004527';

/**
 * Mobile bottom dock — hides on scroll-down, returns on scroll-up
 * (Blinkit / Instagram / modern ecommerce pattern).
 */
export function MobileBottomNav() {
  const [hidden, setHidden] = useState(false);
  const lastY = useRef(0);
  const ticking = useRef(false);

  useEffect(() => {
    lastY.current = window.scrollY || 0;

    const onScroll = () => {
      if (ticking.current) return;
      ticking.current = true;
      window.requestAnimationFrame(() => {
        const y = window.scrollY || 0;
        const delta = y - lastY.current;
        const nearTop = y < 24;
        const atBottom =
          window.innerHeight + y >= document.documentElement.scrollHeight - 48;

        if (nearTop || atBottom || delta < -6) {
          setHidden(false);
        } else if (delta > 8 && y > 64) {
          setHidden(true);
        }

        lastY.current = y;
        ticking.current = false;
      });
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  return (
    <>
      <nav
        id="mobileBottomNav"
        className={`sf-bottom-nav md:hidden${hidden ? ' is-hidden' : ''}`}
        aria-label="Mobile quick navigation"
      >
        <div className="sf-bottom-nav__inner">
          <Link href="/" className="sf-bottom-nav__item">
            <SiteIcon name="home" size={22} />
            <span>Home</span>
          </Link>
          <Link href="/flowers" className="sf-bottom-nav__item">
            <SiteIcon name="shopping_bag" size={22} />
            <span>Shop</span>
          </Link>
          <a
            href={WA_HREF}
            target="_blank"
            rel="noopener noreferrer"
            className="sf-bottom-nav__wa"
            aria-label="WhatsApp Sai Flowers"
          >
            <span className="sf-bottom-nav__wa-btn sf-wa-pulse">
              <i className="fab fa-whatsapp" aria-hidden="true" />
            </span>
            <span>WhatsApp</span>
          </a>
          <a href={TEL_HREF} className="sf-bottom-nav__item">
            <SiteIcon name="call" size={22} />
            <span>Call</span>
          </a>
          <Link href="/contact" className="sf-bottom-nav__item">
            <SiteIcon name="location_on" size={22} />
            <span>Visit</span>
          </Link>
        </div>
      </nav>
      <div id="mobileBottomNavSpacer" className="md:hidden h-20" aria-hidden="true" />
    </>
  );
}
