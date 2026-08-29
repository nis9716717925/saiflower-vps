import type { Metadata } from 'next';

export const SITE_NAME = 'Sai Flower';
export const SITE_NAME_ALT = 'Sai Flowers';

export const DEFAULT_DESCRIPTION =
  'Order fresh flowers and bouquets online from Sai Flower. Same-day flower delivery for birthdays, anniversaries, weddings, and special occasions in Delhi NCR.';

export const DEFAULT_KEYWORDS = [
  'flower delivery Delhi',
  'online bouquets',
  'same day delivery',
  'wedding flowers',
  'Sai Flower',
  'Sai Flowers',
  'Delhi NCR flowers',
  'gift delivery',
];

export const SITE_ICONS: NonNullable<Metadata['icons']> = {
  icon: [
    { url: '/favicon.ico', sizes: 'any' },
    { url: '/icon.png', type: 'image/png', sizes: '32x32' },
    { url: '/favicon.png', type: 'image/png' },
  ],
  apple: [{ url: '/apple-icon.png', sizes: '180x180', type: 'image/png' }],
  shortcut: ['/favicon.ico'],
};

type PageMetaInput = {
  title: string;
  description: string;
  keywords?: string | string[];
  canonical?: string;
  noIndex?: boolean;
};

function mergeKeywords(extra?: string | string[]): string[] {
  const list = [...DEFAULT_KEYWORDS];
  if (!extra) return list;
  const extras = Array.isArray(extra) ? extra : [extra];
  for (const word of extras) {
    const trimmed = word.trim();
    if (trimmed && !list.includes(trimmed)) list.push(trimmed);
  }
  return list;
}

/** Build consistent page metadata with title, description, keywords, and favicon. */
export function pageMetadata(input: PageMetaInput): Metadata {
  const meta: Metadata = {
    title: input.title,
    description: input.description,
    keywords: mergeKeywords(input.keywords),
    icons: SITE_ICONS,
  };

  if (input.canonical) {
    meta.alternates = { canonical: input.canonical };
  }

  if (input.noIndex) {
    meta.robots = { index: false, follow: false };
  }

  return meta;
}

export function productMetadata(
  product: {
    name: string;
    metaTitle?: string | null;
    metaDescription?: string | null;
    description?: string | null;
  },
  category: string,
): Metadata {
  return pageMetadata({
    title: product.metaTitle ?? `${product.name} | Sai Flower`,
    description:
      product.metaDescription ??
      product.description?.slice(0, 160) ??
      `Shop ${product.name} from Sai Flower with same-day delivery in Delhi NCR.`,
    keywords: [product.name, category, 'buy online', 'same day delivery'],
  });
}

export const rootMetadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com'),
  ...pageMetadata({
    title: `${SITE_NAME} | Online Flower & Bouquet Delivery Delhi`,
    description: DEFAULT_DESCRIPTION,
  }),
};
