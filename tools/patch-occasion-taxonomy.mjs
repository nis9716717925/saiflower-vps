import fs from 'fs';

const path = 'apps/web/src/lib/data/collection-taxonomy.json';
const t = JSON.parse(fs.readFileSync(path, 'utf8'));

function faqs(label) {
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

const hero =
  'https://images.unsplash.com/photo-1525310072745-f49212b5ac6d?auto=format&fit=crop&w=1600&q=80';

t.occasion['mothers-day'] = {
  title: "Mother's Day",
  h1: "Mother's Day Flowers & Gifts Online",
  short_description: 'Celebrate Mum with carnations, roses and premium gift combos.',
  filter: {
    tables: ['flowers', 'cakes', 'gifts'],
    name_keywords: ['mother', 'mom', "mother's day", 'mothers day'],
    tags: ['mother', 'mothers-day', 'mom'],
    match: 'any',
  },
  hero_image: hero,
  related: ['relation:mother', 'flower:carnations', 'flower:roses', 'collection:best-sellers'],
  faqs: faqs("Mother's Day"),
  badge: "Mother's Day",
  cta_label: 'Shop Now',
};

t.occasion['fathers-day'] = {
  title: "Father's Day",
  h1: "Father's Day Flowers & Gifts Online",
  short_description: 'Surprise Dad with fresh blooms, cakes and thoughtful hampers.',
  filter: {
    tables: ['flowers', 'cakes', 'gifts'],
    name_keywords: ['father', 'dad', "father's day", 'fathers day'],
    tags: ['father', 'fathers-day', 'dad'],
    match: 'any',
  },
  hero_image: hero,
  related: [
    'relation:father',
    'collection:flower-combos',
    'flower:mixed-flowers',
    'collection:best-sellers',
  ],
  faqs: faqs("Father's Day"),
  badge: "Father's Day",
  cta_label: 'Shop Now',
};

t.occasion['valentines-day'] = {
  title: "Valentine's Day",
  h1: "Valentine's Day Roses & Romantic Gifts",
  short_description: 'Red roses, premium LUXE bouquets and midnight-ready romance.',
  filter: {
    tables: ['flowers', 'cakes', 'gifts'],
    name_keywords: ['valentine', 'valentines'],
    tags: ['valentine', 'valentines', 'love'],
    match: 'any',
  },
  hero_image: hero,
  related: [
    'flower:roses',
    'occasion:love-romance',
    'relation:girlfriend',
    'collection:luxury-flowers',
  ],
  faqs: faqs("Valentine's Day"),
  badge: "Valentine's Day",
  cta_label: 'Shop Now',
};

t.occasion.love = structuredClone(t.occasion['love-romance']);

fs.writeFileSync(path, JSON.stringify(t, null, 2));
console.log('occasions', Object.keys(t.occasion).length);
