import type { Metadata } from 'next';
import { AppProviders } from '@/components/providers/AppProviders';
import { OrderSuccessBanner } from '@/components/home/OrderSuccessBanner';
import { SiteFooter } from '@/components/layout/SiteFooter';
import { SiteHeader } from '@/components/layout/SiteHeader';
import { TailwindBoot } from '@/components/layout/TailwindBoot';
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

const GLOBAL_CSS = [
  '/assets/css/style.css',
  '/assets/css/homepage-luxe.css',
  '/assets/css/homepage-firstview.css',
  '/assets/css/homepage-premium.css',
  '/assets/css/homepage-mobile.css',
  '/assets/css/shop-luxe.css',
  '/assets/css/product-detail-premium.css',
  '/assets/css/catnav.css',
  '/assets/css/mobile-nav.css',
  '/assets/css/search-suggest.css',
  '/assets/css/celebrations-calendar.css',
];

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link rel="preconnect" href="https://cdnjs.cloudflare.com" />
        <link
          href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
          rel="stylesheet"
        />
        <link
          href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined&display=swap"
          rel="stylesheet"
        />
        <link
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
          rel="stylesheet"
        />
        <link
          rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        />
        {GLOBAL_CSS.map((href) => (
          <link key={href} rel="stylesheet" href={href} />
        ))}
      </head>
      <body className="bg-white text-gray-800 homepage-premium">
        <TailwindBoot />
        <AppProviders>
          <SiteHeader />
          <OrderSuccessBanner />
          {children}
          <SiteFooter />
        </AppProviders>
      </body>
    </html>
  );
}
