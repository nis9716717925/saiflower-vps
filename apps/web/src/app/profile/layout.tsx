import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'My Profile | Sai Flower',
  description: 'View your Sai Flower account details, saved addresses, and order history.',
  keywords: ['my account', 'order history', 'profile'],
  canonical: '/profile',
  noIndex: true,
});

export default function ProfileLayout({ children }: { children: React.ReactNode }) {
  return children;
}
