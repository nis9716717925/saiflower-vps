'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import Script from 'next/script';
import { useCallback, useEffect, useState } from 'react';
import { getCustomer } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { CatNav } from './CatNav';

const NAV_LINKS = [
  { href: '/', label: 'Home', icon: 'home', match: ['/', '/index'] },
  { href: '/gallery', label: 'Gallery', icon: 'photo_library', match: ['/gallery'] },
  { href: '/events', label: 'Events', icon: 'event', match: ['/events'] },
  { href: '/flowers', label: 'Flowers', icon: 'local_florist', match: ['/flowers'] },
  { href: '/cakes', label: 'Cakes', icon: 'cake', match: ['/cakes'] },
  { href: '/gifts', label: 'Gifts', icon: 'card_giftcard', match: ['/gifts'] },
  { href: '/blog', label: 'Blog', icon: 'article', match: ['/blog'] },
  { href: '/about', label: 'About', icon: 'info', match: ['/about'] },
  { href: '/contact', label: 'Contact', icon: 'call', match: ['/contact'] },
];

const MOBILE_QUICK_LINKS = [
  { href: '/collection/same-day-delivery', label: 'Same Day', icon: 'bolt' },
  { href: '/occasion/birthday', label: 'Birthday', icon: 'cake' },
  { href: '/flowers/roses', label: 'Roses', icon: 'spa' },
  { href: '/collection/flower-combos', label: 'Combos', icon: 'redeem' },
];

const MOBILE_SHOP_LINKS = NAV_LINKS.filter((link) =>
  ['/flowers', '/cakes', '/gifts'].includes(link.href),
);

const MOBILE_EXPLORE_LINKS = NAV_LINKS.filter((link) =>
  ['/', '/gallery', '/events', '/blog'].includes(link.href),
);

const MOBILE_COMPANY_LINKS = NAV_LINKS.filter((link) =>
  ['/about', '/contact'].includes(link.href),
);

export function SiteHeader() {
  const pathname = usePathname();
  const { cartCount } = useCart();
  const [menuOpen, setMenuOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [customerName, setCustomerName] = useState<string | null>(null);
  const [scrolled, setScrolled] = useState(false);

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
    // Threshold matches catnav.js, which toggles the same class on the DOM node.
    const onScroll = () => setScrolled(window.scrollY > 6);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

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
      <header className={`sf-site-header${scrolled ? ' is-scrolled' : ''}`}>
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
                src="/assets/images/logo-transparent.png"
                alt="Sai Flower logo"
                width={168}
                height={43}
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
                <span className="sf-site-header__cart-count" aria-hidden="true">
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
                style={{ borderRadius: '1rem', padding: '0.75rem 3rem 0.75rem 1.25rem' }}
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

      {/* Outside <header>: the glass backdrop-filter would otherwise become the
          containing block for these fixed-position panels. */}
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
          <Link className="sf-mnav-drawer__logo" href="/" onClick={closeMenu}>
            <img
              src="/assets/images/logo-transparent.png"
              alt="Sai Flower"
              width={128}
              height={33}
              decoding="async"
            />
          </Link>
          <div className="sf-mnav-drawer__head-actions">
            <Link
              href={accountHref}
              className="sf-mnav-drawer__account-btn"
              aria-label={customerName ? 'My account' : 'Login'}
              onClick={closeMenu}
            >
              <span className="material-icons-outlined" aria-hidden="true">
                {customerName ? 'account_circle' : 'person_outline'}
              </span>
            </Link>
            <button
              type="button"
              className="sf-mnav-drawer__close"
              id="mobileMenuClose"
              aria-label="Close menu"
              onClick={closeMenu}
            >
              <span className="material-icons-outlined" aria-hidden="true">
                close
              </span>
            </button>
          </div>
        </div>

        <div className="sf-mnav-drawer__scroll">
          <div className="sf-mnav-drawer__hero">
            {customerName ? (
              <>
                <p className="sf-mnav-drawer__hero-kicker">Welcome back</p>
                <p className="sf-mnav-drawer__hero-title">{customerName}</p>
                <p className="sf-mnav-drawer__hero-sub">Track orders, reorder favourites, manage addresses.</p>
                <div className="sf-mnav-drawer__hero-actions">
                  <Link href="/profile" className="sf-mnav-drawer__btn sf-mnav-drawer__btn--primary" onClick={closeMenu}>
                    My orders
                  </Link>
                  <Link href="/profile" className="sf-mnav-drawer__btn sf-mnav-drawer__btn--ghost" onClick={closeMenu}>
                    Profile
                  </Link>
                </div>
              </>
            ) : (
              <>
                <p className="sf-mnav-drawer__hero-kicker">Delhi NCR · Same-day by 6 PM</p>
                <p className="sf-mnav-drawer__hero-title">Gifts delivered in hours</p>
                <p className="sf-mnav-drawer__hero-sub">Login for faster checkout, order history & saved addresses.</p>
                <div className="sf-mnav-drawer__hero-actions">
                  <Link href="/login" className="sf-mnav-drawer__btn sf-mnav-drawer__btn--primary" onClick={closeMenu}>
                    Login
                  </Link>
                  <Link href="/register" className="sf-mnav-drawer__btn sf-mnav-drawer__btn--ghost" onClick={closeMenu}>
                    Sign up
                  </Link>
                </div>
              </>
            )}
          </div>

          <div className="sf-mnav-drawer__quick" aria-label="Quick shop">
            {MOBILE_QUICK_LINKS.map((item) => (
              <Link key={item.href} href={item.href} className="sf-mnav-drawer__quick-item" onClick={closeMenu}>
                <span className="sf-mnav-drawer__quick-icon" aria-hidden="true">
                  <span className="material-icons-outlined">{item.icon}</span>
                </span>
                <span className="sf-mnav-drawer__quick-label">{item.label}</span>
              </Link>
            ))}
          </div>

          <div className="sf-mnav-drawer__section">
            <p className="sf-mnav-drawer__section-label">Shop</p>
            <nav className="sf-mnav-drawer__nav" aria-label="Shop">
              {MOBILE_SHOP_LINKS.map((link) => {
                const isActive = link.match.includes(pathname);
                return (
                  <Link
                    key={link.href}
                    className={isActive ? 'is-active' : ''}
                    href={link.href}
                    aria-current={isActive ? 'page' : undefined}
                    onClick={closeMenu}
                  >
                    <span className="material-icons-outlined sf-mnav-drawer__nav-icon" aria-hidden="true">
                      {link.icon}
                    </span>
                    {link.label}
                    <span className="material-icons-outlined sf-mnav-drawer__nav-chevron" aria-hidden="true">
                      chevron_right
                    </span>
                  </Link>
                );
              })}
            </nav>
          </div>

          <div className="sf-mnav-drawer__section">
            <p className="sf-mnav-drawer__section-label">Explore</p>
            <nav className="sf-mnav-drawer__nav" aria-label="Explore">
              {MOBILE_EXPLORE_LINKS.map((link) => {
                const isActive = link.match.includes(pathname);
                return (
                  <Link
                    key={link.href}
                    className={isActive ? 'is-active' : ''}
                    href={link.href}
                    aria-current={isActive ? 'page' : undefined}
                    onClick={closeMenu}
                  >
                    <span className="material-icons-outlined sf-mnav-drawer__nav-icon" aria-hidden="true">
                      {link.icon}
                    </span>
                    {link.label}
                    <span className="material-icons-outlined sf-mnav-drawer__nav-chevron" aria-hidden="true">
                      chevron_right
                    </span>
                  </Link>
                );
              })}
            </nav>
          </div>

          <div className="sf-mnav-drawer__section">
            <p className="sf-mnav-drawer__section-label">Support</p>
            <nav className="sf-mnav-drawer__nav" aria-label="Support">
              {MOBILE_COMPANY_LINKS.map((link) => {
                const isActive = link.match.includes(pathname);
                return (
                  <Link
                    key={link.href}
                    className={isActive ? 'is-active' : ''}
                    href={link.href}
                    aria-current={isActive ? 'page' : undefined}
                    onClick={closeMenu}
                  >
                    <span className="material-icons-outlined sf-mnav-drawer__nav-icon" aria-hidden="true">
                      {link.icon}
                    </span>
                    {link.label}
                    <span className="material-icons-outlined sf-mnav-drawer__nav-chevron" aria-hidden="true">
                      chevron_right
                    </span>
                  </Link>
                );
              })}
              <Link href="/legal" onClick={closeMenu}>
                <span className="material-icons-outlined sf-mnav-drawer__nav-icon" aria-hidden="true">
                  gavel
                </span>
                Help & Legal
                <span className="material-icons-outlined sf-mnav-drawer__nav-chevron" aria-hidden="true">
                  chevron_right
                </span>
              </Link>
            </nav>
          </div>

          <div className="sf-mnav-drawer__utility">
            <Link href="/cart" className="sf-mnav-drawer__utility-item" onClick={closeMenu}>
              <span className="material-icons-outlined" aria-hidden="true">
                shopping_cart
              </span>
              Cart{cartCount > 0 ? ` (${cartCount})` : ''}
            </Link>
            <Link href="/wishlist" className="sf-mnav-drawer__utility-item" onClick={closeMenu}>
              <span className="material-icons-outlined" aria-hidden="true">
                favorite_border
              </span>
              Wishlist
            </Link>
            <a href="tel:+911145555555" className="sf-mnav-drawer__utility-item">
              <span className="material-icons-outlined" aria-hidden="true">
                call
              </span>
              Call us
            </a>
          </div>

          <p className="sf-mnav-drawer__trust">
            <span>Same-day delivery</span>
            <span>Rated 4.8/5</span>
            <span>Secure checkout</span>
          </p>

          {customerName ? (
            <div className="sf-mnav-drawer__logout-wrap">
              <Link href="/logout" className="sf-mnav-drawer__logout" onClick={closeMenu}>
                Sign out
              </Link>
            </div>
          ) : null}
        </div>
      </div>

      <Script src="/assets/js/catnav.js?v=2" strategy="afterInteractive" />
      <Script src="/assets/js/search-suggest.js?v=1" strategy="afterInteractive" />
    </>
  );
}
