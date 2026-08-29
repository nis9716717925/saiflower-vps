import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Search Results | Sai Flower',
  description: 'Find flowers, cakes, and gifts across Sai Flower by keyword.',
  keywords: ['search', 'find flowers', 'shop gifts'],
  canonical: '/search-results',
});

export default function SearchResultsLayout({ children }: { children: React.ReactNode }) {
  return children;
}
