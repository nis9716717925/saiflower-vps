import { AppProviders } from '@/components/providers/AppProviders';
import { OrderSuccessBanner } from '@/components/home/OrderSuccessBanner';
import { BodyClass } from '@/components/layout/BodyClass';
import { ChunkLoadRecovery } from '@/components/layout/ChunkLoadRecovery';
import { CriticalPaintGuard } from '@/components/layout/CriticalPaintGuard';
import { CriticalRouteStyles } from '@/components/layout/CriticalRouteStyles';
import { NavigationPaintGuard } from '@/components/layout/NavigationPaintGuard';
import { RouteStyles } from '@/components/layout/RouteStyles';
import { ServerBody } from '@/components/layout/ServerBody';
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
        <link rel="preconnect" href="https://cdnjs.cloudflare.com" />
        <link
          rel="preload"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/fa-solid-900.woff2"
          as="font"
          type="font/woff2"
          crossOrigin=""
        />
        <link
          rel="preload"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/fa-brands-400.woff2"
          as="font"
          type="font/woff2"
          crossOrigin=""
        />
        <link
          rel="preload"
          href="https://fonts.gstatic.com/s/materialiconsoutlined/v109/gok-H7zzDkdnRel8-DQ6KAXJFi-w.woff2"
          as="font"
          type="font/woff2"
          crossOrigin=""
        />
        <link
          href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined&display=swap"
          rel="stylesheet"
        />
        <link
          rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        />
        <CriticalRouteStyles />
        <GlobalSiteSchema />
      </head>
      <ServerBody>
        {/* saiflower-build: fouc-v4 */}
        <RouteStyles />
        <AppProviders>
          <ChunkLoadRecovery />
          <NavigationPaintGuard />
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
      </ServerBody>
    </html>
  );
}
