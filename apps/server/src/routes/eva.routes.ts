import { Router } from 'express';
import { body, param, query } from 'express-validator';
import type { AuthRequest } from '../middleware/auth';
import { authenticate } from '../middleware/auth';
import { evaUpload, publicEvaUploadPath } from '../middleware/evaUpload';
import { validate } from '../middleware/errorHandler';
import * as chatService from '../services/eva/chat.service';
import * as discoverService from '../services/eva/discover.service';
import * as profileService from '../services/eva/profile.service';
import * as socialService from '../services/eva/social.service';
import { successResponse } from '../utils/response';

const router = Router();
router.use(authenticate);

router.get('/eligibility', async (req: AuthRequest, res, next) => {
  try {
    const data = await profileService.getEligibility(req.user!.id);
    res.json(successResponse('Eligibility', data));
  } catch (err) {
    next(err);
  }
});

router.post('/eligibility', async (req: AuthRequest, res, next) => {
  try {
    const data = await profileService.confirmEligibility(req.user!.id);
    res.json(successResponse('Age confirmed', data));
  } catch (err) {
    next(err);
  }
});

router.get('/interests', async (_req, res, next) => {
  try {
    const data = await profileService.listInterests();
    res.json(successResponse('Interests', data));
  } catch (err) {
    next(err);
  }
});

router.get('/prompts', async (_req, res, next) => {
  try {
    const data = await profileService.listPrompts();
    res.json(successResponse('Prompts', data));
  } catch (err) {
    next(err);
  }
});

router.get('/me', async (req: AuthRequest, res, next) => {
  try {
    const data = await profileService.getMyProfile(req.user!.id);
    res.json(successResponse('EVA profile', data));
  } catch (err) {
    next(err);
  }
});

router.put(
  '/me/profile',
  validate([
    body('displayName').optional().isString().trim().isLength({ min: 1, max: 80 }),
    body('age').optional().isInt({ min: 18, max: 100 }),
    body('birthYear').optional().isInt({ min: 1920, max: new Date().getFullYear() - 18 }),
    body('gender').optional().isIn(['WOMAN', 'MAN', 'NON_BINARY', 'OTHER', 'PREFER_NOT']),
    body('intent')
      .optional({ nullable: true })
      .isIn(['LONG_TERM', 'SERIOUS', 'DATING', 'NEW_CONNECTIONS', 'FRIENDSHIP', 'CASUAL']),
    body('interestIds').optional().isArray(),
    body('prompts').optional().isArray(),
  ]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await profileService.upsertProfile(req.user!.id, {
        displayName: req.body.displayName,
        age: req.body.age != null ? Number(req.body.age) : undefined,
        birthYear: req.body.birthYear != null ? Number(req.body.birthYear) : undefined,
        gender: req.body.gender,
        pronouns: req.body.pronouns,
        bio: req.body.bio,
        intent: req.body.intent,
        city: req.body.city,
        latitude: req.body.latitude != null ? Number(req.body.latitude) : undefined,
        longitude: req.body.longitude != null ? Number(req.body.longitude) : undefined,
        interestIds: Array.isArray(req.body.interestIds)
          ? req.body.interestIds.map(Number)
          : undefined,
        prompts: Array.isArray(req.body.prompts)
          ? req.body.prompts.map((p: { promptId: number | string; answer: string }) => ({
              promptId: Number(p.promptId),
              answer: String(p.answer ?? ''),
            }))
          : undefined,
        onboardingComplete: req.body.onboardingComplete,
      });
      res.json(successResponse('Profile saved', data));
    } catch (err) {
      next(err);
    }
  },
);

router.put(
  '/me/preferences',
  validate([
    body('ageMin').optional().isInt({ min: 18, max: 100 }),
    body('ageMax').optional().isInt({ min: 18, max: 100 }),
    body('maxKm').optional().isInt({ min: 1, max: 500 }),
  ]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await profileService.upsertPreferences(req.user!.id, {
        ageMin: req.body.ageMin != null ? Number(req.body.ageMin) : undefined,
        ageMax: req.body.ageMax != null ? Number(req.body.ageMax) : undefined,
        maxKm: req.body.maxKm != null ? Number(req.body.maxKm) : undefined,
        genders: req.body.genders,
        intents: req.body.intents,
        interestIds: Array.isArray(req.body.interestIds)
          ? req.body.interestIds.map(Number)
          : undefined,
        showDistance: req.body.showDistance,
      });
      res.json(successResponse('Preferences saved', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post('/media', evaUpload.single('file'), async (req: AuthRequest, res, next) => {
  try {
    if (!req.file) {
      res.status(422).json({ success: false, message: 'File required' });
      return;
    }
    const url = publicEvaUploadPath(req.file.filename);
    const kind = (req.body.kind as 'PHOTO' | 'VIDEO' | 'SELFIE_VERIFY' | undefined) ?? 'PHOTO';
    const data = await profileService.addMedia(req.user!.id, {
      url,
      kind,
      isPrimary: req.body.isPrimary === 'true' || req.body.isPrimary === true,
    });
    if (kind === 'SELFIE_VERIFY') {
      // Bootstrap: auto-approve after short review placeholder
      await profileService.autoApprovePendingVerification(req.user!.id);
      const refreshed = await profileService.getMyProfile(req.user!.id);
      res.status(201).json(successResponse('Media uploaded', refreshed));
      return;
    }
    res.status(201).json(successResponse('Media uploaded', data));
  } catch (err) {
    next(err);
  }
});

router.put(
  '/media/reorder',
  validate([body('orderedIds').isArray({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await profileService.reorderMedia(
        req.user!.id,
        req.body.orderedIds.map(Number),
      );
      res.json(successResponse('Media reordered', data));
    } catch (err) {
      next(err);
    }
  },
);

router.delete(
  '/media/:id',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await profileService.deleteMedia(req.user!.id, Number(req.params.id));
      res.json(successResponse('Media deleted', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post('/verification/selfie', evaUpload.single('file'), async (req: AuthRequest, res, next) => {
  try {
    if (!req.file) {
      res.status(422).json({ success: false, message: 'Selfie required' });
      return;
    }
    const url = publicEvaUploadPath(req.file.filename);
    await profileService.submitVerification(req.user!.id, url);
    const data = await profileService.autoApprovePendingVerification(req.user!.id);
    res.json(successResponse('Verification submitted', data));
  } catch (err) {
    next(err);
  }
});

router.get('/discover', async (req: AuthRequest, res, next) => {
  try {
    const data = await discoverService.discover(
      req.user!.id,
      typeof req.query.cursor === 'string' ? req.query.cursor : undefined,
      req.query.limit ? Number(req.query.limit) : 10,
    );
    res.json(successResponse('Discover feed', data));
  } catch (err) {
    next(err);
  }
});

router.get(
  '/profiles/:id',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await discoverService.getPublicProfile(
        req.user!.id,
        Number(req.params.id),
      );
      res.json(successResponse('Profile', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/likes',
  validate([
    body('toProfileId').isInt({ min: 1 }),
    body('comment').optional().isString().isLength({ max: 280 }),
    body('targetType').optional().isIn(['PROFILE', 'PROMPT', 'INTEREST', 'PHOTO']),
  ]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await discoverService.likeProfile(req.user!.id, {
        toProfileId: Number(req.body.toProfileId),
        targetType: req.body.targetType,
        targetId: req.body.targetId != null ? Number(req.body.targetId) : undefined,
        comment: req.body.comment,
      });
      res.json(successResponse(data.matched ? 'It\'s a match' : 'Liked', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/passes',
  validate([body('toProfileId').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await discoverService.passProfile(
        req.user!.id,
        Number(req.body.toProfileId),
      );
      res.json(successResponse('Passed', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post('/likes/undo', async (req: AuthRequest, res, next) => {
  try {
    const data = await discoverService.undoLastAction(req.user!.id);
    res.json(successResponse('Undone', data));
  } catch (err) {
    next(err);
  }
});

router.get('/matches', async (req: AuthRequest, res, next) => {
  try {
    const data = await discoverService.listMatches(req.user!.id);
    res.json(successResponse('Matches', data));
  } catch (err) {
    next(err);
  }
});

router.get('/conversations', async (req: AuthRequest, res, next) => {
  try {
    const data = await chatService.listConversations(req.user!.id);
    res.json(successResponse('Conversations', data));
  } catch (err) {
    next(err);
  }
});

router.get(
  '/conversations/:id/messages',
  validate([param('id').isInt({ min: 1 }), query('afterId').optional().isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await chatService.getMessages(
        req.user!.id,
        Number(req.params.id),
        req.query.afterId ? Number(req.query.afterId) : undefined,
      );
      res.json(successResponse('Messages', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/conversations/:id/messages',
  validate([param('id').isInt({ min: 1 }), body('body').isString().trim().isLength({ min: 1, max: 2000 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await chatService.sendMessage(
        req.user!.id,
        Number(req.params.id),
        req.body.body,
      );
      res.status(201).json(successResponse('Sent', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/conversations/:id/typing',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await chatService.setTyping(req.user!.id, Number(req.params.id));
      res.json(successResponse('Typing', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/unmatch',
  validate([body('matchId').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await chatService.unmatch(req.user!.id, Number(req.body.matchId));
      res.json(successResponse('Unmatched', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/block',
  validate([body('customerId').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await chatService.blockUser(req.user!.id, Number(req.body.customerId));
      res.json(successResponse('Blocked', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/report',
  validate([
    body('customerId').isInt({ min: 1 }),
    body('reason').isString().trim().isLength({ min: 2, max: 80 }),
  ]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await chatService.reportUser(
        req.user!.id,
        Number(req.body.customerId),
        req.body.reason,
        req.body.details,
      );
      res.json(successResponse('Reported', data));
    } catch (err) {
      next(err);
    }
  },
);

router.get('/safety', async (_req, res) => {
  res.json(successResponse('Safety Center', chatService.safetyContent()));
});

router.get('/dating-events', async (req: AuthRequest, res, next) => {
  try {
    const data = await socialService.listDatingEvents(req.user!.id);
    res.json(successResponse('Events', data));
  } catch (err) {
    next(err);
  }
});

router.get(
  '/dating-events/:id',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await socialService.getDatingEvent(req.user!.id, Number(req.params.id));
      res.json(successResponse('Event', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/dating-events/:id/interest',
  validate([
    param('id').isInt({ min: 1 }),
    body('kind').isIn(['SAVED', 'INTERESTED', 'JOINED']),
  ]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await socialService.setEventInterest(
        req.user!.id,
        Number(req.params.id),
        req.body.kind,
        req.body.discoverable !== false,
      );
      res.json(successResponse('Event interest saved', data));
    } catch (err) {
      next(err);
    }
  },
);

router.get('/rave/rooms', async (_req, res, next) => {
  try {
    const data = await socialService.listRaveRooms();
    res.json(successResponse('Rave rooms', data));
  } catch (err) {
    next(err);
  }
});

router.get(
  '/rave/rooms/:id',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await socialService.getRaveRoom(req.user!.id, Number(req.params.id));
      res.json(successResponse('Rave room', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/rave/rooms/:id/join',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await socialService.joinRaveRoom(req.user!.id, Number(req.params.id));
      res.json(successResponse('Joined rave', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/rave/rooms/:id/leave',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await socialService.leaveRaveRoom(req.user!.id, Number(req.params.id));
      res.json(successResponse('Left rave', data));
    } catch (err) {
      next(err);
    }
  },
);

router.get('/notifications', async (req: AuthRequest, res, next) => {
  try {
    const data = await socialService.listNotifications(req.user!.id);
    res.json(successResponse('Notifications', data));
  } catch (err) {
    next(err);
  }
});

router.post('/notifications/read', async (req: AuthRequest, res, next) => {
  try {
    const ids = Array.isArray(req.body.ids) ? req.body.ids.map(Number) : undefined;
    const data = await socialService.markNotificationsRead(req.user!.id, ids);
    res.json(successResponse('Marked read', data));
  } catch (err) {
    next(err);
  }
});

router.post(
  '/push-token',
  validate([body('token').isString().trim().notEmpty()]),
  async (req: AuthRequest, res, next) => {
    try {
      const data = await socialService.registerPushToken(req.user!.id, req.body.token);
      res.json(successResponse('Push token saved', data));
    } catch (err) {
      next(err);
    }
  },
);

export default router;
