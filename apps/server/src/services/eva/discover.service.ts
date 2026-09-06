import { prisma } from '../../db/client';
import { NotFoundError, ValidationError } from '../../utils/errors';
import {
  ageFromBirthYear,
  buildWhyConnect,
  createNotification,
  getBlockedCustomerIds,
  haversineKm,
  orderedMatchPair,
  profileInclude,
  serializeProfile,
  touchActive,
} from './helpers';

async function myProfile(customerId: number) {
  const profile = await prisma.evaProfile.findUnique({
    where: { customerId },
    include: profileInclude,
  });
  if (!profile) throw new NotFoundError('Complete your EVA profile first');
  return profile;
}

export async function discover(customerId: number, cursor?: string, limit = 10) {
  const me = await myProfile(customerId);
  await touchActive(me.id);
  const prefs = await prisma.evaPreference.findUnique({ where: { customerId } });
  const blocked = await getBlockedCustomerIds(customerId);

  const liked = await prisma.evaLike.findMany({
    where: { fromProfileId: me.id },
    select: { toProfileId: true },
  });
  const passed = await prisma.evaPass.findMany({
    where: { fromProfileId: me.id },
    select: { toProfileId: true },
  });
  const exclude = new Set<number>([
    me.id,
    ...liked.map((l) => l.toProfileId),
    ...passed.map((p) => p.toProfileId),
  ]);

  const ageMin = prefs?.ageMin ?? 18;
  const ageMax = prefs?.ageMax ?? 55;
  const maxKm = prefs?.maxKm ?? 80;
  const genderFilter = prefs?.genders?.length ? prefs.genders : undefined;
  const intentFilter = prefs?.intents?.length ? prefs.intents : undefined;
  const offset = cursor ? Number(cursor) || 0 : 0;

  const candidates = await prisma.evaProfile.findMany({
    where: {
      onboardingComplete: true,
      isHidden: false,
      id: { notIn: [...exclude] },
      customerId: blocked.size ? { notIn: [...blocked] } : undefined,
      gender: genderFilter ? { in: genderFilter } : undefined,
      intent: intentFilter ? { in: intentFilter } : undefined,
    },
    include: profileInclude,
    take: 80,
    orderBy: [{ lastActiveAt: 'desc' }, { completeness: 'desc' }, { createdAt: 'desc' }],
  });

  const meInterestIds = me.interests.map((i) => i.interestId);
  const scored = candidates
    .map((candidate) => {
      const age = ageFromBirthYear(candidate.birthYear);
      if (age < ageMin || age > ageMax) return null;

      let distanceKm: number | null = null;
      if (
        me.latitude != null &&
        me.longitude != null &&
        candidate.latitude != null &&
        candidate.longitude != null
      ) {
        distanceKm = haversineKm(
          me.latitude,
          me.longitude,
          candidate.latitude,
          candidate.longitude,
        );
        if (distanceKm > maxKm) return null;
      }

      const theirInterestIds = candidate.interests.map((i) => i.interestId);
      const sharedCount = theirInterestIds.filter((id) => meInterestIds.includes(id)).length;
      const freshnessBoost =
        Date.now() - (candidate.createdAt?.getTime() ?? Date.now()) < 7 * 86400000 ? 8 : 0;
      const activityBoost = candidate.lastActiveAt
        ? Math.max(0, 10 - (Date.now() - candidate.lastActiveAt.getTime()) / 86400000)
        : 0;
      let score =
        sharedCount * 12 +
        (candidate.intent && me.intent && candidate.intent === me.intent ? 18 : 0) +
        candidate.completeness * 0.35 +
        freshnessBoost +
        activityBoost +
        (candidate.verificationStatus === 'APPROVED' ? 6 : 0);

      // Mild diversity: downweight very complete popular-looking profiles slightly at random
      score += Math.random() * 4;

      const why = buildWhyConnect(
        { intent: me.intent, interestIds: meInterestIds },
        {
          intent: candidate.intent,
          interestIds: theirInterestIds,
          interestLabels: candidate.interests.map((i) => i.interest.label),
        },
      );

      return { candidate, score, distanceKm, why };
    })
    .filter((row): row is NonNullable<typeof row> => Boolean(row))
    .sort((a, b) => b.score - a.score);

  const page = scored.slice(offset, offset + limit);
  const nextOffset = offset + page.length;
  const hasMore = nextOffset < scored.length;

  return {
    items: page.map((row) =>
      serializeProfile(row.candidate, {
        distanceKm: prefs?.showDistance === false ? null : row.distanceKm,
        whyConnect: row.why,
      }),
    ),
    cursor: hasMore ? String(nextOffset) : null,
    hasMore,
  };
}

export async function likeProfile(
  customerId: number,
  input: {
    toProfileId: number;
    targetType?: 'PROFILE' | 'PROMPT' | 'INTEREST' | 'PHOTO';
    targetId?: number;
    comment?: string;
  },
) {
  const me = await myProfile(customerId);
  if (input.toProfileId === me.id) throw new ValidationError('Cannot like yourself');

  const target = await prisma.evaProfile.findUnique({
    where: { id: input.toProfileId },
    include: profileInclude,
  });
  if (!target || target.isHidden) throw new NotFoundError('Profile not found');

  const blocked = await getBlockedCustomerIds(customerId);
  if (blocked.has(target.customerId)) throw new ValidationError('Unable to like this profile');

  await prisma.evaPass.deleteMany({
    where: { fromProfileId: me.id, toProfileId: target.id },
  });

  const like = await prisma.evaLike.upsert({
    where: {
      fromProfileId_toProfileId: {
        fromProfileId: me.id,
        toProfileId: target.id,
      },
    },
    create: {
      fromProfileId: me.id,
      toProfileId: target.id,
      targetType: input.targetType ?? 'PROFILE',
      targetId: input.targetId ?? null,
      comment: input.comment?.slice(0, 280) ?? null,
    },
    update: {
      targetType: input.targetType ?? 'PROFILE',
      targetId: input.targetId ?? null,
      comment: input.comment?.slice(0, 280) ?? null,
    },
  });

  // Soft moderation signal for mass liking
  const recentLikes = await prisma.evaLike.count({
    where: {
      fromProfileId: me.id,
      createdAt: { gte: new Date(Date.now() - 10 * 60 * 1000) },
    },
  });
  if (recentLikes >= 40) {
    await prisma.evaModerationFlag.create({
      data: {
        customerId,
        signal: 'mass_liking',
        score: 2,
        metaJson: { recentLikes },
      },
    });
  }

  const reciprocal = await prisma.evaLike.findUnique({
    where: {
      fromProfileId_toProfileId: {
        fromProfileId: target.id,
        toProfileId: me.id,
      },
    },
  });

  let matchPayload: unknown = null;
  if (reciprocal) {
    const [a, b] = orderedMatchPair(me.id, target.id);
    const match = await prisma.evaMatch.upsert({
      where: { profileAId_profileBId: { profileAId: a, profileBId: b } },
      create: { profileAId: a, profileBId: b },
      update: { unmatchedAt: null, unmatchedById: null },
    });
    await prisma.evaConversation.upsert({
      where: { matchId: match.id },
      create: { matchId: match.id },
      update: {},
    });

    await createNotification({
      customerId: target.customerId,
      kind: 'MATCH',
      title: 'It\'s a match',
      body: `You and ${me.displayName} liked each other.`,
      data: { screen: 'Match', matchId: String(match.id) },
    });
    await createNotification({
      customerId: me.customerId,
      kind: 'MATCH',
      title: 'It\'s a match',
      body: `You and ${target.displayName} liked each other.`,
      data: { screen: 'Match', matchId: String(match.id) },
    });

    matchPayload = {
      id: String(match.id),
      me: serializeProfile(me, { isMe: true }),
      them: serializeProfile(target),
    };
  } else {
    await createNotification({
      customerId: target.customerId,
      kind: 'LIKE',
      title: 'Someone liked you',
      body: 'Open EVA to see who appreciated your profile.',
      data: { screen: 'Matches' },
    });
  }

  return {
    like: {
      id: String(like.id),
      toProfileId: String(like.toProfileId),
      comment: like.comment,
    },
    matched: Boolean(matchPayload),
    match: matchPayload,
  };
}

export async function passProfile(customerId: number, toProfileId: number) {
  const me = await myProfile(customerId);
  if (toProfileId === me.id) throw new ValidationError('Cannot pass yourself');
  await prisma.evaLike.deleteMany({
    where: { fromProfileId: me.id, toProfileId },
  });
  await prisma.evaPass.upsert({
    where: {
      fromProfileId_toProfileId: { fromProfileId: me.id, toProfileId },
    },
    create: { fromProfileId: me.id, toProfileId },
    update: {},
  });
  return { ok: true };
}

export async function undoLastAction(customerId: number) {
  const me = await myProfile(customerId);
  const [lastLike, lastPass] = await Promise.all([
    prisma.evaLike.findFirst({
      where: { fromProfileId: me.id },
      orderBy: { createdAt: 'desc' },
    }),
    prisma.evaPass.findFirst({
      where: { fromProfileId: me.id },
      orderBy: { createdAt: 'desc' },
    }),
  ]);

  const likeTime = lastLike?.createdAt?.getTime() ?? 0;
  const passTime = lastPass?.createdAt?.getTime() ?? 0;
  if (!likeTime && !passTime) throw new ValidationError('Nothing to undo');

  if (likeTime >= passTime && lastLike) {
    // Only undo if no match created yet, or unmatch carefully
    const [a, b] = orderedMatchPair(me.id, lastLike.toProfileId);
    const match = await prisma.evaMatch.findUnique({
      where: { profileAId_profileBId: { profileAId: a, profileBId: b } },
    });
    if (match && !match.unmatchedAt) {
      throw new ValidationError('Cannot undo after a match — unmatch from chat instead');
    }
    await prisma.evaLike.delete({ where: { id: lastLike.id } });
    return { undone: 'like', profileId: String(lastLike.toProfileId) };
  }

  if (lastPass) {
    await prisma.evaPass.delete({ where: { id: lastPass.id } });
    return { undone: 'pass', profileId: String(lastPass.toProfileId) };
  }

  throw new ValidationError('Nothing to undo');
}

export async function listMatches(customerId: number) {
  const me = await myProfile(customerId);
  const matches = await prisma.evaMatch.findMany({
    where: {
      unmatchedAt: null,
      OR: [{ profileAId: me.id }, { profileBId: me.id }],
    },
    include: {
      profileA: { include: profileInclude },
      profileB: { include: profileInclude },
      conversation: {
        include: {
          messages: {
            where: { deletedAt: null },
            orderBy: { createdAt: 'desc' },
            take: 1,
          },
        },
      },
    },
    orderBy: { createdAt: 'desc' },
  });

  const dayAgo = Date.now() - 24 * 60 * 60 * 1000;
  return matches.map((match) => {
    const them = match.profileAId === me.id ? match.profileB : match.profileA;
    const last = match.conversation?.messages[0];
    return {
      id: String(match.id),
      isNew: match.createdAt.getTime() > dayAgo && !last,
      createdAt: match.createdAt.toISOString(),
      conversationId: match.conversation ? String(match.conversation.id) : null,
      lastMessage: last
        ? {
            body: last.body,
            createdAt: last.createdAt.toISOString(),
            mine: last.senderProfileId === me.id,
          }
        : null,
      profile: serializeProfile(them),
    };
  });
}

export async function getPublicProfile(customerId: number, profileId: number) {
  const me = await myProfile(customerId);
  const them = await prisma.evaProfile.findUnique({
    where: { id: profileId },
    include: profileInclude,
  });
  if (!them || them.isHidden) throw new NotFoundError('Profile not found');
  const blocked = await getBlockedCustomerIds(customerId);
  if (blocked.has(them.customerId)) throw new NotFoundError('Profile not found');

  let distanceKm: number | null = null;
  if (
    me.latitude != null &&
    me.longitude != null &&
    them.latitude != null &&
    them.longitude != null
  ) {
    distanceKm = haversineKm(me.latitude, me.longitude, them.latitude, them.longitude);
  }
  const why = buildWhyConnect(
    { intent: me.intent, interestIds: me.interests.map((i) => i.interestId) },
    {
      intent: them.intent,
      interestIds: them.interests.map((i) => i.interestId),
      interestLabels: them.interests.map((i) => i.interest.label),
    },
  );
  return serializeProfile(them, { distanceKm, whyConnect: why });
}
