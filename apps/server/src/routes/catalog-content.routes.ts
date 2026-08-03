import { Router } from 'express';
import * as content from '../services/catalog-content.service';
import { successResponse } from '../utils/response';

const galleryRouter = Router();
const eventsRouter = Router();

galleryRouter.get('/', async (req, res, next) => {
  try {
    const limit = req.query.limit ? Number(req.query.limit) : 100;
    const items = await content.listGallery(limit);
    res.json(successResponse('Gallery retrieved', items));
  } catch (err) {
    next(err);
  }
});

galleryRouter.get('/:id', async (req, res, next) => {
  try {
    const id = Number(req.params.id);
    if (!Number.isFinite(id)) {
      res.status(400).json({ success: false, message: 'Invalid gallery id' });
      return;
    }
    const item = await content.getGalleryItem(id);
    res.json(successResponse('Gallery item retrieved', item));
  } catch (err) {
    next(err);
  }
});

eventsRouter.get('/', async (req, res, next) => {
  try {
    const limit = req.query.limit ? Number(req.query.limit) : 100;
    const items = await content.listEvents(limit);
    res.json(successResponse('Events retrieved', items));
  } catch (err) {
    next(err);
  }
});

eventsRouter.get('/:slug', async (req, res, next) => {
  try {
    const item = await content.getEventBySlug(req.params.slug);
    res.json(successResponse('Event retrieved', item));
  } catch (err) {
    next(err);
  }
});

export { galleryRouter, eventsRouter };
