#!/usr/bin/env bash
# Restore legacy PHP admin on the VPS (nginx + PostgreSQL bridge).
#
# Usage:
#   cd /var/www/saiflower-vps && bash scripts/vps-setup-admin.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HTML="/var/www/html"

echo "==> Install PHP bridge + config"
install -m 644 "$ROOT/deploy/php/pg_mysqli_bridge.php" "$HTML/includes/pg_mysqli_bridge.php"
cp "$ROOT/deploy/php/config.php" "$HTML/config.php"
chown www-data:www-data "$HTML/includes/pg_mysqli_bridge.php" "$HTML/config.php"

echo "==> Ensure uploads path is shared with storefront"
if [ ! -L "$HTML/uploads" ] && [ -d "$ROOT/uploads" ]; then
  if [ -d "$HTML/uploads" ] && [ ! -L "$HTML/uploads" ]; then
    echo "    keeping existing $HTML/uploads directory"
  else
    ln -sfn "$ROOT/uploads" "$HTML/uploads"
  fi
fi

echo "==> Install nginx site config"
ln -sf "$ROOT/deploy/nginx/saiflower.com.conf" /etc/nginx/sites-enabled/saiflower.com
nginx -t
systemctl reload nginx

echo "==> Smoke test PHP admin login page"
CODE="$(curl -s -o /dev/null -w '%{http_code}' https://saiflower.com/admin/login.php || true)"
echo "    GET /admin/login.php => HTTP $CODE"

echo "Done. Open https://saiflower.com/admin/ and sign in with your admin_users credentials."
