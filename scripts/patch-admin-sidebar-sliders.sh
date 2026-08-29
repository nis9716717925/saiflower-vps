#!/usr/bin/env bash
# Add Homepage Slider nav link to PHP admin sidebar if missing.
set -euo pipefail

SIDEBAR="${1:-/var/www/html/admin/partials/sidebar.php}"

if [ ! -f "$SIDEBAR" ]; then
  echo "Sidebar not found: $SIDEBAR"
  exit 0
fi

if grep -q 'homepage-slides.php' "$SIDEBAR"; then
  echo "Sidebar already has homepage slider link"
  exit 0
fi

python3 - <<'PY' "$SIDEBAR"
import sys
path = sys.argv[1]
needle = '<a href="gallery.php"'
insert = '''        <a href="homepage-slides.php" class="<?= $page=='homepage-slides.php'||$page=='add-homepage-slide.php'||$page=='edit-homepage-slide.php'?'active':'' ?>">
            <i class="fas fa-images"></i> <span>Homepage Sliders</span>
        </a>
'''
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()
if needle not in content:
    raise SystemExit('Could not find gallery nav anchor to patch sidebar')
content = content.replace(needle, insert + needle, 1)
with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
print('Patched sidebar with Homepage Sliders link')
PY
