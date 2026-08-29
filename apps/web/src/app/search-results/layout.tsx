import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Search Results | Sai Flower',
  description: 'Find flowers, cakes, and gifts across Sai Flower by keyword.',
  canonical: '/search-results',
});

export default function SearchResultsLayout({ children }: { children: React.ReactNode }) {
  return children;
}
