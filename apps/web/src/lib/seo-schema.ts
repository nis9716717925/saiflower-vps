import type { Product } from '@/lib/types';

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com';

function absoluteUrl(path: string): string {
  if (path.startsWith('http')) return path;
  return `${SITE_URL}${path.startsWith('/') ? path : `/${path}`}`;
}

export function organizationSchema() {
  return {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'Sai Flower',
    url: SITE_URL,
    logo: absoluteUrl('/uploads/logo_transparent.png'),
    contactPoint: {
      '@type': 'ContactPoint',
      telephone: '+91-8802004527',
      contactType: 'customer service',
      areaServed: 'IN',
      availableLanguage: ['en', 'hi'],
    },
    sameAs: [
      'https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/',
      'https://www.instagram.com/saiflowerofficial',
      'https://x.com/saiflower03',
    ],
  };
}

export function floristSchema() {
  return {
    '@context': 'https://schema.org',
    '@type': 'Florist',
    name: 'Sai Flower',
    image: absoluteUrl('/uploads/logo_transparent.png'),
    '@id': SITE_URL,
    url: SITE_URL,
    telephone: '+91-8802004527',
    priceRange: '₹₹',
    address: {
      '@type': 'PostalAddress',
      streetAddress: 'Shop No. 1, Lodhi Road',
      addressLocality: 'New Delhi',
      postalCode: '110003',
      addressCountry: 'IN',
    },
    geo: {
      '@type': 'GeoCoordinates',
      latitude: 28.5912,
      longitude: 77.227,
    },
    openingHoursSpecification: [
      {
        '@type': 'OpeningHoursSpecification',
        dayOfWeek: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        opens: '09:00',
        closes: '21:00',
      },
    ],
  };
}

export function websiteSchema() {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    url: SITE_URL,
    potentialAction: {
      '@type': 'SearchAction',
      target: {
        '@type': 'EntryPoint',
        urlTemplate: `${SITE_URL}/search-results?q={search_term_string}`,
      },
      'query-input': 'required name=search_term_string',
    },
  };
}

export function productSchemas(product: Product, category: string, pageUrl: string) {
  const images: string[] = [];
  if (product.image) images.push(absoluteUrl(product.image));
  for (const img of product.imagesGallery ?? []) {
    if (img) images.push(absoluteUrl(img));
  }

  const description =
    product.metaDescription?.trim() ||
    product.description?.replace(/<[^>]+>/g, '').slice(0, 160) ||
    product.name;

  const categoryPath =
    category === 'flower' ? 'flowers' : category === 'cake' ? 'cakes' : category === 'gift' ? 'gifts' : `${category}s`;

  const schemas: Record<string, unknown>[] = [
    {
      '@context': 'https://schema.org/',
      '@type': 'Product',
      name: product.name,
      image: images,
      description,
      sku: `${category.slice(0, 3).toUpperCase()}-${product.id}`,
      brand: { '@type': 'Brand', name: 'Sai Flowers' },
      offers: {
        '@type': 'Offer',
        url: absoluteUrl(pageUrl),
        priceCurrency: 'INR',
        price: product.price,
        itemCondition: 'https://schema.org/NewCondition',
        availability:
          product.inStock !== false
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
      },
      ...(product.rating
        ? {
            aggregateRating: {
              '@type': 'AggregateRating',
              ratingValue: product.rating,
              reviewCount: 120,
            },
          }
        : {}),
    },
    {
      '@context': 'https://schema.org',
      '@type': 'BreadcrumbList',
      itemListElement: [
        { '@type': 'ListItem', position: 1, name: 'Home', item: `${SITE_URL}/` },
        { '@type': 'ListItem', position: 2, name: `${category.charAt(0).toUpperCase()}${category.slice(1)}s`, item: `${SITE_URL}/${categoryPath}` },
        { '@type': 'ListItem', position: 3, name: product.name, item: absoluteUrl(pageUrl) },
      ],
    },
  ];

  const faqItems = (() => {
    if (!product.faqs) return [];
    if (typeof product.faqs === 'string') {
      try {
        const parsed = JSON.parse(product.faqs) as Array<{ question?: string; answer?: string }>;
        return Array.isArray(parsed) ? parsed : [];
      } catch {
        return [];
      }
    }
    return [];
  })();

  if (faqItems.length > 0) {
    schemas.push({
      '@context': 'https://schema.org',
      '@type': 'FAQPage',
      mainEntity: faqItems
        .filter((faq) => faq.question && faq.answer)
        .map((faq) => ({
          '@type': 'Question',
          name: faq.question,
          acceptedAnswer: { '@type': 'Answer', text: faq.answer },
        })),
    });
  }

  return schemas;
}
