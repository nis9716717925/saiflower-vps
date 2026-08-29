import { JsonLd } from '@/components/seo/JsonLd';
import { floristSchema, organizationSchema, websiteSchema } from '@/lib/seo-schema';

export function GlobalSiteSchema() {
  return (
    <>
      <JsonLd data={organizationSchema()} />
      <JsonLd data={floristSchema()} />
      <JsonLd data={websiteSchema()} />
    </>
  );
}
