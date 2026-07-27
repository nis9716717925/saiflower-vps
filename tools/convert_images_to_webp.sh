#!/usr/bin/env bash
# Convert JPG/PNG images to WebP in-place (creates .webp alongside originals)
# Requires: cwebp (from libwebp)
# Usage: ./convert_images_to_webp.sh /path/to/project/uploads /path/to/project/assets/images

set -euo pipefail
QUALITY=80
MIN_SIZE_KB=20  # only convert files larger than this (skip tiny images)

if ! command -v cwebp >/dev/null 2>&1; then
  echo "Error: cwebp is required. Install it (macOS: brew install webp)"
  exit 2
fi

if [ "$#" -lt 1 ]; then
  echo "Usage: $0 <dir> [dir ...]"
  exit 1
fi

for DIR in "$@"; do
  echo "Scanning $DIR for JPG/PNG files..."
  find "$DIR" -type f \( -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" \) | while read -r img; do
    size_kb=$(( $(stat -f%z "$img") / 1024 ))
    webp_path="${img%.*}.webp"
    if [ -f "$webp_path" ]; then
      # already converted - skip
      continue
    fi
    if [ "$size_kb" -lt "$MIN_SIZE_KB" ]; then
      # skip small images
      continue
    fi
    echo "Converting: $img -> $webp_path (quality=$QUALITY)"
    cwebp -q $QUALITY "$img" -o "$webp_path" >/dev/null 2>&1 || echo "Failed: $img"
  done
done

echo "Done. WebP files produced alongside originals. Review results before removing originals."
