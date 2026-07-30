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
      <header className="sticky top-0 z-50 bg-white border-b border-slate-100 shadow-sm sf-site-header">
        <nav className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
          <div className="flex items-center gap-2 sm:gap-3">
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

            <Link className="flex items-center gap-2" href="/">
              <img
                src="/uploads/logo_transparent.png"
                alt="Sai Flower logo"
                width={180}
                height={64}
                className="h-10 sm:h-11 w-auto object-contain"
              />
            </Link>

            <div className="hidden xl:flex items-center gap-7 text-sm font-semibold text-slate-700">
              {NAV_LINKS.map((link) => (
                <Link key={link.href} className="hover:text-primary transition-colors" href={link.href}>
                  {link.label}
                </Link>
              ))}
            </div>
          </div>

          <div className="hidden md:flex flex-1 justify-center">
            <div className="relative w-full max-w-lg search-wrapper">
              <form action="/search-results" method="GET" className="relative" role="search">
                <input
                  name="q"
                  id="desktopSearchInput"
                  autoComplete="off"
                  className="w-full bg-slate-50 border border-slate-200 rounded-full pl-5 pr-12 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                  placeholder="Search flowers, occasions, gifts..."
                  type="search"
                  enterKeyHint="search"
                />
                <button
                  type="submit"
                  className="material-icons-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg bg-transparent border-none cursor-pointer"
                  aria-label="Search"
                >
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

          <div className="flex items-center gap-2 sm:gap-3">
            <button
              type="button"
              className="md:hidden inline-flex items-center justify-center w-12 h-12 rounded-full hover:text-primary hover:bg-slate-50 transition-colors text-gray-700"
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

            <Link
              href="/wishlist"
              className="inline-flex items-center justify-center w-11 h-11 sm:w-10 sm:h-10 rounded-full border border-slate-200 hover:bg-slate-50 hover:text-primary transition-colors text-gray-700"
              aria-label="Wishlist"
            >
              <span className="material-icons-outlined text-2xl">favorite_border</span>
            </Link>

            <Link
              href="/cart"
              className="relative inline-flex items-center justify-center w-11 h-11 sm:w-10 sm:h-10 rounded-full border border-slate-200 hover:bg-slate-50 hover:text-primary transition-colors text-gray-700"
              aria-label="Cart"
            >
              <span className="material-icons-outlined text-2xl">shopping_cart</span>
              {cartCount > 0 && (
                <span className="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold">
                  {cartCount}
                </span>
              )}
            </Link>

            <div className="relative group">
              <Link
                className="w-10 h-10 rounded-full border border-slate-200 hover:bg-slate-50 flex items-center justify-center transition-colors text-gray-700"
                href={accountHref}
                aria-label="Account"
              >
                <span className="material-icons-outlined md:text-xl">person_outline</span>
              </Link>

              <div className="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-2 hidden group-hover:block group-focus-within:block z-50">
                {customerName ? (
                  <>
                    <div className="px-4 py-2 border-b border-slate-50">
                      <p className="text-xs text-slate-500">Signed in as</p>
                      <p className="font-bold text-sm truncate">{customerName}</p>
                    </div>
                    <Link href="/profile" className="block px-4 py-2 text-sm hover:bg-slate-50 font-semibold text-primary">
                      My Profile
                    </Link>
                    <Link href="/profile" className="block px-4 py-2 text-sm hover:bg-slate-50">
                      My Orders
                    </Link>
                    <button
                      type="button"
                      className="block w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50"
                      onClick={() => {
                        localStorage.removeItem('saiflower_access_token');
                        localStorage.removeItem('saiflower_refresh_token');
                        localStorage.removeItem('saiflower_customer');
                        window.location.href = '/';
                      }}
                    >
                      Sign Out
                    </button>
                  </>
                ) : (
                  <>
                    <Link href="/login" className="block px-4 py-2 text-sm hover:bg-slate-50 font-bold">
                      Login
                    </Link>
                    <Link href="/register" className="block px-4 py-2 text-sm hover:bg-slate-50">
                      Create Account
                    </Link>
                    <div className="border-t border-slate-50 my-1" />
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
              <button
                type="button"
                className="text-red-500"
                onClick={() => {
                  localStorage.removeItem('saiflower_access_token');
                  localStorage.removeItem('saiflower_refresh_token');
                  localStorage.removeItem('saiflower_customer');
                  closeMenu();
                  window.location.href = '/';
                }}
              >
                Logout
              </button>
            </div>
          )}
        </div>

        <div
          id="mobileSearch"
          className={`md:hidden bg-slate-50 border-t border-slate-100 px-4 pb-4 shadow-sm relative z-50${searchOpen ? '' : ' hidden'}`}
        >
          <div className="pt-4 relative search-wrapper">
            <form action="/search-results" method="GET" className="relative" role="search">
              <input
                name="q"
                id="mobileSearchInput"
                autoComplete="off"
                enterKeyHint="search"
                inputMode="search"
                className="w-full bg-white border border-slate-200 rounded-2xl pl-5 pr-12 py-3 text-base focus:ring-2 focus:ring-primary/40 outline-none"
                placeholder="Search flowers, occasions, gifts..."
                type="search"
              />
              <button
                type="submit"
                className="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 w-11 h-11 inline-flex items-center justify-center"
                aria-label="Search"
              >
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
