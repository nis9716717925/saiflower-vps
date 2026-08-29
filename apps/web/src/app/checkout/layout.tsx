import { pageMetadata } from '@/lib/site-metadata';
import '@/styles/bundled-checkout';

export const metadata = pageMetadata({
  title: 'Checkout | Sai Flower',
  description: 'Complete your Sai Flower order with delivery details and secure payment.',
  canonical: '/checkout',
  noIndex: true,
});

export default function CheckoutLayout({ children }: { children: React.ReactNode }) {
  return children;
}
