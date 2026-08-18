import type { Metadata } from 'next';
import { AppProviders } from '@/components/providers/AppProviders';
import { OrderSuccessBanner } from '@/components/home/OrderSuccessBanner';
import { BodyClass } from '@/components/layout/BodyClass';
import { ChunkLoadRecovery } from '@/components/layout/ChunkLoadRecovery';
import { CriticalPaintGuard } from '@/components/layout/CriticalPaintGuard';
import { CriticalRouteStyles } from '@/components/layout/CriticalRouteStyles';
import { NavigationPaintGuard } from '@/components/layout/NavigationPaintGuard';
import { RouteStyles } from '@/components/layout/RouteStyles';
import { ServerBody } from '@/components/layout/ServerBody';
import { SiteFooter } from '@/components/layout/SiteFooter';
import { SiteHeader } from '@/components/layout/SiteHeader';
import { getThemePrimary, themeCssVars } from '@/lib/theme';
import '@/styles/bundled-core';
import '@/styles/bundled-pages';
import '../styles/site-header.css';
import '../styles/not-found-page.css';
import './tailwind.css';
import './globals.css';

export const metadata: Metadata = {
  title: 'Sai Flower | Online Flower & Bouquet Delivery Delhi',
  description:
    'Order fresh flowers and bouquets online from Sai Flower. Same-day flower delivery for birthdays, anniversaries, weddings, and special occasions in Delhi NCR.',
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com'),
  keywords: [
    'flower delivery Delhi',
    'online bouquets',
    'same day delivery',
    'wedding flowers',
    'Sai Flower',
  ],
  icons: {
    icon: '/favicon.png',
    apple: '/favicon.png',
  },
};

export default async function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  const themePrimary = await getThemePrimary();

  return (
    <html lang="en" style={themeCssVars(themePrimary)}>
      <head>
        <CriticalPaintGuard />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link rel="preconnect" href="https://cdnjs.cloudflare.com" />
        <link rel="preconnect" href="https://accounts.google.com" />
        <script src="https://accounts.google.com/gsi/client" async defer />
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
          href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&family=Manrope:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
          rel="stylesheet"
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
      </head>
      <ServerBody>
        {/* saiflower-build: fouc-v4 */}
        <RouteStyles />
        <AppProviders>
          <ChunkLoadRecovery />
          <NavigationPaintGuard />
          <BodyClass />
          <SiteHeader />
          <div id="sf-page">
            <OrderSuccessBanner />
            {children}
            <SiteFooter />
          </div>
        </AppProviders>
      </ServerBody>
    </html>
  );
}
