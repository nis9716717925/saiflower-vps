import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Verify Email | Sai Flower',
  description: 'Confirm your Sai Flower account email address to start ordering.',
  canonical: '/verify',
  noIndex: true,
});

export default function VerifyLayout({ children }: { children: React.ReactNode }) {
  return children;
}
