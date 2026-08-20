export interface CelebrationItem {
  date: string;
  title: string;
  image: string;
  slug: string;
}

export const CELEBRATION_ITEMS: CelebrationItem[] = [
  { date: '1ST JAN', title: "New Year's Day", image: '/celebrations/new-year.webp', slug: 'new-years-day' },
  { date: '7TH FEB', title: 'Rose Day', image: '/celebrations/rose-day.webp', slug: 'rose-day' },
  { date: '8TH FEB', title: 'Propose Day', image: '/celebrations/propose-day.webp', slug: 'propose-day' },
  { date: '9TH FEB', title: 'Chocolate Day', image: '/celebrations/chocolate-day.webp', slug: 'chocolate-day' },
  { date: '10TH FEB', title: 'Teddy Day', image: '/celebrations/teddy-day.webp', slug: 'teddy-day' },
  { date: '11TH FEB', title: 'Promise Day', image: '/celebrations/promise-day.webp', slug: 'promise-day' },
  { date: '12TH FEB', title: 'Hug Day', image: '/celebrations/hug-day.webp', slug: 'hug-day' },
  { date: '13TH FEB', title: 'Kiss Day', image: '/celebrations/kiss-day.webp', slug: 'kiss-day' },
  { date: '14TH FEB', title: "Valentine's Day", image: '/celebrations/valentines-day.webp', slug: 'valentines-day' },
  { date: '8TH MAR', title: "Women's Day", image: '/celebrations/womens-day.webp', slug: 'womens-day' },
  { date: '10TH MAY', title: "Mother's Day", image: '/celebrations/mothers-day.webp', slug: 'mothers-day' },
  { date: '21ST JUN', title: "Father's Day", image: '/celebrations/fathers-day.webp', slug: 'fathers-day' },
  { date: '1ST JUL', title: "Doctor's Day", image: '/celebrations/doctors-day.webp', slug: 'doctors-day' },
  { date: '2ND AUG', title: 'Friendship Day', image: '/celebrations/friendship-day.webp', slug: 'friendship-day' },
  { date: '28TH AUG', title: 'Raksha Bandhan', image: '/celebrations/raksha-bandhan.webp', slug: 'raksha-bandhan' },
  { date: '5TH SEP', title: "Teacher's Day", image: '/celebrations/teachers-day.webp', slug: 'teachers-day' },
  { date: '13TH SEP', title: 'Grandparents Day', image: '/celebrations/grandparents-day.webp', slug: 'grandparents-day' },
  { date: '14TH SEP', title: 'Janmashtami', image: '/celebrations/janmashtami.webp', slug: 'janmashtami' },
  { date: '21ST SEP', title: 'Wife Appreciation Day', image: '/celebrations/wife-appreciation-day.webp', slug: 'wife-appreciation-day' },
  { date: '29TH OCT', title: 'Karwa Chauth', image: '/celebrations/karwa-chauth.webp', slug: 'karwa-chauth' },
  { date: '5TH NOV', title: 'Dhanteras', image: '/celebrations/dhanteras.webp', slug: 'dhanteras' },
  { date: '12TH NOV', title: 'Diwali', image: '/celebrations/diwali.webp', slug: 'diwali' },
  { date: '14TH NOV', title: "Children's Day", image: '/celebrations/childrens-day.webp', slug: 'childrens-day' },
  { date: '15TH NOV', title: 'Bhai Dooj', image: '/celebrations/bhai-dooj.webp', slug: 'bhai-dooj' },
  { date: '19TH NOV', title: "International Men's Day", image: '/celebrations/mens-day.webp', slug: 'mens-day' },
  { date: '25TH DEC', title: 'Christmas', image: '/celebrations/christmas.webp', slug: 'christmas' },
];

const MONTHS: Record<string, [string, number]> = {
  JAN: ['January', 1],
  FEB: ['February', 2],
  MAR: ['March', 3],
  APR: ['April', 4],
  MAY: ['May', 5],
  JUN: ['June', 6],
  JUL: ['July', 7],
  AUG: ['August', 8],
  SEP: ['September', 9],
  OCT: ['October', 10],
  NOV: ['November', 11],
  DEC: ['December', 12],
};

const OCCASION_SLUG_MAP: Record<string, string> = {
  'valentines-day': '/occasion/valentines-day',
  'mothers-day': '/occasion/mothers-day',
  'fathers-day': '/occasion/fathers-day',
  'womens-day': '/occasion/festivals',
  'new-years-day': '/occasion/festivals',
  'raksha-bandhan': '/relation/brother',
  diwali: '/occasion/festivals',
  christmas: '/occasion/festivals',
  'karwa-chauth': '/relation/wife',
  'friendship-day': '/relation/friends',
  'teachers-day': '/occasion/thank-you',
  'childrens-day': '/relation/kids',
  'bhai-dooj': '/relation/brother',
  'mens-day': '/relation/him',
  'rose-day': '/flowers/roses',
  'propose-day': '/occasion/love-romance',
  'chocolate-day': '/gifts',
  'teddy-day': '/gifts',
  'promise-day': '/occasion/love-romance',
  'hug-day': '/occasion/love-romance',
  'kiss-day': '/occasion/love-romance',
  janmashtami: '/occasion/festivals',
  dhanteras: '/occasion/festivals',
  'doctors-day': '/occasion/thank-you',
  'grandparents-day': '/relation/grandmother',
  'wife-appreciation-day': '/relation/wife',
};

export function celebrationHref(item: CelebrationItem): string {
  return OCCASION_SLUG_MAP[item.slug] ?? `/search-results?q=${encodeURIComponent(item.title)}`;
}

export function celebrationsGroupByMonth(items: CelebrationItem[]) {
  const groups: Record<string, { month: string; sort: number; items: CelebrationItem[] }> = {};
  for (const item of items) {
    const m = item.date.match(/\b([A-Z]{3})\b/i);
    const abbr = m ? m[1].toUpperCase() : '';
    const meta = MONTHS[abbr] ?? ['Year-round', 99];
    const key = meta[0];
    if (!groups[key]) groups[key] = { month: key, sort: meta[1], items: [] };
    groups[key].items.push(item);
  }
  return Object.values(groups).sort((a, b) => a.sort - b.sort);
}

export function celebrationsUpcoming(items: CelebrationItem[], limit = 6): CelebrationItem[] {
  // Approximate: take first N from current month onward based on today's month
  const now = new Date();
  const month = now.getMonth() + 1;
  const grouped = celebrationsGroupByMonth(items);
  const rotated = [
    ...grouped.filter((g) => g.sort >= month),
    ...grouped.filter((g) => g.sort < month),
  ];
  const out: CelebrationItem[] = [];
  for (const g of rotated) {
    for (const item of g.items) {
      out.push(item);
      if (out.length >= limit) return out;
    }
  }
  return out;
}
