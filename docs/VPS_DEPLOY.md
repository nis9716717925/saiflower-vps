# SaiFlower VPS deploy (permanent JS chunk safety)

## Why the site kept crashing

`next build` **deletes and rewrites** `apps/web/.next` in place. If PM2/nginx are
still serving that folder during the build, HTML can reference chunk hashes that
are not on disk yet (or were deleted). Browsers then download HTML **400** pages
instead of JS and show:

> Application error: a client-side exception has occurred…

The old redeploy script also resolved `ROOT` with `../..` (outside the repo), so
some “redeploys” never rebuilt the real app tree.

## Permanent rules

1. **Never build in place while live.** Use `bash scripts/vps-redeploy-web.sh`
   (builds into `.next-build`, verifies, then atomically swaps to `.next`).
2. **Nginx must serve** `/_next/static/` from disk via
   `deploy/nginx/next-static.conf` **before** `location /`. Missing files must
   return **404**, not be proxied to Node (Node returns HTML 400).
3. **Verify before reload.** `tools/deploy/verify-next-static.mjs` +
   `tools/deploy/probe-live-chunks.mjs` must pass.
4. **Use PM2 names** `saiflower-web` / `saiflower-api` from
   `deploy/pm2/ecosystem.config.cjs`.

## First-time / recovery (Kodee / SSH)

```bash
cd /var/www/saiflower-vps
git pull --ff-only origin main

# Nginx (once): include next-static.conf inside the saiflower.com server block
# BEFORE location /, then:
nginx -t && systemctl reload nginx

# PM2 (once, if not already using these names):
pm2 start deploy/pm2/ecosystem.config.cjs
pm2 save

# Every deploy / when JS is broken:
bash scripts/vps-redeploy-web.sh

# Optional watchdog every 5 minutes (auto-redeploy after 2 failed chunk probes):
sudo bash scripts/vps-install-watch-cron.sh
```

## Manual health checks

```bash
node tools/deploy/probe-live-chunks.mjs
node tools/deploy/probe-live-chunks.mjs --origin=http://127.0.0.1:3000
curl -sI https://saiflower.com/api/v1/health
```

## Long-term recommendations

| Option | Benefit | Notes |
|--------|---------|--------|
| Keep atomic VPS deploys (current) | Stops in-place build races | Lowest cost; already in-repo |
| Cron watchdog (`vps-watch-static.sh`) | Auto-heals within minutes | Enable after first successful redeploy |
| Release folders (`/var/www/releases/<id>` + symlink) | Easy rollback | Next step if you outgrow single `.next` swap |
| Put hashed `/_next/static` on Cloudflare/R2 CDN | Global cache, less VPS load | Still need atomic HTML↔chunk consistency |
| Host storefront on Vercel + API on VPS | Platform handles asset deploy | Bigger architecture move |

**Do not** run `rm -rf apps/web/.next && npm run build` on the live server outside
the atomic script. That is what caused the repeated outages.
