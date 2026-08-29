import { apiUrl } from './api';

export type HomepageSlide = {
  id: number;
  image: string;
  mobileImage: string | null;
  link: string | null;
  sortOrder: number;
};

type SlidesResponse = {
  success?: boolean;
  data?: HomepageSlide[];
};

export async function loadHomepageSlides(): Promise<HomepageSlide[]> {
  try {
    const res = await fetch(apiUrl('/homepage/slides'), {
      next: { revalidate: 30 },
    });
    if (!res.ok) return [];
    const json = (await res.json()) as SlidesResponse;
    return Array.isArray(json.data) ? json.data : [];
  } catch {
    return [];
  }
}
