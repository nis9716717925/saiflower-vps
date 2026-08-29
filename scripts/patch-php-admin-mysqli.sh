#!/usr/bin/env bash
# Rewrite legacy mysqli_* procedural calls to PgMysqli object methods.
# Safe to re-run: skips files already converted.
set -euo pipefail

HTML="${1:-/var/www/html}"

patch_tree() {
  local dir="$1"
  [ -d "$dir" ] || return 0

  while IFS= read -r -d '' file; do
    if ! grep -qE 'mysqli_(query|fetch_assoc|num_rows|error|real_escape_string|report)\(' "$file"; then
      continue
    fi

    sed -i \
      -e 's/mysqli_query(\$conn, /$conn->query(/g' \
      -e 's/mysqli_fetch_assoc(\$\([a-zA-Z_][a-zA-Z0-9_]*\))/\$\1->fetch_assoc()/g' \
      -e 's/mysqli_num_rows(\$\([a-zA-Z_][a-zA-Z0-9_]*\))/\$\1->num_rows/g' \
      -e 's/mysqli_error(\$conn)/$conn->getError()/g' \
      -e 's/mysqli_real_escape_string(\$conn, /$conn->real_escape_string(/g' \
      -e 's/mysqli_report([^;]*);//g' \
      "$file"
  done < <(find "$dir" -name '*.php' -print0)
}

echo "==> Patch PHP admin mysqli calls under $HTML"
patch_tree "$HTML/admin"
patch_tree "$HTML/admin/actions"
