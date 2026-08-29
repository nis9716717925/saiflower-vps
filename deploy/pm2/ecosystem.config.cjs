/**
 * PM2 process file for SaiFlower on the VPS.
 *
 * Usage:
 *   cd /var/www/saiflower-vps
 *   pm2 start deploy/pm2/ecosystem.config.cjs
 *   pm2 save
 *
 * Later deploys:
 *   bash scripts/vps-redeploy-web.sh
 */
module.exports = {
  apps: [
    {
      name: 'saiflower-web',
      cwd: '/var/www/saiflower-vps/apps/web',
      script: 'npm',
      args: 'run start -- -p 3000',
      instances: 1,
      exec_mode: 'fork',
      max_memory_restart: '512M',
      env: {
        NODE_ENV: 'production',
        PORT: '3000',
      },
    },
    {
      name: 'saiflower-api',
      cwd: '/var/www/saiflower-vps/apps/server',
      script: 'npm',
      args: 'run start',
      instances: 2,
      exec_mode: 'cluster',
      max_memory_restart: '512M',
      env: {
        NODE_ENV: 'production',
        PORT: '4000',
      },
    },
  ],
};
