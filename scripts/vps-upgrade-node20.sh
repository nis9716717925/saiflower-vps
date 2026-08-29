#!/usr/bin/env bash
# Upgrade Node.js to 20.x LTS on Ubuntu VPS (run once; requires root).
set -euo pipefail

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Run with sudo: sudo bash scripts/vps-upgrade-node20.sh"
  exit 1
fi

echo "==> Current node: $(node -v 2>/dev/null || echo missing)"

if command -v node >/dev/null && node -v | grep -q '^v20\.'; then
  echo "Node 20 already installed."
  exit 0
fi

echo "==> Install NodeSource Node 20.x"
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

echo "==> Verify"
node -v
npm -v

echo "Done. Re-run deploy: cd /var/www/saiflower-vps && bash scripts/vps-redeploy-web.sh"
