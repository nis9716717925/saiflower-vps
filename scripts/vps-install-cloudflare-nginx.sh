#!/usr/bin/env bash
# Wire Cloudflare real-IP headers into nginx (run once on VPS when proxied).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CF_SNIPPET="$ROOT/deploy/nginx/cloudflare.conf"
NGINX_CONF="/etc/nginx/nginx.conf"

if [[ ! -f "$CF_SNIPPET" ]]; then
  echo "Missing $CF_SNIPPET"
  exit 1
fi

if grep -q 'deploy/nginx/cloudflare.conf' "$NGINX_CONF" 2>/dev/null; then
  echo "cloudflare.conf already included in $NGINX_CONF"
else
  echo "==> Adding Cloudflare real-IP include to $NGINX_CONF"
  sudo sed -i "/^http {/a\\    include $CF_SNIPPET;" "$NGINX_CONF"
fi

sudo nginx -t
sudo systemctl reload nginx
echo "Done. See docs/CLOUDFLARE_CDN.md for DNS + cache rules."
