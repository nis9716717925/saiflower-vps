import { AppProviders } from '@/components/providers/AppProviders';
import { OrderSuccessBanner } from '@/components/home/OrderSuccessBanner';
import { BodyClass } from '@/components/layout/BodyClass';
import { ChunkLoadRecovery } from '@/components/layout/ChunkLoadRecovery';
import { CriticalPaintGuard } from '@/components/layout/CriticalPaintGuard';
import { RouteBodyBoot } from '@/components/layout/RouteBodyBoot';
import { ServiceWorkerRegister } from '@/components/layout/ServiceWorkerRegister';
import { CookieConsent } from '@/components/layout/CookieConsent';
import { SiteFooter } from '@/components/layout/SiteFooter';
import { SiteHeader } from '@/components/layout/SiteHeader';
import { GlobalSiteSchema } from '@/components/seo/GlobalSiteSchema';
import { rootMetadata } from '@/lib/site-metadata';
import { siteFontClassName } from '@/lib/fonts';
import { getThemePrimary, themeCssVars } from '@/lib/theme';
import '@/styles/bundled-core';
import '../styles/site-header.css';
import '../styles/not-found-page.css';
import './tailwind.css';
import './globals.css';

export const metadata = rootMetadata;

export default async function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  const themePrimary = await getThemePrimary();

  return (
    <html lang="en" className={siteFontClassName} style={themeCssVars(themePrimary)}>
      <head>
        <CriticalPaintGuard />
        <link
          rel="preload"
          href="/assets/vendor/fontawesome/webfonts/fa-solid-900.woff2"
          as="font"
          type="font/woff2"
          crossOrigin=""
        />
        <link
          rel="preload"
          href="/assets/vendor/fontawesome/webfonts/fa-brands-400.woff2"
          as="font"
          type="font/woff2"
          crossOrigin=""
        />
        <link
          rel="preload"
          href="/assets/vendor/fontawesome/webfonts/fa-regular-400.woff2"
          as="font"
          type="font/woff2"
          crossOrigin=""
        />
        <link
          rel="preload"
          href="/assets/vendor/material-icons/material-icons-outlined.woff2"
          as="font"
          type="font/woff2"
          crossOrigin=""
        />
        <link rel="stylesheet" href="/assets/vendor/fontawesome/css/fontawesome.min.css" />
        <link rel="stylesheet" href="/assets/vendor/fontawesome/css/solid.min.css" />
        <link rel="stylesheet" href="/assets/vendor/fontawesome/css/brands.min.css" />
        <link rel="stylesheet" href="/assets/vendor/fontawesome/css/regular.min.css" />
        <link rel="stylesheet" href="/assets/vendor/material-icons-outlined.css" />
        <GlobalSiteSchema />
      </head>
      <body className="text-gray-800">
        <RouteBodyBoot />
        <AppProviders>
          <ChunkLoadRecovery />
          <BodyClass />
          <ServiceWorkerRegister />
          <SiteHeader />
          <div id="sf-page">
            <OrderSuccessBanner />
            {children}
            <SiteFooter />
          </div>
          <CookieConsent />
        </AppProviders>
      </body>
    </html>
  );
}
