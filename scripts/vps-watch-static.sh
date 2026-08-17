#!/usr/bin/env bash
# Lightweight cron probe for production JS health.
# Install (as root):
#   */5 * * * * /var/www/saiflower-vps/scripts/vps-watch-static.sh >> /var/log/saiflower-chunk-watch.log 2>&1
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

ORIGIN="${PUBLIC_ORIGIN:-https://saiflower.com}"
STATE_DIR="${TMPDIR:-/tmp}/saiflower-watch"
mkdir -p "$STATE_DIR"
FAIL_FILE="$STATE_DIR/chunk-fail-count"

if node tools/deploy/probe-live-chunks.mjs --origin="$ORIGIN"; then
  rm -f "$FAIL_FILE"
  exit 0
fi

fails=0
if [ -f "$FAIL_FILE" ]; then
  fails="$(cat "$FAIL_FILE" 2>/dev/null || echo 0)"
fi
fails=$((fails + 1))
echo "$fails" > "$FAIL_FILE"
echo "$(date -Is) chunk probe FAILED ($fails)"

# After 2 consecutive failures, attempt one atomic redeploy (avoids flapping).
if [ "$fails" -ge 2 ]; then
  echo "$(date -Is) triggering atomic redeploy"
  bash "$ROOT/scripts/vps-redeploy-web.sh" || true
  rm -f "$FAIL_FILE"
fi
