import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import { config } from './config';
import { globalRateLimiter } from './middleware/rateLimiter';
import { sanitizeBody } from './middleware/sanitize';
import { errorHandler, notFoundHandler } from './middleware/errorHandler';
import routes from './routes';

const app = express();

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

app.get('/health', async (_req, res) => {
  const { pingDb, tableCounts } = await import('./db/client');
  const dbOk = await pingDb();
  const counts = dbOk ? await tableCounts().catch(() => undefined) : undefined;
  let databaseHost = 'unset';
  try {
    databaseHost = new URL(config.database.url).host || 'invalid';
  } catch {
    databaseHost = 'invalid';
  }
  res.status(dbOk ? 200 : 503).json({
    status: dbOk ? 'ok' : 'degraded',
    service: 'saiflower-server',
    checkoutMode: config.checkout.mode,
    database: dbOk ? 'up' : 'down',
    databaseHost,
    ...(counts ? { tableCounts: counts } : {}),
    timestamp: new Date().toISOString(),
  });
});

app.use(config.apiPrefix, routes);

app.use(notFoundHandler);
app.use(errorHandler);

export default app;
