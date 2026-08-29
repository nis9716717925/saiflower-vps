import { pageMetadata } from '@/lib/site-metadata';
import '@/styles/bundled-checkout';

export const metadata = pageMetadata({
  title: 'Shopping Cart | Sai Flower',
  description: 'Review bouquets, cakes, and gifts in your Sai Flower cart before checkout.',
  canonical: '/cart',
  noIndex: true,
});

export default function CartLayout({ children }: { children: React.ReactNode }) {
  return children;
}
