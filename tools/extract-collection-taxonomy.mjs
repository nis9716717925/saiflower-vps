import fs from 'fs';

const php = fs.readFileSync('includes/collection_taxonomy.php', 'utf8');

function defaultFaqs(label) {
  return [
    {
      q: `Can I get same-day delivery for ${label} in Delhi NCR?`,
      a: `Yes. Order before our daily cut-off for same-day ${label} delivery across Delhi, Gurgaon, Noida and nearby NCR areas. WhatsApp us if you need express help.`,
    },
    {
      q: `Are the flowers for ${label} freshly arranged?`,
      a: 'Every bouquet is handcrafted with daily-fresh blooms by our florists. We guarantee freshness and careful packaging for doorstep delivery.',
    },
    {
      q: `What payment methods do you accept for ${label} orders?`,
      a: 'We accept UPI, credit/debit cards, and secure online wallets at checkout. Your payment details are encrypted and never stored on our servers.',
    },
    {
      q: `Can I add a personal message with ${label} gifts?`,
      a: 'Absolutely. Add a free message card at checkout, or mention special instructions on WhatsApp and our team will include them with your order.',
    },
    {
      q: 'What is your return or replacement policy?',
      a: 'If your order arrives damaged or incorrect, contact us within the window in our refund policy. We will arrange a replacement or refund as applicable.',
    },
  ];
}

function parseQuotedList(str) {
  const out = [];
  const re = /'((?:\\'|[^'])*)'|"((?:\\"|[^"])*)"/g;
  let m;
  while ((m = re.exec(str))) {
    out.push((m[1] ?? m[2]).replace(/\\'/g, "'").replace(/\\"/g, '"'));
  }
  return out;
}

function parseFilter(filterSrc) {
  /** @type {Record<string, unknown>} */
  const filter = { tables: ['flowers'], name_keywords: [], tags: [], match: 'any' };
  const tables = filterSrc.match(/'tables'\s*=>\s*\[([^\]]*)\]/);
  if (tables) filter.tables = parseQuotedList(tables[1]);
  const nk = filterSrc.match(/'name_keywords'\s*=>\s*\[([^\]]*)\]/);
  if (nk) filter.name_keywords = parseQuotedList(nk[1]);
  const tags = filterSrc.match(/'tags'\s*=>\s*\[([^\]]*)\]/);
  if (tags) filter.tags = parseQuotedList(tags[1]);
  const pm = filterSrc.match(/'price_min'\s*=>\s*(\d+)/);
  if (pm) filter.price_min = Number(pm[1]);
  const px = filterSrc.match(/'price_max'\s*=>\s*(\d+)/);
  if (px) filter.price_max = Number(px[1]);
  const sort = filterSrc.match(/'sort'\s*=>\s*'([^']+)'/);
  if (sort) filter.sort = sort[1];
  const match = filterSrc.match(/'match'\s*=>\s*'([^']+)'/);
  if (match) filter.match = match[1];
  return filter;
}

function extractCollectionEntries(src) {
  /** @type {Record<string, any>} */
  const out = {};
  const re =
    /'([a-z0-9-]+)'\s*=>\s*collection_entry\(\s*'((?:\\'|[^'])*)'\s*,\s*'((?:\\'|[^'])*)'\s*,\s*'((?:\\'|[^'])*)'\s*,\s*(\[[\s\S]*?\])\s*,\s*(?:\$hero|'((?:\\'|[^'])*)')\s*,\s*(\[[^\]]*\])\s*,\s*collection_default_faqs\('((?:\\'|[^'])*)'\)\s*(?:,\s*'((?:\\'|[^'])*)')?/g;
  let m;
  while ((m = re.exec(src))) {
    out[m[1]] = {
      title: m[2].replace(/\\'/g, "'"),
      h1: m[3].replace(/\\'/g, "'"),
      short_description: m[4].replace(/\\'/g, "'"),
      filter: parseFilter(m[5]),
      hero_image:
        m[6] ||
        'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=1600&q=80',
      related: parseQuotedList(m[7]),
      faqs: defaultFaqs(m[8].replace(/\\'/g, "'")),
      badge: m[9] ? m[9].replace(/\\'/g, "'") : m[2].replace(/\\'/g, "'"),
      cta_label: 'Shop Now',
    };
  }
  return out;
}

function extractRelations(src) {
  const block = src.match(/function collection_taxonomy_relations[\s\S]*?\$defs = \[([\s\S]*?)\];/);
  /** @type {Record<string, any>} */
  const out = {};
  if (!block) return out;
  const re = /'([a-z-]+)'\s*=>\s*\[\s*'([^']+)'\s*,\s*(\[[^\]]+\])\s*,\s*(\[[^\]]+\])\s*\]/g;
  let m;
  while ((m = re.exec(block[1]))) {
    const slug = m[1];
    const label = m[2];
    const names = parseQuotedList(m[3]);
    const tags = parseQuotedList(m[4]);
    out[slug] = {
      title: label,
      h1: `Gifts for ${label} — Flowers, Cakes & Surprises`,
      short_description: `Thoughtful flowers and gifts curated for ${label}. Same-day delivery across Delhi NCR.`,
      filter: { tables: ['flowers', 'cakes', 'gifts'], name_keywords: names, tags, match: 'any' },
      hero_image:
        'https://images.unsplash.com/photo-1487530811176-3780de880c2d?auto=format&fit=crop&w=1600&q=80',
      related: ['occasion:birthday', 'occasion:anniversary', 'collection:best-sellers', 'flower:roses'],
      faqs: defaultFaqs(`gifts for ${label}`),
      badge: `For ${label}`,
      cta_label: 'Shop Now',
    };
  }
  return out;
}

function extractOccasions(src) {
  const block = src.match(
    /function collection_taxonomy_occasions[\s\S]*?\$defs = \[([\s\S]*?)\];\s*\n\s*\$out/,
  );
  /** @type {Record<string, any>} */
  const out = {};
  if (!block) return out;
  const re =
    /'([a-z0-9-]+)'\s*=>\s*\[\s*'((?:\\'|[^'])*)'\s*,\s*'((?:\\'|[^'])*)'\s*,\s*'((?:\\'|[^'])*)'\s*,\s*(\[[\s\S]*?\])\s*,\s*(\[[^\]]*\])\s*,?\s*\]/g;
  let m;
  while ((m = re.exec(block[1]))) {
    const title = m[2].replace(/\\'/g, "'");
    out[m[1]] = {
      title,
      h1: m[3].replace(/\\'/g, "'"),
      short_description: m[4].replace(/\\'/g, "'"),
      filter: { tables: ['flowers'], ...parseFilter(m[5]), match: 'any' },
      hero_image:
        'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=1600&q=80',
      related: parseQuotedList(m[6]),
      faqs: defaultFaqs(title.toLowerCase()),
      badge: title,
      cta_label: 'Shop Now',
    };
  }
  return out;
}

const flowerFn = php.match(/function collection_taxonomy_flowers[\s\S]*?\n\}/);
const collectionFn = php.match(/function collection_taxonomy_collections[\s\S]*?\n\}/);

const taxonomy = {
  flower: extractCollectionEntries(flowerFn?.[0] || ''),
  relation: extractRelations(php),
  occasion: extractOccasions(php),
  collection: extractCollectionEntries(collectionFn?.[0] || ''),
};

const counts = Object.fromEntries(
  Object.entries(taxonomy).map(([k, v]) => [k, Object.keys(v).length]),
);
console.log(counts);

fs.mkdirSync('apps/web/src/lib/data', { recursive: true });
fs.writeFileSync(
  'apps/web/src/lib/data/collection-taxonomy.json',
  JSON.stringify(taxonomy, null, 2),
);
console.log('wrote apps/web/src/lib/data/collection-taxonomy.json');
