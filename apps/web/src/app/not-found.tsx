import { NotFoundView } from '@/components/pages/NotFoundView';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Page Not Found | Sai Flower',
  description:
    'The page you are looking for could not be found. Browse fresh flowers, cakes, and gifts with same-day delivery from Sai Flower.',
  keywords: ['404', 'page not found'],
  noIndex: true,
});

export default function NotFound() {
  return <NotFoundView />;
}
