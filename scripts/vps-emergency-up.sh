#!/usr/bin/env bash
# Emergency bring-up when saiflower.com returns nginx 502 Bad Gateway.
# Restores Node upstreams first, then rebuilds only if needed.
#
# Usage on VPS:
#   cd /var/www/saiflower-vps && bash scripts/vps-emergency-up.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

WEB_PORT="${WEB_PORT:-3000}"
API_PORT="${API_PORT:-4000}"

echo "==> Repo: $ROOT"
echo "==> Date: $(date -Is)"

echo "==> PM2 status"
if command -v pm2 >/dev/null 2>&1; then
  pm2 list || true
else
  echo "ERROR: pm2 not installed"
  exit 1
fi

probe_local() {
  local port="$1"
  curl -s -o /dev/null -w '%{http_code}' --max-time 3 "http://127.0.0.1:${port}/" || echo "000"
}

echo "==> Local probes (before)"
echo "    :${WEB_PORT} => $(probe_local "$WEB_PORT")"
echo "    :${API_PORT} => $(probe_local "$API_PORT")"

echo "==> Listening ports"
ss -lntp 2>/dev/null | grep -E ":${WEB_PORT}|:${API_PORT}" || netstat -lntp 2>/dev/null | grep -E ":${WEB_PORT}|:${API_PORT}" || true

start_or_restart() {
  local name="$1"
  if pm2 describe "$name" >/dev/null 2>&1; then
    pm2 restart "$name" --update-env || pm2 restart "$name"
  else
    echo "    $name not in pm2 — will start from ecosystem if present"
  fi
}

echo "==> Restart known processes"
start_or_restart saiflower-web
start_or_restart saiflower-api
start_or_restart web
start_or_restart server
start_or_restart next
start_or_restart api

# If named apps missing, start ecosystem
if [ -f "$ROOT/deploy/pm2/ecosystem.config.cjs" ]; then
  echo "==> Ensure ecosystem apps exist"
  pm2 startOrReload "$ROOT/deploy/pm2/ecosystem.config.cjs" --update-env || pm2 start "$ROOT/deploy/pm2/ecosystem.config.cjs" || true
fi

# Last resort: restart everything pm2 knows
pm2 restart all --update-env || true
pm2 save || true
sleep 3

echo "==> Local probes (after restart)"
WEB_CODE="$(probe_local "$WEB_PORT")"
API_CODE="$(probe_local "$API_PORT")"
echo "    :${WEB_PORT} => ${WEB_CODE}"
echo "    :${API_PORT} => ${API_CODE}"

NEED_BUILD=0
if [ ! -f "$ROOT/apps/web/.next/BUILD_ID" ]; then
  echo "WARN: apps/web/.next/BUILD_ID missing"
  NEED_BUILD=1
fi
if [ -z "$(find "$ROOT/apps/web/.next/static/chunks/app" -name 'layout-*.js' 2>/dev/null | head -1)" ]; then
  echo "WARN: layout chunk missing on disk"
  NEED_BUILD=1
fi
if [ "$WEB_CODE" = "000" ] || [ "$WEB_CODE" = "502" ]; then
  echo "WARN: web still not responding locally"
  NEED_BUILD=1
fi

if [ "$NEED_BUILD" = "1" ]; then
  echo "==> Running atomic redeploy (build + swap + reload)"
  bash "$ROOT/scripts/vps-redeploy-web.sh"
else
  echo "==> Build looks present; skipping full rebuild"
fi

sleep 2
echo "==> Final local probes"
echo "    :${WEB_PORT} => $(probe_local "$WEB_PORT")"
echo "    :${API_PORT} => $(probe_local "$API_PORT")"

echo "==> Nginx check"
if command -v nginx >/dev/null 2>&1; then
  nginx -t 2>&1 || true
fi

echo "==> Public probe (best-effort)"
curl -sI --max-time 8 https://saiflower.com/ | head -5 || true
curl -sI --max-time 8 https://saiflower.com/api/v1/health | head -5 || true

echo "==> PM2 logs (last 40 lines each)"
pm2 logs saiflower-web --lines 40 --nostream 2>/dev/null || pm2 logs --lines 40 --nostream || true

echo "Done. If public still 502 while localhost:3000 works, fix nginx upstream to 127.0.0.1:${WEB_PORT}."
