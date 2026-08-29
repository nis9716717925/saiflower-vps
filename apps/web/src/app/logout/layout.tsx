import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Sign Out | Sai Flower',
  description: 'Signing you out of your Sai Flower account.',
  canonical: '/logout',
  noIndex: true,
});

export default function LogoutLayout({ children }: { children: React.ReactNode }) {
  return children;
}
