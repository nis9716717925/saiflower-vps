"""Strip the baked-in white backdrop from the header logo.

The live logo (uploads/logo_transparent.png) ships with an opaque near-white
background, which shows as a white plate on the glass header. The artwork is
gold-only, so every near-white pixel (including the regions enclosed by the
petal and script strokes) becomes transparent, ramped so anti-aliased edges
stay smooth. The result is trimmed to the artwork bounds.
"""

import sys

from PIL import Image

SRC, DST = sys.argv[1], sys.argv[2]
FULL_WHITE = 246  # min-channel value treated as pure background
EDGE_WHITE = 170  # below this, pixel is fully opaque artwork

im = Image.open(SRC).convert("RGBA")
w, h = im.size
px = im.load()

for y in range(h):
    for x in range(w):
        r, g, b, a = px[x, y]
        v = min(r, g, b)
        if v >= FULL_WHITE:
            alpha = 0
        elif v <= EDGE_WHITE:
            continue
        else:
            alpha = round(255 * (FULL_WHITE - v) / (FULL_WHITE - EDGE_WHITE))
        px[x, y] = (r, g, b, min(a, alpha))

bbox = im.getbbox()
im = im.crop(bbox)
im.save(DST, optimize=True)
print(f"cropped to {im.size} from {(w, h)} bbox={bbox}")
