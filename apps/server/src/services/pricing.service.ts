import { prisma, num } from '../db/client';

type SurgeMap = Record<string, number>;

let cache: { at: number; surges: SurgeMap } | null = null;
const TTL_MS = 30_000;

async function loadSurges(): Promise<SurgeMap> {
  if (cache && Date.now() - cache.at < TTL_MS) return cache.surges;

  const surges: SurgeMap = {
    all: 1,
    flower: 1,
    cake: 1,
    gift: 1,
    addon: 1,
  };

  try {
    const row = await prisma.globalPricing.findFirst({ where: { id: 1 } });
    if (row) {
      surges.all = 1 + num(row.surgePercentage) / 100;
      surges.flower = 1 + num(row.flowerSurge) / 100;
      surges.cake = 1 + num(row.cakeSurge) / 100;
      surges.gift = 1 + num(row.giftSurge) / 100;
    }
  } catch {
    // Table may be missing in empty local DBs — keep 1.0 multipliers (PHP also defaults).
  }

  cache = { at: Date.now(), surges };
  return surges;
}

/** Mirrors includes/pricing_helper.php apply_surge_pricing(). */
export async function applySurgePricing(
  basePrice: number,
  category: string = 'all',
): Promise<number> {
  const surges = await loadSurges();
  let multiplier = 1;
  if (category !== 'all' && (surges[category] ?? 1) > 1) {
    multiplier = surges[category];
  } else {
    multiplier = surges.all ?? 1;
  }
  return Math.round(Number(basePrice) * multiplier);
}
