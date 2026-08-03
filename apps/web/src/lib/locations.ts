import type { LocationInfo } from '@/components/landings/LocationLandingView';

const ENTRIES: Array<[string, string, string, string]> = [
  ['GK 1', 'Greater Kailash Part 1', 'GK 2, Kailash Colony, and Nehru Enclave', 'South Delhi'],
  ['GK 2', 'Greater Kailash Part 2', 'GK 1, Chittaranjan Park, and Alaknanda', 'South Delhi'],
  ['Hauz Khas', 'Hauz Khas', 'Green Park, Safdarjung Enclave, and IIT Delhi', 'South Delhi'],
  ['Green Park', 'Green Park', 'Hauz Khas, Gulmohar Park, and Yusuf Sarai', 'South Delhi'],
  ['Saket', 'Saket', 'Malviya Nagar, Hauz Khas, and Pushp Vihar', 'South Delhi'],
  ['Malviya Nagar', 'Malviya Nagar', 'Saket, Hauz Khas, and Begumpur', 'South Delhi'],
  ['Vasant Kunj', 'Vasant Kunj', 'Munirka, Mahipalpur, and Rangpuri', 'South Delhi'],
  ['Mehrauli', 'Mehrauli', 'Chattarpur, Vasant Kunj, and Qutub Minar area', 'South Delhi'],
  ['Chattarpur', 'Chattarpur', 'Mehrauli, Vasant Kunj, and Chhatarpur Extension', 'South Delhi'],
  ['CR Park', 'Chittaranjan Park', 'Kalkaji, Nehru Place, and GK 2', 'South Delhi'],
  ['Kalkaji', 'Kalkaji', 'Nehru Place, CR Park, and Okhla', 'South Delhi'],
  ['Nehru Place', 'Nehru Place', 'Kalkaji, CR Park, and East of Kailash', 'South Delhi'],
  ['Jor Bagh', 'Jor Bagh', 'Lodhi Road, Safdarjung, and INA Colony', 'South Delhi'],
  ['Lodhi Road', 'Lodhi Road', 'Jor Bagh, Safdarjung, and Khan Market', 'South Delhi'],
  ['Safdarjung', 'Safdarjung', 'AIIMS, Green Park, and Hauz Khas', 'South Delhi'],
  ['AIIMS', 'AIIMS', 'Safdarjung, Green Park, and Ansari Nagar', 'South Delhi'],
  ['Panchsheel', 'Panchsheel Park', 'Chirag Delhi, Sheikh Sarai, and Hauz Khas', 'South Delhi'],
  ['Gulmohar Park', 'Gulmohar Park', 'Green Park, Yusuf Sarai, and Hauz Khas', 'South Delhi'],
  ['SDA', 'Safdarjung Development Area', 'Hauz Khas, Green Park, and IIT Delhi', 'South Delhi'],
  ['Lajpat Nagar', 'Lajpat Nagar', 'Amar Colony, Jungpura, and Kotla Mubarakpur', 'South Delhi'],
  ['Greater Kailash', 'Greater Kailash', 'Kailash Colony, Chittaranjan Park, and East of Kailash', 'South Delhi'],
  ['Connaught Place', 'Connaught Place', 'Barakhamba Road, Janpath, and Mandi House', 'Central Delhi'],
  ['Karol Bagh', 'Karol Bagh', 'Paharganj, Rajendra Place, and Patel Nagar', 'Central Delhi'],
  ['Dwarka', 'Dwarka', 'Janakpuri, Uttam Nagar, and Palam', 'West Delhi'],
  ['Rohini', 'Rohini', 'Pitampura, Burari, and Prashant Vihar', 'North West Delhi'],
  ['Sector 18 Noida', 'Sector 18 Noida', 'Sector 16, Atta Market, and Botanical Garden', 'Noida'],
  ['Sector 62 Noida', 'Sector 62 Noida', 'Sector 60, Fortis Hospital area, and Noida City Centre', 'Noida'],
  ['Gurgaon', 'Gurgaon', 'DLF Phase 1, Cyber City, and Sohna Road', 'Gurgaon'],
  ['Delhi', 'Delhi', 'Gurgaon, Noida, and Ghaziabad', 'Delhi NCR'],
  ['Noida', 'Noida', 'Greater Noida, Sector 18, and Ghaziabad', 'Noida'],
  ['Ghaziabad', 'Ghaziabad', 'Noida, Indirapuram, and Vaishali', 'Ghaziabad'],
  ['Faridabad', 'Faridabad', 'Greater Faridabad, Ballabhgarh, and Delhi border', 'Faridabad'],
  ['Greater Noida', 'Greater Noida', 'Noida, Knowledge Park, and Pari Chowk', 'Greater Noida'],
];

function slugFromArea(area: string): string {
  return `flower-delivery-in-${area
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')}`;
}

export const LOCATION_REGISTRY: Record<string, LocationInfo> = Object.fromEntries(
  ENTRIES.map(([area, local, nearby, region]) => {
    const slug = slugFromArea(area);
    return [slug, { area, local, nearby, region, slug }];
  }),
);

export function locationGet(slug: string): LocationInfo | null {
  return LOCATION_REGISTRY[slug] ?? null;
}

export function isLocationSlug(slug: string): boolean {
  return slug.startsWith('flower-delivery-in-') && Boolean(LOCATION_REGISTRY[slug]);
}
