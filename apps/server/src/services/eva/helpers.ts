import type { EvaGender, EvaIntent, EvaProfile, EvaMedia, EvaInterest, EvaPrompt, Prisma } from '@prisma/client';
import { prisma } from '../../db/client';

export const CURRENT_YEAR = new Date().getFullYear();

export function ageFromBirthYear(birthYear: number): number {
  return Math.max(0, CURRENT_YEAR - birthYear);
}

export function birthYearFromAge(age: number): number {
  return CURRENT_YEAR - age;
}

export function haversineKm(
  lat1: number,
  lon1: number,
  lat2: number,
  lon2: number,
): number {
  const toRad = (d: number) => (d * Math.PI) / 180;
  const R = 6371;
  const dLat = toRad(lat2 - lat1);
  const dLon = toRad(lon2 - lon1);
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

export function distanceLabel(km: number | null | undefined): string | null {
  if (km == null || Number.isNaN(km)) return null;
  if (km < 2) return 'Near you';
  if (km < 5) return 'Within 5 km';
  if (km < 10) return 'Within 10 km';
  if (km < 25) return 'Within 25 km';
  if (km < 50) return 'Within 50 km';
  return 'Farther away';
}

export function orderedMatchPair(a: number, b: number): [number, number] {
  return a < b ? [a, b] : [b, a];
}

export async function getBlockedCustomerIds(customerId: number): Promise<Set<number>> {
  const rows = await prisma.evaBlock.findMany({
    where: {
      OR: [{ blockerCustomerId: customerId }, { blockedCustomerId: customerId }],
    },
    select: { blockerCustomerId: true, blockedCustomerId: true },
  });
  const ids = new Set<number>();
  for (const row of rows) {
    if (row.blockerCustomerId !== customerId) ids.add(row.blockerCustomerId);
    if (row.blockedCustomerId !== customerId) ids.add(row.blockedCustomerId);
  }
  return ids;
}

export function computeCompleteness(input: {
  hasPrimaryPhoto: boolean;
  photoCount: number;
  bio?: string | null;
  intent?: EvaIntent | null;
  interestCount: number;
  promptCount: number;
  city?: string | null;
  verified: boolean;
}): { score: number; tips: string[] } {
  let score = 0;
  const tips: string[] = [];
  if (input.hasPrimaryPhoto) score += 20;
  else tips.push('Add a clear primary photo');
  if (input.photoCount >= 3) score += 15;
  else tips.push('Add at least 3 photos');
  if (input.bio && input.bio.trim().length >= 20) score += 15;
  else tips.push('Write a short bio');
  if (input.intent) score += 10;
  else tips.push('Share what you are looking for');
  if (input.interestCount >= 3) score += 15;
  else tips.push('Add a few interests');
  if (input.promptCount >= 2) score += 15;
  else tips.push('Answer at least two prompts');
  if (input.city) score += 5;
  else tips.push('Add your city');
  if (input.verified) score += 5;
  else tips.push('Complete photo verification');
  return { score: Math.min(100, score), tips };
}

type ProfileBundle = EvaProfile & {
  media: EvaMedia[];
  interests: { interest: EvaInterest }[];
  prompts: { id: number; answer: string; sortOrder: number; prompt: EvaPrompt }[];
};

export function serializeProfile(
  profile: ProfileBundle,
  extras?: {
    distanceKm?: number | null;
    whyConnect?: string[];
    isMe?: boolean;
  },
) {
  const age = ageFromBirthYear(profile.birthYear);
  const photos = [...profile.media]
    .filter((m) => m.kind === 'PHOTO' || m.kind === 'VIDEO')
    .sort((a, b) => a.sortOrder - b.sortOrder);
  const primary = photos.find((p) => p.isPrimary) ?? photos[0] ?? null;
  const { score, tips } = computeCompleteness({
    hasPrimaryPhoto: Boolean(primary),
    photoCount: photos.length,
    bio: profile.bio,
    intent: profile.intent,
    interestCount: profile.interests.length,
    promptCount: profile.prompts.length,
    city: profile.city,
    verified: profile.verificationStatus === 'APPROVED',
  });

  return {
    id: String(profile.id),
    customerId: String(profile.customerId),
    displayName: profile.displayName,
    age,
    birthYear: profile.birthYear,
    gender: profile.gender,
    pronouns: profile.pronouns,
    bio: profile.bio,
    intent: profile.intent,
    city: profile.city,
    completeness: Math.max(profile.completeness, score),
    profileStrengthTips: tips,
    onboardingComplete: profile.onboardingComplete,
    verificationStatus: profile.verificationStatus,
    photoVerified: profile.verificationStatus === 'APPROVED',
    lastActiveAt: profile.lastActiveAt?.toISOString() ?? null,
    primaryPhotoUrl: primary?.url ?? null,
    media: photos.map((m) => ({
      id: String(m.id),
      url: m.url,
      kind: m.kind,
      sortOrder: m.sortOrder,
      isPrimary: m.isPrimary,
      moderationStatus: m.moderationStatus,
    })),
    interests: profile.interests.map((row) => ({
      id: String(row.interest.id),
      slug: row.interest.slug,
      label: row.interest.label,
      category: row.interest.category,
    })),
    prompts: [...profile.prompts]
      .sort((a, b) => a.sortOrder - b.sortOrder)
      .map((row) => ({
        id: String(row.id),
        promptId: String(row.prompt.id),
        prompt: row.prompt.text,
        answer: row.answer,
        sortOrder: row.sortOrder,
      })),
    distanceLabel: extras?.isMe ? null : distanceLabel(extras?.distanceKm),
    distanceKm: extras?.isMe ? null : extras?.distanceKm ?? null,
    whyYouMayConnect: extras?.whyConnect ?? [],
  };
}

export const profileInclude = {
  media: true,
  interests: { include: { interest: true } },
  prompts: { include: { prompt: true } },
} as const;

export function buildWhyConnect(
  me: { intent?: EvaIntent | null; interestIds: number[] },
  them: { intent?: EvaIntent | null; interestIds: number[]; interestLabels: string[] },
): string[] {
  const reasons: string[] = [];
  if (me.intent && them.intent && me.intent === them.intent) {
    reasons.push('Same relationship intention');
  }
  const shared = them.interestLabels.filter((_, i) => me.interestIds.includes(them.interestIds[i]));
  for (const label of shared.slice(0, 3)) {
    reasons.push(`Both into ${label}`);
  }
  return reasons.slice(0, 4);
}

export async function touchActive(profileId: number) {
  await prisma.evaProfile.update({
    where: { id: profileId },
    data: { lastActiveAt: new Date() },
  });
}

export async function createNotification(input: {
  customerId: number;
  kind: 'MATCH' | 'MESSAGE' | 'LIKE' | 'EVENT' | 'RAVE' | 'VERIFICATION' | 'SAFETY' | 'SYSTEM';
  title: string;
  body: string;
  data?: Record<string, unknown>;
}) {
  return prisma.evaNotification.create({
    data: {
      customerId: input.customerId,
      kind: input.kind,
      title: input.title,
      body: input.body,
      dataJson: (input.data as Prisma.InputJsonValue) ?? undefined,
    },
  });
}

export type { EvaGender, EvaIntent };
