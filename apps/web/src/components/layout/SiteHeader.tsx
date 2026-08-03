'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import Script from 'next/script';
import { useCallback, useEffect, useState } from 'react';
import { getCustomer } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { CatNav } from './CatNav';

const NAV_LINKS = [
  { href: '/', label: 'Home', match: ['/', '/index'] },
  { href: '/gallery', label: 'Gallery', match: ['/gallery'] },
  { href: '/events', label: 'Events', match: ['/events'] },
  { href: '/flowers', label: 'Flowers', match: ['/flowers'] },
  { href: '/cakes', label: 'Cakes', match: ['/cakes'] },
  { href: '/gifts', label: 'Gifts', match: ['/gifts'] },
  { href: '/blog', label: 'Blog', match: ['/blog'] },
  { href: '/about', label: 'About', match: ['/about'] },
  { href: '/contact', label: 'Contact', match: ['/contact'] },
];

export function SiteHeader() {
  const pathname = usePathname();
  const { cartCount } = useCart();
  const [menuOpen, setMenuOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [customerName, setCustomerName] = useState<string | null>(null);

  useEffect(() => {
    const c = getCustomer();
    setCustomerName(c?.name ?? null);
  }, [pathname]);

  const closeMenu = useCallback(() => setMenuOpen(false), []);

  useEffect(() => {
    document.body.classList.toggle('sf-mnav-locked', menuOpen);
    return () => document.body.classList.remove('sf-mnav-locked');
  }, [menuOpen]);

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setMenuOpen(false);
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, []);

  const accountHref = customerName ? '/profile' : '/login';

  return (
    <>
      <header className="sf-site-header">
        <nav className="sf-site-header__nav">
          <div className="sf-site-header__brand-row">
            <button
              type="button"
              id="mobileMenuBtn"
              className={`lg:hidden inline-flex items-center justify-center w-12 h-12 rounded-full text-gray-700 hover:text-primary hover:bg-slate-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40${menuOpen ? ' is-open' : ''}`}
              aria-label={menuOpen ? 'Close menu' : 'Open menu'}
              aria-expanded={menuOpen}
              aria-controls="mobileMenu"
              onClick={() => {
                setMenuOpen((v) => !v);
                if (!menuOpen) setSearchOpen(false);
              }}
            >
              <span className="material-icons-outlined text-2xl sf-mnav-icon-menu" aria-hidden="true">
                menu
              </span>
              <span className="material-icons-outlined text-2xl sf-mnav-icon-close" aria-hidden="true">
                close
              </span>
            </button>

            <Link className="sf-site-header__logo" href="/">
              <img
                src="/uploads/logo_transparent.png"
                alt="Sai Flower logo"
                width={152}
                height={44}
                className="sf-site-header__logo-img"
                decoding="async"
              />
            </Link>

            <div className="sf-site-header__desktop-nav">
              {NAV_LINKS.map((link) => (
                <Link key={link.href} href={link.href}>
                  {link.label}
                </Link>
              ))}
            </div>
          </div>

          <div className="sf-site-header__search">
            <div className="sf-site-header__search-inner search-wrapper">
              <form action="/search-results" method="GET" role="search">
                <input
                  name="q"
                  id="desktopSearchInput"
                  autoComplete="off"
                  placeholder="Search flowers, occasions, gifts..."
                  type="search"
                  enterKeyHint="search"
                />
                <button type="submit" className="material-icons-outlined" aria-label="Search">
                  search
                </button>
                <div
                  id="desktopSearchSuggestions"
                  className="search-suggestions absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-slate-100 overflow-hidden z-[10000] max-h-96 overflow-y-auto"
                  style={{ display: 'none' }}
                />
              </form>
            </div>
          </div>

          <div className="sf-site-header__actions">
            <button
              type="button"
              className="sf-site-header__icon-btn sf-site-header__search-toggle"
              aria-label="Open search"
              aria-expanded={searchOpen}
              aria-controls="mobileSearch"
              id="mobileSearchBtn"
              onClick={() => {
                setSearchOpen((v) => !v);
                setMenuOpen(false);
              }}
            >
              <span className="material-icons-outlined text-2xl">search</span>
            </button>

            <Link href="/wishlist" className="sf-site-header__icon-btn" aria-label="Wishlist">
              <span className="material-icons-outlined text-2xl">favorite_border</span>
            </Link>

            <Link href="/cart" className="sf-site-header__icon-btn" aria-label="Cart" style={{ position: 'relative' }}>
              <span className="material-icons-outlined text-2xl">shopping_cart</span>
              {cartCount > 0 && (
                <span className="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold">
                  {cartCount}
                </span>
              )}
            </Link>

            <div className="sf-site-header__account">
              <Link className="sf-site-header__icon-btn" href={accountHref} aria-label="Account">
                <span className="material-icons-outlined">person_outline</span>
              </Link>

              <div className="sf-site-header__account-menu">
                {customerName ? (
                  <>
                    <div className="px-4 py-2 border-b border-slate-50">
                      <p className="text-xs text-slate-500">Signed in as</p>
                      <p className="font-bold text-sm truncate">{customerName}</p>
                    </div>
                    <Link href="/profile">My Profile</Link>
                    <Link href="/profile">My Orders</Link>
                    <Link href="/logout" style={{ color: '#ef4444' }}>
                      Sign Out
                    </Link>
                  </>
                ) : (
                  <>
                    <Link href="/login" style={{ fontWeight: 700 }}>
                      Login
                    </Link>
                    <Link href="/register">Create Account</Link>
                  </>
                )}
              </div>
            </div>
          </div>
        </nav>

        <CatNav />

        <div
          id="mobileMenuBackdrop"
          className={`sf-mnav-backdrop lg:hidden${menuOpen ? ' is-open' : ''}`}
          hidden={!menuOpen}
          onClick={closeMenu}
          aria-hidden={!menuOpen}
        />

        <div
          id="mobileMenu"
          className={`sf-mnav-drawer lg:hidden${menuOpen ? ' is-open' : ''}${menuOpen ? '' : ' hidden'}`}
          role="dialog"
          aria-modal="true"
          aria-label="Site menu"
          hidden={!menuOpen}
        >
          <div className="sf-mnav-drawer__head">
            <Link className="sf-mnav-drawer__brand" href="/" onClick={closeMenu}>
              Sai Flower
            </Link>
            <button
              type="button"
              className="sf-mnav-drawer__close"
              id="mobileMenuClose"
              aria-label="Close menu"
              onClick={closeMenu}
            >
              <span className="material-icons-outlined">close</span>
            </button>
          </div>
          <nav className="sf-mnav-drawer__nav" aria-label="Mobile">
            {NAV_LINKS.map((link) => {
              const isActive = link.match.includes(pathname);
              return (
                <Link
                  key={link.href}
                  className={isActive ? 'is-active' : ''}
                  href={link.href}
                  aria-current={isActive ? 'page' : undefined}
                  onClick={closeMenu}
                >
                  {link.label}
                </Link>
              );
            })}
            <Link href="/legal" onClick={closeMenu}>
              Help & Legal
            </Link>
          </nav>

          {customerName && (
            <div className="sf-mnav-drawer__account">
              <Link className="font-bold text-primary" href="/profile" onClick={closeMenu}>
                My Profile
              </Link>
              <Link className="text-red-500" href="/logout" onClick={closeMenu}>
                Logout
              </Link>
            </div>
          )}
        </div>

        <div
          id="mobileSearch"
          className={`sf-site-header__mobile-search${searchOpen ? ' is-open' : ''}`}
        >
          <div className="relative search-wrapper">
            <form action="/search-results" method="GET" className="sf-site-header__search-inner" role="search">
              <input
                name="q"
                id="mobileSearchInput"
                autoComplete="off"
                enterKeyHint="search"
                inputMode="search"
                placeholder="Search flowers, occasions, gifts..."
                type="search"
                style={{ background: '#fff', borderRadius: '1rem', padding: '0.75rem 3rem 0.75rem 1.25rem' }}
              />
              <button type="submit" className="material-icons-outlined" aria-label="Search">
                search
              </button>
              <div
                id="mobileSearchSuggestions"
                className="search-suggestions absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-slate-100 overflow-hidden z-[10000] max-h-[60vh] overflow-y-auto"
                style={{ display: 'none' }}
              />
            </form>
          </div>
        </div>
      </header>

      <Script src="/assets/js/catnav.js?v=2" strategy="afterInteractive" />
      <Script src="/assets/js/search-suggest.js?v=1" strategy="afterInteractive" />
    </>
  );
}
