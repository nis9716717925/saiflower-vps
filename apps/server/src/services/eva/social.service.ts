import { prisma } from '../../db/client';
import { NotFoundError, ValidationError } from '../../utils/errors';
import { createNotification, profileInclude, serializeProfile } from './helpers';

export async function listDatingEvents(customerId: number) {
  const events = await prisma.evaDatingEvent.findMany({
    where: { active: true, startsAt: { gte: new Date(Date.now() - 6 * 3600000) } },
    orderBy: { startsAt: 'asc' },
    include: {
      interests: {
        where: { kind: { in: ['INTERESTED', 'JOINED'] } },
      },
    },
  });

  const mine = await prisma.evaEventInterest.findMany({ where: { customerId } });
  const mineMap = new Map(mine.map((m) => [m.eventId, m]));

  return events.map((event) => {
    const my = mineMap.get(event.id);
    return {
      id: String(event.id),
      title: event.title,
      slug: event.slug,
      summary: event.summary,
      description: event.description,
      coverUrl: event.coverUrl,
      city: event.city,
      venueApprox: event.venueApprox,
      startsAt: event.startsAt.toISOString(),
      endsAt: event.endsAt?.toISOString() ?? null,
      capacity: event.capacity,
      interestedCount: event.interests.length,
      myKind: my?.kind ?? null,
    };
  });
}

export async function getDatingEvent(customerId: number, eventId: number) {
  const event = await prisma.evaDatingEvent.findUnique({
    where: { id: eventId },
    include: {
      interests: {
        where: { kind: { in: ['INTERESTED', 'JOINED'] }, discoverable: true },
        include: {
          customer: {
            include: { evaProfile: { include: profileInclude } },
          },
        },
      },
    },
  });
  if (!event || !event.active) throw new NotFoundError('Event not found');

  const my = await prisma.evaEventInterest.findUnique({
    where: { eventId_customerId: { eventId, customerId } },
  });

  const discoverablePeople = event.interests
    .filter((row) => row.customerId !== customerId && row.customer.evaProfile)
    .map((row) => serializeProfile(row.customer.evaProfile!));

  return {
    id: String(event.id),
    title: event.title,
    slug: event.slug,
    summary: event.summary,
    description: event.description,
    coverUrl: event.coverUrl,
    city: event.city,
    venueApprox: event.venueApprox,
    startsAt: event.startsAt.toISOString(),
    endsAt: event.endsAt?.toISOString() ?? null,
    capacity: event.capacity,
    interestedCount: event.interests.length,
    myKind: my?.kind ?? null,
    peopleYouMayKnowCount: discoverablePeople.length,
    peoplePreview: discoverablePeople.slice(0, 12),
  };
}

export async function setEventInterest(
  customerId: number,
  eventId: number,
  kind: 'SAVED' | 'INTERESTED' | 'JOINED',
  discoverable = true,
) {
  const event = await prisma.evaDatingEvent.findUnique({ where: { id: eventId } });
  if (!event || !event.active) throw new NotFoundError('Event not found');

  await prisma.evaEventInterest.upsert({
    where: { eventId_customerId: { eventId, customerId } },
    create: { eventId, customerId, kind, discoverable },
    update: { kind, discoverable },
  });

  if (kind === 'JOINED' || kind === 'INTERESTED') {
    await createNotification({
      customerId,
      kind: 'EVENT',
      title: event.title,
      body:
        kind === 'JOINED'
          ? 'You are on the list. We will remind you before it starts.'
          : 'Saved your interest for this EVA event.',
      data: { screen: 'EventDetail', eventId: String(eventId) },
    });
  }

  return getDatingEvent(customerId, eventId);
}

export async function listRaveRooms() {
  const now = new Date();
  const rooms = await prisma.evaRaveRoom.findMany({
    where: { active: true },
    include: {
      presence: { where: { expiresAt: { gt: now } } },
    },
    orderBy: { title: 'asc' },
  });
  return rooms.map((room) => ({
    id: String(room.id),
    slug: room.slug,
    title: room.title,
    theme: room.theme,
    description: room.description,
    prompt: room.prompt,
    activeCount: room.presence.length,
  }));
}

export async function joinRaveRoom(customerId: number, roomId: number) {
  const room = await prisma.evaRaveRoom.findUnique({ where: { id: roomId } });
  if (!room || !room.active) throw new NotFoundError('Rave room not found');
  const expiresAt = new Date(Date.now() + 15 * 60 * 1000);
  await prisma.evaRavePresence.upsert({
    where: { roomId_customerId: { roomId, customerId } },
    create: { roomId, customerId, expiresAt },
    update: { expiresAt, joinedAt: new Date() },
  });
  return getRaveRoom(customerId, roomId);
}

export async function leaveRaveRoom(customerId: number, roomId: number) {
  await prisma.evaRavePresence.deleteMany({ where: { roomId, customerId } });
  return { ok: true };
}

export async function getRaveRoom(customerId: number, roomId: number) {
  const now = new Date();
  const room = await prisma.evaRaveRoom.findUnique({
    where: { id: roomId },
    include: {
      presence: {
        where: { expiresAt: { gt: now } },
        include: {
          customer: { include: { evaProfile: { include: profileInclude } } },
        },
      },
    },
  });
  if (!room || !room.active) throw new NotFoundError('Rave room not found');

  const people = room.presence
    .filter((p) => p.customer.evaProfile && p.customerId !== customerId)
    .map((p) => serializeProfile(p.customer.evaProfile!));

  return {
    id: String(room.id),
    slug: room.slug,
    title: room.title,
    theme: room.theme,
    description: room.description,
    prompt: room.prompt,
    activeCount: room.presence.length,
    iAmHere: room.presence.some((p) => p.customerId === customerId),
    people,
  };
}

export async function listNotifications(customerId: number) {
  const rows = await prisma.evaNotification.findMany({
    where: { customerId },
    orderBy: { createdAt: 'desc' },
    take: 50,
  });
  return rows.map((n) => ({
    id: String(n.id),
    kind: n.kind,
    title: n.title,
    body: n.body,
    data: n.dataJson,
    readAt: n.readAt?.toISOString() ?? null,
    createdAt: n.createdAt.toISOString(),
  }));
}

export async function markNotificationsRead(customerId: number, ids?: number[]) {
  await prisma.evaNotification.updateMany({
    where: {
      customerId,
      readAt: null,
      ...(ids?.length ? { id: { in: ids } } : {}),
    },
    data: { readAt: new Date() },
  });
  return { ok: true };
}

export async function registerPushToken(_customerId: number, _token: string) {
  // Token storage can be expanded later; acknowledge for client flow.
  if (!_token.trim()) throw new ValidationError('Push token required');
  return { ok: true };
}
