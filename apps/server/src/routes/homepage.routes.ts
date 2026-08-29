import { Router } from 'express';
import * as homepage from '../services/homepage.service';
import { successResponse } from '../utils/response';

const homepageRouter = Router();

homepageRouter.get('/slides', async (_req, res, next) => {
  try {
    const slides = await homepage.listHomepageSlides();
    res.json(successResponse('Homepage slides retrieved', slides));
  } catch (err) {
    next(err);
  }
});

export { homepageRouter };
