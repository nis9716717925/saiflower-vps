import { JsonLd } from '@/components/seo/JsonLd';
import { productSchemas } from '@/lib/seo-schema';
import type { Product } from '@/lib/types';

export function ProductJsonLd({
  product,
  category,
  pageUrl,
}: {
  product: Product;
  category: string;
  pageUrl: string;
}) {
  return <JsonLd data={productSchemas(product, category, pageUrl)} />;
}
