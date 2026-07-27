import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import swaggerUi from 'swagger-ui-express';
import { config } from './config';
import routes from './routes';
import { openApiSpec } from './docs/openapi';
import { globalRateLimiter } from './middleware/rateLimiter';
import { maintenanceMode } from './middleware/maintenance';
import { sanitizeBody } from './middleware/sanitize';
import { errorHandler, notFoundHandler } from './middleware/errorHandler';

const app = express();

app.use(helmet());
app.use(cors({
  origin: (origin, callback) => {
    if (!origin || config.cors.origins.includes(origin) || !config.isProduction) {
      callback(null, true);
    } else {
      callback(new Error('Not allowed by CORS'));
    }
  },
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Guest-Id'],
}));
app.use(express.json({ limit: '1mb' }));
app.use(express.urlencoded({ extended: true }));
app.use(sanitizeBody);
app.use(morgan(config.isProduction ? 'combined' : 'dev'));
app.use(globalRateLimiter);
app.use(maintenanceMode);

app.get('/health', (_req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

app.use('/docs', swaggerUi.serve, swaggerUi.setup(openApiSpec, {
  customSiteTitle: 'Saiflower API Docs',
  swaggerOptions: { persistAuthorization: true },
}));

app.get('/docs.json', (_req, res) => {
  res.json(openApiSpec);
});

app.use(config.apiPrefix, routes);

app.use(notFoundHandler);
app.use(errorHandler);

export default app;
