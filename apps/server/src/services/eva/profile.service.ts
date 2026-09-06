import type { EvaGender, EvaIntent } from '@prisma/client';
import { prisma } from '../../db/client';
import { NotFoundError, ValidationError } from '../../utils/errors';
import {
  ageFromBirthYear,
  birthYearFromAge,
  computeCompleteness,
  profileInclude,
  serializeProfile,
  touchActive,
} from './helpers';

async function requireProfile(customerId: number) {
  const profile = await prisma.evaProfile.findUnique({
    where: { customerId },
    include: profileInclude,
  });
  if (!profile) throw new NotFoundError('EVA profile not found');
  return profile;
}

export async function getEligibility(customerId: number) {
  const row = await prisma.evaEligibility.findUnique({ where: { customerId } });
  return {
    is18Confirmed: Boolean(row?.is18Confirmed),
    confirmedAt: row?.confirmedAt?.toISOString() ?? null,
  };
}

export async function confirmEligibility(customerId: number) {
  const row = await prisma.evaEligibility.upsert({
    where: { customerId },
    create: {
      customerId,
      is18Confirmed: true,
      confirmedAt: new Date(),
    },
    update: {
      is18Confirmed: true,
      confirmedAt: new Date(),
    },
  });
  return {
    is18Confirmed: row.is18Confirmed,
    confirmedAt: row.confirmedAt?.toISOString() ?? null,
  };
}

export async function listInterests() {
  return prisma.evaInterest.findMany({
    where: { active: true },
    orderBy: { label: 'asc' },
  });
}

export async function listPrompts() {
  return prisma.evaPrompt.findMany({
    where: { active: true },
    orderBy: { sortOrder: 'asc' },
  });
}

export async function getMyProfile(customerId: number) {
  const eligibility = await getEligibility(customerId);
  const profile = await prisma.evaProfile.findUnique({
    where: { customerId },
    include: profileInclude,
  });
  const prefs = await prisma.evaPreference.findUnique({ where: { customerId } });
  return {
    eligibility,
    profile: profile ? serializeProfile(profile, { isMe: true }) : null,
    preferences: prefs
      ? {
          ageMin: prefs.ageMin,
          ageMax: prefs.ageMax,
          maxKm: prefs.maxKm,
          genders: prefs.genders,
          intents: prefs.intents,
          interestIds: prefs.interestIds.map(String),
          showDistance: prefs.showDistance,
        }
      : null,
  };
}

export async function upsertProfile(
  customerId: number,
  input: {
    displayName?: string;
    age?: number;
    birthYear?: number;
    gender?: EvaGender;
    pronouns?: string | null;
    bio?: string | null;
    intent?: EvaIntent | null;
    city?: string | null;
    latitude?: number | null;
    longitude?: number | null;
    interestIds?: number[];
    prompts?: { promptId: number; answer: string }[];
    onboardingComplete?: boolean;
  },
) {
  const existing = await prisma.evaProfile.findUnique({ where: { customerId } });
  const birthYear =
    input.birthYear ??
    (input.age != null ? birthYearFromAge(input.age) : existing?.birthYear);
  if (!birthYear) throw new ValidationError('Age or birth year is required');
  const age = ageFromBirthYear(birthYear);
  if (age < 18) throw new ValidationError('You must be at least 18 to use EVA');

  const displayName = (input.displayName ?? existing?.displayName ?? '').trim();
  if (!displayName) throw new ValidationError('Display name is required');
  const gender = input.gender ?? existing?.gender;
  if (!gender) throw new ValidationError('Gender is required');

  const profile = await prisma.evaProfile.upsert({
    where: { customerId },
    create: {
      customerId,
      displayName,
      birthYear,
      gender,
      pronouns: input.pronouns ?? null,
      bio: input.bio ?? null,
      intent: input.intent ?? null,
      city: input.city ?? null,
      latitude: input.latitude ?? null,
      longitude: input.longitude ?? null,
      onboardingComplete: Boolean(input.onboardingComplete),
      lastActiveAt: new Date(),
    },
    update: {
      displayName,
      birthYear,
      gender,
      pronouns: input.pronouns === undefined ? undefined : input.pronouns,
      bio: input.bio === undefined ? undefined : input.bio,
      intent: input.intent === undefined ? undefined : input.intent,
      city: input.city === undefined ? undefined : input.city,
      latitude: input.latitude === undefined ? undefined : input.latitude,
      longitude: input.longitude === undefined ? undefined : input.longitude,
      onboardingComplete:
        input.onboardingComplete === undefined ? undefined : input.onboardingComplete,
      lastActiveAt: new Date(),
    },
  });

  if (input.interestIds) {
    await prisma.evaProfileInterest.deleteMany({ where: { profileId: profile.id } });
    if (input.interestIds.length) {
      await prisma.evaProfileInterest.createMany({
        data: input.interestIds.map((interestId) => ({
          profileId: profile.id,
          interestId,
        })),
        skipDuplicates: true,
      });
    }
  }

  if (input.prompts) {
    await prisma.evaProfilePrompt.deleteMany({ where: { profileId: profile.id } });
    for (const [index, item] of input.prompts.entries()) {
      const answer = item.answer.trim();
      if (!answer) continue;
      await prisma.evaProfilePrompt.create({
        data: {
          profileId: profile.id,
          promptId: item.promptId,
          answer: answer.slice(0, 400),
          sortOrder: index,
        },
      });
    }
  }

  const full = await requireProfile(customerId);
  const photos = full.media.filter((m) => m.kind === 'PHOTO');
  const primary = photos.find((p) => p.isPrimary) ?? photos[0];
  const { score } = computeCompleteness({
    hasPrimaryPhoto: Boolean(primary),
    photoCount: photos.length,
    bio: full.bio,
    intent: full.intent,
    interestCount: full.interests.length,
    promptCount: full.prompts.length,
    city: full.city,
    verified: full.verificationStatus === 'APPROVED',
  });
  await prisma.evaProfile.update({
    where: { id: full.id },
    data: { completeness: score },
  });

  const refreshed = await requireProfile(customerId);
  return serializeProfile(refreshed, { isMe: true });
}

export async function upsertPreferences(
  customerId: number,
  input: {
    ageMin?: number;
    ageMax?: number;
    maxKm?: number;
    genders?: EvaGender[];
    intents?: EvaIntent[];
    interestIds?: number[];
    showDistance?: boolean;
  },
) {
  const ageMin = input.ageMin ?? 18;
  const ageMax = input.ageMax ?? 50;
  if (ageMin < 18 || ageMax < ageMin) {
    throw new ValidationError('Invalid age preference range');
  }
  const row = await prisma.evaPreference.upsert({
    where: { customerId },
    create: {
      customerId,
      ageMin,
      ageMax,
      maxKm: input.maxKm ?? 50,
      genders: input.genders ?? [],
      intents: input.intents ?? [],
      interestIds: input.interestIds ?? [],
      showDistance: input.showDistance ?? true,
    },
    update: {
      ageMin,
      ageMax,
      maxKm: input.maxKm,
      genders: input.genders,
      intents: input.intents,
      interestIds: input.interestIds,
      showDistance: input.showDistance,
    },
  });
  return {
    ageMin: row.ageMin,
    ageMax: row.ageMax,
    maxKm: row.maxKm,
    genders: row.genders,
    intents: row.intents,
    interestIds: row.interestIds.map(String),
    showDistance: row.showDistance,
  };
}

export async function addMedia(
  customerId: number,
  input: { url: string; kind?: 'PHOTO' | 'VIDEO' | 'SELFIE_VERIFY'; isPrimary?: boolean },
) {
  const profile = await prisma.evaProfile.findUnique({ where: { customerId } });
  if (!profile) throw new NotFoundError('Create your EVA profile first');

  const kind = input.kind ?? 'PHOTO';
  if (kind === 'SELFIE_VERIFY') {
    await prisma.evaMedia.create({
      data: {
        profileId: profile.id,
        url: input.url,
        kind,
        sortOrder: 999,
        isPrimary: false,
        moderationStatus: 'PENDING',
      },
    });
    await prisma.evaProfile.update({
      where: { id: profile.id },
      data: { verificationStatus: 'PENDING' },
    });
    return getMyProfile(customerId);
  }

  const count = await prisma.evaMedia.count({
    where: { profileId: profile.id, kind: { in: ['PHOTO', 'VIDEO'] } },
  });
  const makePrimary = input.isPrimary || count === 0;
  if (makePrimary) {
    await prisma.evaMedia.updateMany({
      where: { profileId: profile.id },
      data: { isPrimary: false },
    });
  }
  await prisma.evaMedia.create({
    data: {
      profileId: profile.id,
      url: input.url,
      kind,
      sortOrder: count,
      isPrimary: makePrimary,
      moderationStatus: 'APPROVED',
    },
  });
  await touchActive(profile.id);
  return getMyProfile(customerId);
}

export async function reorderMedia(customerId: number, orderedIds: number[]) {
  const profile = await requireProfile(customerId);
  for (const [index, id] of orderedIds.entries()) {
    await prisma.evaMedia.updateMany({
      where: { id, profileId: profile.id },
      data: { sortOrder: index, isPrimary: index === 0 },
    });
  }
  return getMyProfile(customerId);
}

export async function deleteMedia(customerId: number, mediaId: number) {
  const profile = await requireProfile(customerId);
  await prisma.evaMedia.deleteMany({ where: { id: mediaId, profileId: profile.id } });
  const remaining = await prisma.evaMedia.findMany({
    where: { profileId: profile.id, kind: { in: ['PHOTO', 'VIDEO'] } },
    orderBy: { sortOrder: 'asc' },
  });
  if (remaining.length && !remaining.some((m) => m.isPrimary)) {
    await prisma.evaMedia.update({
      where: { id: remaining[0].id },
      data: { isPrimary: true },
    });
  }
  return getMyProfile(customerId);
}

export async function submitVerification(customerId: number, url: string) {
  return addMedia(customerId, { url, kind: 'SELFIE_VERIFY' });
}

/** Auto-approve for demo/production bootstrap when no admin review queue UI exists yet. */
export async function autoApprovePendingVerification(customerId: number) {
  const profile = await prisma.evaProfile.findUnique({ where: { customerId } });
  if (!profile || profile.verificationStatus !== 'PENDING') {
    return getMyProfile(customerId);
  }
  await prisma.evaMedia.updateMany({
    where: { profileId: profile.id, kind: 'SELFIE_VERIFY', moderationStatus: 'PENDING' },
    data: { moderationStatus: 'APPROVED' },
  });
  await prisma.evaProfile.update({
    where: { id: profile.id },
    data: {
      verificationStatus: 'APPROVED',
      photoVerifiedAt: new Date(),
    },
  });
  const { createNotification } = await import('./helpers');
  await createNotification({
    customerId,
    kind: 'VERIFICATION',
    title: 'Photo verified',
    body: 'Your EVA profile now shows a Photo verified badge.',
    data: { screen: 'Profile' },
  });
  return getMyProfile(customerId);
}
