# Cloudflare CDN setup for saiflower.com

Putting Cloudflare in front of the VPS cuts global latency and offloads static assets from the origin.

## 1. DNS (Cloudflare dashboard)

1. Add `saiflower.com` to Cloudflare.
2. Point `A` / `AAAA` records for `@` and `www` to the VPS IP (`200.141.1.201`).
3. Enable the **orange cloud** (proxied) for both hostnames.
4. SSL/TLS mode: **Full (strict)** (Let’s Encrypt on nginx is already configured).

## 2. Origin nginx (once on VPS)

Include real client IPs when traffic is proxied:

```bash
cd /var/www/saiflower-vps && sudo bash scripts/vps-install-cloudflare-nginx.sh
```

Or manually:

```bash
# In /etc/nginx/nginx.conf http { } block:
include /var/www/saiflower-vps/deploy/nginx/cloudflare.conf;
sudo nginx -t && sudo systemctl reload nginx
```

## 2b. Responsive upload variants (Phase 4)

After new product images land in `uploads/`, generate width variants for srcset:

```bash
cd /var/www/saiflower-vps && bash scripts/vps-generate-image-variants.sh
```

Creates `image-w320.webp`, `image-w640.webp`, etc. next to each source WebP.

## 3. Cache rules (recommended)

Create **Cache Rules** in Cloudflare (order matters):

| URL pattern | Cache | Edge TTL | Notes |
|-------------|-------|----------|-------|
| `/_next/static/*` | Cache everything | 1 year | Hashed filenames — safe immutable |
| `/uploads/*` | Cache everything | 30 days | Product images |
| `/assets/*` | Cache everything | 7 days | Legacy CSS/JS/images |
| `/api/*` | Bypass | — | Cart, auth, checkout |
| `/cart`, `/checkout`, `/login`, `/register`, `/profile` | Bypass | — | User-specific pages |

For HTML on catalog pages (optional, after verifying ISR):

- Cache Rule: `/*` → **Eligible for cache**, **Respect origin `Cache-Control`**
- Origin sends `Cache-Control: public, s-maxage=120, stale-while-revalidate=600` on public catalog pages (Phase 5 middleware). Cart/checkout/login bypass cache.

## 4. Speed optimizations (Cloudflare dashboard)

- **Brotli**: On  
- **Auto Minify**: CSS + JS (HTML optional — Next already minifies)  
- **HTTP/2 + HTTP/3**: On  
- **Early Hints**: On (helps fonts/CSS)  

## 5. Verify

```bash
curl -sI https://saiflower.com/_next/static/chunks/webpack-*.js | grep -i cf-cache
curl -sI https://saiflower.com/uploads/ | head
```

`cf-cache-status: HIT` on static assets after the second request means CDN is working.

## 6. Purge after deploy

After `vps-redeploy-web.sh`, purge only if needed:

- Cloudflare → **Caching** → **Purge Everything** (rare), or  
- Purge `/_next/static/*` if HTML references old chunk hashes (atomic deploy usually avoids this).

The in-repo **chunk watchdog cron** (`vps-install-watch-cron.sh`) still auto-heals origin if chunks go missing.
