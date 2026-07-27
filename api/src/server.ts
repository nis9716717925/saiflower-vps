import app from './app';
import { config } from './config';
import prisma from './config/database';

const server = app.listen(config.port, '0.0.0.0', () => {
  console.log(`Saiflower API running on port ${config.port}`);
  console.log(`Environment: ${config.env}`);
  console.log(`API base: http://localhost:${config.port}${config.apiPrefix}`);
  console.log(`LAN access: use your PC IPv4 from ipconfig (e.g. http://192.168.x.x:${config.port}${config.apiPrefix})`);
  console.log(`Swagger docs: http://localhost:${config.port}/docs`);
});

const shutdown = async (signal: string) => {
  console.log(`${signal} received. Shutting down gracefully...`);
  server.close(async () => {
    await prisma.$disconnect();
    process.exit(0);
  });
};

process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('SIGINT', () => shutdown('SIGINT'));
