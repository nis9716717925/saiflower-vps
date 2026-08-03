import { Router } from 'express';
import * as blogService from '../services/blog.service';
import { successResponse } from '../utils/response';

const blogRouter = Router();
const faqRouter = Router();

blogRouter.get('/', async (req, res, next) => {
  try {
    const limit = req.query.limit ? Number(req.query.limit) : 100;
    const items = await blogService.listBlogs(limit);
    res.json(successResponse('Blogs retrieved', items));
  } catch (err) {
    next(err);
  }
});

blogRouter.get('/:slug', async (req, res, next) => {
  try {
    const blog = await blogService.getBlogBySlug(req.params.slug);
    res.json(successResponse('Blog retrieved', blog));
  } catch (err) {
    next(err);
  }
});

faqRouter.get('/', async (req, res, next) => {
  try {
    const page = typeof req.query.page === 'string' ? req.query.page : 'general';
    const limit = req.query.limit ? Number(req.query.limit) : 6;
    const items = await blogService.listFaqs(page, limit);
    res.json(successResponse('FAQs retrieved', items));
  } catch (err) {
    next(err);
  }
});

export { blogRouter, faqRouter };
