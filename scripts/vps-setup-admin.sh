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

echo "==> Patch legacy mysqli_* calls for PostgreSQL bridge"
bash "$ROOT/scripts/patch-php-admin-mysqli.sh" "$HTML"

echo "==> Install homepage slider admin pages"
if [ -d "$ROOT/deploy/php/admin" ]; then
  install -d "$HTML/admin/actions"
  install -m 644 "$ROOT/deploy/php/admin/"*.php "$HTML/admin/" 2>/dev/null || true
  install -m 644 "$ROOT/deploy/php/admin/actions/"*.php "$HTML/admin/actions/" 2>/dev/null || true
  if [ -f "$ROOT/deploy/php/admin/includes/image_handler.php" ]; then
    install -d "$HTML/admin/includes"
    install -m 644 "$ROOT/deploy/php/admin/includes/image_handler.php" "$HTML/admin/includes/image_handler.php"
    chown www-data:www-data "$HTML/admin/includes/image_handler.php"
  fi
  chown -R www-data:www-data "$HTML/admin/homepage-slides.php" "$HTML/admin/add-homepage-slide.php" "$HTML/admin/edit-homepage-slide.php" "$HTML/admin/actions/add_homepage_slide.php" "$HTML/admin/actions/update_homepage_slide.php" "$HTML/admin/actions/delete_homepage_slide.php" "$HTML/admin/actions/toggle_homepage_slide.php" 2>/dev/null || true
  bash "$ROOT/scripts/patch-admin-sidebar-sliders.sh" "$HTML/admin/partials/sidebar.php"
fi

echo "==> Ensure uploads path is shared with storefront"
if [ -L "$HTML/uploads" ]; then
  echo "    uploads already symlinked"
elif [ -d "$ROOT/uploads" ]; then
  if [ -d "$HTML/uploads" ] && [ ! -L "$HTML/uploads" ]; then
    echo "    replacing $HTML/uploads with symlink to $ROOT/uploads"
    rm -rf "$HTML/uploads"
  fi
  ln -sfn "$ROOT/uploads" "$HTML/uploads"
  chown -h www-data:www-data "$HTML/uploads" 2>/dev/null || true
fi

echo "==> Fix uploads permissions for PHP admin writes"
if [ -d "$ROOT/uploads" ]; then
  chown -R www-data:www-data "$ROOT/uploads"
  find "$ROOT/uploads" -type d -exec chmod 775 {} +
  find "$ROOT/uploads" -type f -exec chmod 664 {} +
fi

echo "==> Install nginx site config"
rm -f /etc/nginx/sites-enabled/saiflower.conf
ln -sf "$ROOT/deploy/nginx/saiflower.com.conf" /etc/nginx/sites-enabled/saiflower.com
nginx -t
systemctl reload nginx

echo "==> Smoke test PHP admin login page"
CODE="$(curl -s -o /dev/null -w '%{http_code}' https://saiflower.com/admin/login.php || true)"
echo "    GET /admin/login.php => HTTP $CODE"

echo "Done. Open https://saiflower.com/admin/ and sign in with your admin_users credentials."
