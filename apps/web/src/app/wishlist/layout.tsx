import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Wishlist | Sai Flower',
  description: 'Save your favourite bouquets, cakes, and gifts from Sai Flower.',
  canonical: '/wishlist',
  noIndex: true,
});

export default function WishlistLayout({ children }: { children: React.ReactNode }) {
  return children;
}
