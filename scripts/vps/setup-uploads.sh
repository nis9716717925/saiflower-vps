#!/usr/bin/env bash
# Prepare uploads directory on VPS and optionally extract a legacy archive.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
UPLOADS_DIR="${UPLOADS_DIR:-$REPO_ROOT/uploads}"
ARCHIVE="${1:-}"

echo "==> SaiFlower uploads setup"
echo "    Repo:        $REPO_ROOT"
echo "    UPLOADS_DIR: $UPLOADS_DIR"

mkdir -p "$UPLOADS_DIR"
chmod 755 "$UPLOADS_DIR"

if [[ -n "$ARCHIVE" ]]; then
  echo "==> Extracting $ARCHIVE"
  case "$ARCHIVE" in
    *.zip) unzip -o "$ARCHIVE" -d "$UPLOADS_DIR" ;;
    *.tar.gz|*.tgz) tar -xzf "$ARCHIVE" -C "$UPLOADS_DIR" --strip-components=0 ;;
    *)
      echo "Unsupported archive. Use .zip or .tar.gz"
      exit 1
      ;;
  esac
fi

# If archive contained a nested uploads/ folder, flatten it.
if [[ -d "$UPLOADS_DIR/uploads" ]]; then
  shopt -s dotglob
  mv "$UPLOADS_DIR/uploads"/* "$UPLOADS_DIR/" 2>/dev/null || true
  rmdir "$UPLOADS_DIR/uploads" 2>/dev/null || true
fi

FILE_COUNT="$(find "$UPLOADS_DIR" -type f | wc -l | tr -d ' ')"
echo "==> Upload files on disk: $FILE_COUNT"

if [[ "$FILE_COUNT" -lt 10 ]]; then
  echo "WARNING: Very few upload files found."
  echo "Copy your old PHP public_html/uploads folder into: $UPLOADS_DIR"
  exit 1
fi

# Symlink for local dev / Next public (optional)
PUBLIC_UPLOADS="$REPO_ROOT/apps/web/public/uploads"
if [[ ! -e "$PUBLIC_UPLOADS" ]]; then
  ln -s "$UPLOADS_DIR" "$PUBLIC_UPLOADS"
  echo "==> Linked $PUBLIC_UPLOADS -> $UPLOADS_DIR"
fi

echo "==> Done. Set in apps/server/.env and apps/web/.env.local:"
echo "UPLOADS_DIR=$UPLOADS_DIR"
