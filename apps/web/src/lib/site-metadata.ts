import type { Metadata } from 'next';

export const SITE_NAME = 'Sai Flower';
export const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com';

export const DEFAULT_DESCRIPTION =
  'Order fresh flowers and bouquets online from Sai Flower. Same-day flower delivery for birthdays, anniversaries, weddings, and special occasions in Delhi NCR.';

/** Matches legacy PHP homepage / default keywords (max 5). */
export const DEFAULT_KEYWORDS =
  'flower delivery Delhi, online bouquets, same day delivery, wedding flowers, Sai Flower';

export const SITE_ICONS: NonNullable<Metadata['icons']> = {
  icon: [{ url: '/favicon.png', type: 'image/png' }],
  apple: [{ url: '/favicon.png', type: 'image/png' }],
};

/** Authoritative SEO overrides from legacy includes/seo_helper.php */
export const PAGE_SEO_OVERRIDES: Record<string, { title: string; description: string; keywords: string }> = {
  '/flowers': {
    title: 'Fresh Flowers & Bouquets Online | Sai Flower Delhi',
    description:
      'Order fresh flower bouquets online from Sai Flower. Same-day delivery in Delhi. Roses, orchids, wedding & event flowers.',
    keywords: 'fresh flowers Delhi, flower delivery, bouquets online, same day delivery, Sai Flower',
  },
  '/contact': {
    title: 'Contact Sai Flower | Flower Delivery Delhi | +91 88020 04527',
    description:
      'Get in touch with Sai Flower for flower delivery in Delhi NCR. Call +91 88020 04527, WhatsApp us, or visit our Lodhi Road shop.',
    keywords: 'contact Sai Flower, flower delivery Delhi, florist phone number',
  },
};

type PageMetaInput = {
  title: string;
  description: string;
  keywords?: string;
  canonical?: string;
  noIndex?: boolean;
  image?: string;
};

function limitMetaKeywords(keywords: string, max = 5): string {
  const parts = keywords
    .split(',')
    .map((part) => part.trim())
    .filter(Boolean);
  if (parts.length <= max) return parts.join(', ');
  return parts.slice(0, max).join(', ');
}

function withSocialMeta(input: PageMetaInput): Metadata {
  const keywords = limitMetaKeywords(input.keywords ?? DEFAULT_KEYWORDS);
  const canonical = input.canonical ? absoluteCanonical(input.canonical) : undefined;
  const image = input.image ? absoluteCanonical(input.image) : absoluteCanonical('/uploads/logo_transparent.png');

  const meta: Metadata = {
    title: input.title,
    description: input.description,
    keywords,
    authors: [{ name: SITE_NAME }],
    publisher: SITE_NAME,
    icons: SITE_ICONS,
    openGraph: {
      type: 'website',
      siteName: SITE_NAME,
      title: input.title,
      description: input.description,
      url: canonical,
      images: [{ url: image, alt: input.title }],
      locale: 'en_IN',
    },
    twitter: {
      card: 'summary_large_image',
      title: input.title,
      description: input.description,
      images: [image],
    },
    verification: {
      google: ['eB9VORqGBu2riVGwdtWi5Ycg4aQyGLOlVnl1Elc7_sI', '_3OJRaDzm_rnfg5OqKQWnN6jxKp0bhQh6KkPVcU8Cio'],
    },
  };

  if (canonical) {
    meta.alternates = {
      canonical,
      languages: {
        'en-in': canonical,
        'x-default': canonical,
      },
    };
  }

  if (input.noIndex) {
    meta.robots = { index: false, follow: false };
  } else {
    meta.robots = {
      index: true,
      follow: true,
      googleBot: {
        index: true,
        follow: true,
        'max-image-preview': 'large',
        'max-snippet': -1,
        'max-video-preview': -1,
      },
    };
  }

  return meta;
}

function absoluteCanonical(path: string): string {
  if (path.startsWith('http')) return path;
  return `${SITE_URL}${path.startsWith('/') ? path : `/${path}`}`;
}

/** Build page metadata matching legacy PHP seo_helper.php output. */
export function pageMetadata(input: PageMetaInput): Metadata {
  return withSocialMeta(input);
}

export function pageMetadataForPath(path: string, fallback: Omit<PageMetaInput, 'keywords'>): Metadata {
  const override = PAGE_SEO_OVERRIDES[path];
  if (override) {
    return pageMetadata({ ...override, canonical: path });
  }
  return pageMetadata({ ...fallback, canonical: path });
}

export function productMetadata(
  product: {
    name: string;
    metaTitle?: string | null;
    metaDescription?: string | null;
    description?: string | null;
  },
  pageUrl: string,
): Metadata {
  const title = product.metaTitle?.trim() || `${product.name} | Sai Flower`;
  const description =
    product.metaDescription?.trim() ||
    product.description?.replace(/<[^>]+>/g, '').slice(0, 160) ||
    `Shop ${product.name} from Sai Flower with same-day delivery in Delhi NCR.`;

  return pageMetadata({
    title,
    description,
    keywords: limitMetaKeywords(`${product.name}, Sai Flower, flower delivery Delhi`),
    canonical: pageUrl,
    image: undefined,
  });
}

export const rootMetadata: Metadata = {
  metadataBase: new URL(SITE_URL),
  ...pageMetadata({
    title: `${SITE_NAME} | Online Flower & Bouquet Delivery Delhi`,
    description: DEFAULT_DESCRIPTION,
    canonical: '/',
  }),
};
