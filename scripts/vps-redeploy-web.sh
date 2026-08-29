#!/usr/bin/env bash
# Atomic VPS redeploy for SaiFlower web + API.
# Builds into apps/web/.next-build while live apps/web/.next keeps serving,
# verifies chunks, swaps directories, then reloads pm2.
#
# Usage (on VPS):
#   cd /var/www/saiflower-vps && bash scripts/vps-redeploy-web.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

WEB_DIR="$ROOT/apps/web"
LIVE_DIR="$WEB_DIR/.next"
BUILD_DIR="$WEB_DIR/.next-build"
PREV_DIR="$WEB_DIR/.next-prev"
WEB_PORT="${WEB_PORT:-3000}"
PUBLIC_ORIGIN="${PUBLIC_ORIGIN:-https://saiflower.com}"

echo "==> Repo root: $ROOT"

echo "==> Pull latest code"
BEFORE_HEAD="$(git rev-parse HEAD)"
git pull --ff-only origin main
AFTER_HEAD="$(git rev-parse HEAD)"
if [ "$BEFORE_HEAD" != "$AFTER_HEAD" ] && git diff --name-only "$BEFORE_HEAD" "$AFTER_HEAD" -- "$0" | grep -q .; then
  echo "==> Deploy script updated — re-running with latest version"
  exec bash "$0" "$@"
fi

echo "==> Install dependencies"
npm ci

echo "==> Copy self-hosted icon fonts to /assets/vendor"
npm run vendor:fonts

echo "==> Build API + packages"
npm run build:server

echo "==> Atomic web build into .next-build (live .next untouched)"
rm -rf "$BUILD_DIR" "$WEB_DIR/.next/types"
export NEXT_DIST_DIR=.next-build
npm run build:packages
npm run build -w @saiflower/web
unset NEXT_DIST_DIR

echo "==> Verify build output"
node tools/deploy/verify-next-static.mjs --dir=apps/web/.next-build

LAYOUT_CHUNK="$(find "$BUILD_DIR/static/chunks/app" -name 'layout-*.js' 2>/dev/null | head -1 || true)"
if [ -z "$LAYOUT_CHUNK" ]; then
  echo "ERROR: layout chunk missing in .next-build — aborting (live site unchanged)."
  exit 1
fi
LAYOUT_NAME="$(basename "$LAYOUT_CHUNK")"
echo "    layout chunk: $LAYOUT_NAME"

echo "==> Atomic swap .next-build -> .next"
rm -rf "$PREV_DIR"
if [ -d "$LIVE_DIR" ]; then
  mv "$LIVE_DIR" "$PREV_DIR"
fi
mv "$BUILD_DIR" "$LIVE_DIR"

rollback() {
  echo "==> ROLLBACK: restoring previous .next"
  if [ -d "$PREV_DIR" ]; then
    rm -rf "$LIVE_DIR"
    mv "$PREV_DIR" "$LIVE_DIR"
  fi
}

echo "==> Reload pm2 processes"
if command -v pm2 >/dev/null 2>&1; then
  if [ -f "$ROOT/deploy/pm2/ecosystem.config.cjs" ]; then
    pm2 startOrReload "$ROOT/deploy/pm2/ecosystem.config.cjs" --update-env || true
  fi
  pm2 restart saiflower-web --update-env 2>/dev/null \
    || pm2 restart web --update-env 2>/dev/null \
    || pm2 restart all --update-env
  pm2 restart saiflower-api --update-env 2>/dev/null \
    || pm2 restart server --update-env 2>/dev/null \
    || true
  pm2 save
  pm2 list
else
  echo "WARN: pm2 not found — start Next manually from $ROOT"
fi

echo "==> Wait for local server"
sleep 4

echo "==> Probe local chunks"
if ! node tools/deploy/probe-live-chunks.mjs --origin="http://127.0.0.1:${WEB_PORT}"; then
  echo "ERROR: local chunk probe failed after swap."
  rollback
  if command -v pm2 >/dev/null 2>&1; then
    pm2 restart saiflower-web --update-env 2>/dev/null || pm2 restart all --update-env || true
  fi
  exit 1
fi

echo "==> Probe public site"
if ! node tools/deploy/probe-live-chunks.mjs --origin="$PUBLIC_ORIGIN"; then
  echo "WARN: public probe failed. Live local build is OK."
  echo "      Check nginx includes deploy/nginx/next-static.conf BEFORE location /"
  echo "      alias must be: $LIVE_DIR/static/"
  echo "      Then: nginx -t && systemctl reload nginx"
  echo "      Re-test: node tools/deploy/probe-live-chunks.mjs"
else
  echo "==> Cleaning previous build"
  rm -rf "$PREV_DIR"
fi

API_STATUS="$(curl -s -o /dev/null -w '%{http_code}' "${PUBLIC_ORIGIN}/api/v1/health" || true)"
echo "    public API health HTTP ${API_STATUS}"

echo "Done. Hard refresh https://saiflower.com/ (Ctrl+Shift+R)."
