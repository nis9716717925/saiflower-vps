#!/usr/bin/env bash
# Generate -w320/-w640/etc. WebP variants for /uploads (run on VPS after new product images).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Responsive WebP variants for uploads/"
node tools/generate-responsive-variants.mjs --dir uploads
