#!/usr/bin/env bash
# Full Next.js redeploy on the VPS — fixes missing /_next/static chunks (400 errors).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

echo "==> Pull latest code"
git pull --ff-only origin main

echo "==> Install dependencies"
npm ci

echo "==> Build shared packages + web"
npm run build:web

echo "==> Verify critical static chunks exist"
LAYOUT_CHUNK="$(find apps/web/.next/static/chunks/app -name 'layout-*.js' 2>/dev/null | head -1 || true)"
if [ -z "$LAYOUT_CHUNK" ]; then
  echo "ERROR: layout chunk missing after build — aborting."
  exit 1
fi
echo "    layout chunk: $LAYOUT_CHUNK"

echo "==> Verify static chunks"
node tools/deploy/verify-next-static.mjs

echo "==> Restart Next.js (pm2)"
if command -v pm2 >/dev/null 2>&1; then
  pm2 restart saiflower-web --update-env 2>/dev/null || pm2 restart web --update-env 2>/dev/null || pm2 restart all --update-env
  pm2 save
else
  echo "WARN: pm2 not found — restart Next.js manually: npm run start -w @saiflower/web"
fi

echo "==> Restart API (pm2) so Google OAuth env is loaded"
if command -v pm2 >/dev/null 2>&1; then
  pm2 restart saiflower-api --update-env 2>/dev/null || pm2 restart server --update-env 2>/dev/null || true
fi

echo "Done. Test: https://saiflower.com/login"
