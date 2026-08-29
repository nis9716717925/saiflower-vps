import express from 'express';
import compression from 'compression';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import { config } from './config';
import { globalRateLimiter } from './middleware/rateLimiter';
import { sanitizeBody } from './middleware/sanitize';
import { errorHandler, notFoundHandler } from './middleware/errorHandler';
import routes from './routes';
import { countUploadFilesSync, resolveUploadsDirectory } from './utils/uploads';

const app = express();
const uploadsDir = resolveUploadsDirectory();

// So express-rate-limit keys by real client IP from nginx X-Forwarded-For,
// instead of collapsing every visitor into 127.0.0.1.
app.set('trust proxy', 1);

app.use(compression({ threshold: 1024 }));
app.use(helmet());
app.use(
  cors({
    origin: (origin, callback) => {
      if (!origin || config.cors.origins.includes(origin) || !config.isProduction) {
        callback(null, true);
      } else {
        callback(new Error('Not allowed by CORS'));
      }
    },
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Guest-Id', 'X-CSRF-Token'],
    exposedHeaders: ['X-Guest-Id'],
  }),
);
app.use(express.json({ limit: '1mb' }));
app.use(express.urlencoded({ extended: true }));
app.use(sanitizeBody);
app.use(morgan(config.isProduction ? 'combined' : 'dev'));
app.use(globalRateLimiter);

// Product/category images — canonical store: UPLOADS_DIR or /var/www/saiflower-vps/uploads
app.use(
  '/uploads',
  express.static(uploadsDir, {
    maxAge: config.isProduction ? '30d' : 0,
    etag: true,
    lastModified: true,
    fallthrough: false,
    setHeaders(res, filePath) {
      if (filePath.endsWith('.webp')) res.setHeader('Content-Type', 'image/webp');
      else if (filePath.endsWith('.avif')) res.setHeader('Content-Type', 'image/avif');
      // Long-lived immutable cache for hashed/upload filenames (content-addressed enough in practice)
      if (config.isProduction) {
        res.setHeader(
          'Cache-Control',
          'public, max-age=2592000, stale-while-revalidate=86400',
        );
      }
    },
  }),
);

app.get('/health', async (_req, res) => {
  const { pingDb, tableCounts } = await import('./db/client');
  const dbOk = await pingDb();
  const counts = dbOk ? await tableCounts().catch(() => undefined) : undefined;
  const uploadFiles = countUploadFilesSync();
  res.status(dbOk ? 200 : 503).json({
    status: dbOk ? 'ok' : 'degraded',
    service: 'saiflower-server',
    checkoutMode: config.checkout.mode,
    database: dbOk ? 'up' : 'down',
    uploadsDir,
    uploadFiles,
    ...(uploadFiles === 0 ? { uploadsWarning: 'No image files on disk — copy legacy uploads folder to UPLOADS_DIR' } : {}),
    ...(counts ? { tableCounts: counts } : {}),
    timestamp: new Date().toISOString(),
  });
});

app.use(config.apiPrefix, routes);

app.use(notFoundHandler);
app.use(errorHandler);

export default app;
