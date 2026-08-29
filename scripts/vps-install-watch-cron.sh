#!/usr/bin/env bash
# Install the chunk-health watchdog cron (idempotent).
# Usage: sudo bash scripts/vps-install-watch-cron.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WATCH="$ROOT/scripts/vps-watch-static.sh"
LOG="/var/log/saiflower-chunk-watch.log"
CRON_LINE="*/5 * * * * $WATCH >> $LOG 2>&1"

touch "$LOG"
chmod 644 "$LOG" 2>/dev/null || true

existing="$(crontab -l 2>/dev/null || true)"
if echo "$existing" | grep -Fq "$WATCH"; then
  echo "Cron already installed for vps-watch-static.sh"
  exit 0
fi

{
  echo "$existing" | sed '/vps-watch-static\.sh/d'
  echo "$CRON_LINE"
} | crontab -

echo "Installed cron: $CRON_LINE"
crontab -l | grep vps-watch-static || true
