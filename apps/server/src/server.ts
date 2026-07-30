import app from './app';
import { config } from './config';

const server = app.listen(config.port, '0.0.0.0', () => {
  console.log(`SaiFlower API listening on :${config.port}`);
  console.log(`API base: http://localhost:${config.port}${config.apiPrefix}`);
  console.log(`Health:   http://localhost:${config.port}/health`);
  console.log(`Checkout mode: ${config.checkout.mode}`);
});

const shutdown = (signal: string) => {
  console.log(`${signal} received — shutting down`);
  server.close(() => process.exit(0));
};

process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('SIGINT', () => shutdown('SIGINT'));
