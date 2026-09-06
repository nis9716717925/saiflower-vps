import { prisma } from '../../db/client';
import { NotFoundError, ValidationError } from '../../utils/errors';
import {
  createNotification,
  getBlockedCustomerIds,
  profileInclude,
  serializeProfile,
} from './helpers';

async function myProfile(customerId: number) {
  const profile = await prisma.evaProfile.findUnique({
    where: { customerId },
    include: profileInclude,
  });
  if (!profile) throw new NotFoundError('Complete your EVA profile first');
  return profile;
}

async function assertConversationAccess(customerId: number, conversationId: number) {
  const me = await myProfile(customerId);
  const conversation = await prisma.evaConversation.findUnique({
    where: { id: conversationId },
    include: {
      match: {
        include: {
          profileA: { include: profileInclude },
          profileB: { include: profileInclude },
        },
      },
    },
  });
  if (!conversation || conversation.match.unmatchedAt) {
    throw new NotFoundError('Conversation not found');
  }
  const { match } = conversation;
  if (match.profileAId !== me.id && match.profileBId !== me.id) {
    throw new NotFoundError('Conversation not found');
  }
  const them = match.profileAId === me.id ? match.profileB : match.profileA;
  const blocked = await getBlockedCustomerIds(customerId);
  if (blocked.has(them.customerId)) {
    throw new ValidationError('You cannot message this person');
  }
  return { me, conversation, them, match };
}

export async function listConversations(customerId: number) {
  const me = await myProfile(customerId);
  const conversations = await prisma.evaConversation.findMany({
    where: {
      match: {
        unmatchedAt: null,
        OR: [{ profileAId: me.id }, { profileBId: me.id }],
      },
    },
    include: {
      match: {
        include: {
          profileA: { include: profileInclude },
          profileB: { include: profileInclude },
        },
      },
      messages: {
        where: { deletedAt: null },
        orderBy: { createdAt: 'desc' },
        take: 1,
      },
    },
    orderBy: [{ lastMessageAt: 'desc' }, { createdAt: 'desc' }],
  });

  return conversations.map((c) => {
    const them = c.match.profileAId === me.id ? c.match.profileB : c.match.profileA;
    const last = c.messages[0];
    const unread = last && last.senderProfileId !== me.id && !last.readAt ? 1 : 0;
    return {
      id: String(c.id),
      matchId: String(c.matchId),
      profile: serializeProfile(them),
      unreadCount: unread,
      lastMessage: last
        ? {
            id: String(last.id),
            body: last.body,
            createdAt: last.createdAt.toISOString(),
            mine: last.senderProfileId === me.id,
            readAt: last.readAt?.toISOString() ?? null,
          }
        : null,
    };
  });
}

export async function getMessages(
  customerId: number,
  conversationId: number,
  afterId?: number,
) {
  const { me, conversation, them } = await assertConversationAccess(
    customerId,
    conversationId,
  );

  await prisma.evaMessage.updateMany({
    where: {
      conversationId,
      senderProfileId: { not: me.id },
      readAt: null,
      deletedAt: null,
    },
    data: { readAt: new Date(), deliveredAt: new Date() },
  });

  const messages = await prisma.evaMessage.findMany({
    where: {
      conversationId,
      deletedAt: null,
      ...(afterId ? { id: { gt: afterId } } : {}),
    },
    orderBy: { createdAt: 'asc' },
    take: 100,
  });

  const typing = await prisma.evaTyping.findMany({
    where: {
      conversationId,
      profileId: them.id,
      expiresAt: { gt: new Date() },
    },
  });

  const sharedInterestLabels = me.interests
    .map((i) => i.interest)
    .filter((interest) => them.interests.some((t) => t.interestId === interest.id))
    .map((i) => i.label);

  const starters = [
    ...sharedInterestLabels.slice(0, 2).map((label) => `Ask about their love of ${label}`),
    ...them.prompts.slice(0, 2).map((p) => `Respond to: “${p.prompt.text}”`),
  ].slice(0, 4);

  return {
    conversationId: String(conversation.id),
    profile: serializeProfile(them),
    typing: typing.length > 0,
    starters,
    messages: messages.map((m) => ({
      id: String(m.id),
      body: m.body,
      mine: m.senderProfileId === me.id,
      createdAt: m.createdAt.toISOString(),
      deliveredAt: m.deliveredAt?.toISOString() ?? null,
      readAt: m.readAt?.toISOString() ?? null,
    })),
  };
}

export async function sendMessage(
  customerId: number,
  conversationId: number,
  body: string,
) {
  const text = body.trim();
  if (!text) throw new ValidationError('Message cannot be empty');
  if (text.length > 2000) throw new ValidationError('Message is too long');

  const { me, them, conversation } = await assertConversationAccess(
    customerId,
    conversationId,
  );

  const message = await prisma.evaMessage.create({
    data: {
      conversationId: conversation.id,
      senderProfileId: me.id,
      body: text,
      deliveredAt: new Date(),
    },
  });
  await prisma.evaConversation.update({
    where: { id: conversation.id },
    data: { lastMessageAt: message.createdAt },
  });
  await createNotification({
    customerId: them.customerId,
    kind: 'MESSAGE',
    title: me.displayName,
    body: text.slice(0, 120),
    data: {
      screen: 'Conversation',
      conversationId: String(conversation.id),
    },
  });

  return {
    id: String(message.id),
    body: message.body,
    mine: true,
    createdAt: message.createdAt.toISOString(),
    deliveredAt: message.deliveredAt?.toISOString() ?? null,
    readAt: null,
  };
}

export async function setTyping(customerId: number, conversationId: number) {
  const { me } = await assertConversationAccess(customerId, conversationId);
  const expiresAt = new Date(Date.now() + 4000);
  await prisma.evaTyping.upsert({
    where: {
      conversationId_profileId: { conversationId, profileId: me.id },
    },
    create: { conversationId, profileId: me.id, expiresAt },
    update: { expiresAt },
  });
  return { ok: true };
}

export async function unmatch(customerId: number, matchId: number) {
  const me = await myProfile(customerId);
  const match = await prisma.evaMatch.findUnique({ where: { id: matchId } });
  if (!match || (match.profileAId !== me.id && match.profileBId !== me.id)) {
    throw new NotFoundError('Match not found');
  }
  await prisma.evaMatch.update({
    where: { id: matchId },
    data: { unmatchedAt: new Date(), unmatchedById: me.id },
  });
  return { ok: true };
}

export async function blockUser(customerId: number, blockedCustomerId: number) {
  if (customerId === blockedCustomerId) {
    throw new ValidationError('Cannot block yourself');
  }
  await prisma.evaBlock.upsert({
    where: {
      blockerCustomerId_blockedCustomerId: {
        blockerCustomerId: customerId,
        blockedCustomerId,
      },
    },
    create: { blockerCustomerId: customerId, blockedCustomerId },
    update: {},
  });

  const me = await prisma.evaProfile.findUnique({ where: { customerId } });
  const them = await prisma.evaProfile.findUnique({
    where: { customerId: blockedCustomerId },
  });
  if (me && them) {
    const matches = await prisma.evaMatch.findMany({
      where: {
        unmatchedAt: null,
        OR: [
          { profileAId: me.id, profileBId: them.id },
          { profileAId: them.id, profileBId: me.id },
        ],
      },
    });
    for (const match of matches) {
      await prisma.evaMatch.update({
        where: { id: match.id },
        data: { unmatchedAt: new Date(), unmatchedById: me.id },
      });
    }
  }
  return { ok: true };
}

export async function reportUser(
  customerId: number,
  reportedCustomerId: number,
  reason: string,
  details?: string,
) {
  if (!reason.trim()) throw new ValidationError('Reason is required');
  await prisma.evaReport.create({
    data: {
      reporterCustomerId: customerId,
      reportedCustomerId,
      reason: reason.slice(0, 80),
      details: details?.slice(0, 1000) ?? null,
    },
  });
  await prisma.evaModerationFlag.create({
    data: {
      customerId: reportedCustomerId,
      signal: 'user_report',
      score: 3,
      metaJson: { reason, reporterCustomerId: customerId },
    },
  });
  return { ok: true };
}

export function safetyContent() {
  return {
    title: 'EVA Safety Center',
    sections: [
      {
        id: 'tips',
        title: 'Dating safety tips',
        body: 'Meet in public places, tell a friend your plans, and never share financial information.',
      },
      {
        id: 'report',
        title: 'How to report',
        body: 'Open a profile or chat, choose Report, and tell us what happened. Our team reviews every report.',
      },
      {
        id: 'block',
        title: 'How to block',
        body: 'Blocking removes the person from discovery, matches, and chat. They will not be notified.',
      },
      {
        id: 'verification',
        title: 'Photo verification',
        body: 'A Photo verified badge means the person completed a selfie check that reasonably matches their photos. It is not government ID verification.',
      },
      {
        id: 'scams',
        title: 'Scam awareness',
        body: 'Be wary of anyone who asks for money, moves you off-app quickly, or refuses a video call.',
      },
      {
        id: 'meeting',
        title: 'Meeting safely',
        body: 'Share your date details with a trusted contact using Share My Date, arrange your own transport, and leave if you feel uncomfortable.',
      },
      {
        id: 'support',
        title: 'Support',
        body: 'Need help? Contact SaiFlower support through the Help screen in the main app.',
      },
    ],
  };
}
