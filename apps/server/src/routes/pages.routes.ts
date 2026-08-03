import { Router } from 'express';
import * as pages from '../services/dynamic-page.service';
import { successResponse } from '../utils/response';

const pagesRouter = Router();

pagesRouter.get('/', async (req, res, next) => {
  try {
    const limit = req.query.limit ? Number(req.query.limit) : 200;
    const items = await pages.listDynamicPages(limit);
    res.json(successResponse('Pages retrieved', items));
  } catch (err) {
    next(err);
  }
});

pagesRouter.get('/:slug', async (req, res, next) => {
  try {
    const page = await pages.getDynamicPageBySlug(req.params.slug);
    res.json(successResponse('Page retrieved', page));
  } catch (err) {
    next(err);
  }
});

export { pagesRouter };
