# Product image uploads (VPS deploy)

Product images are **not in git**. They must live on the VPS at:

```
/var/www/saiflower-vps/uploads/
  flowers/
  sections/
  circles/
  cakes/
  ...
```

The API already returns correct paths like `/uploads/flowers/img_xxx.webp`.  
Images only break when **files are missing on disk**.

## Kodee prompt (copy-paste)

```text
Fix SaiFlower product images on this VPS.

1) Pull latest code:
   cd /var/www/saiflower-vps && git pull origin main

2) Set env (apps/server/.env AND apps/web/.env.local):
   UPLOADS_DIR=/var/www/saiflower-vps/uploads

3) Create uploads folder and copy legacy PHP uploads into it:
   mkdir -p /var/www/saiflower-vps/uploads
   # If I uploaded /tmp/uploads.zip or /tmp/saiflower-uploads.tar.gz, extract:
   tar -xzf /tmp/saiflower-uploads.tar.gz -C /var/www/saiflower-vps/uploads
   # OR: unzip -o /tmp/uploads.zip -d /var/www/saiflower-vps/uploads
   find /var/www/saiflower-vps/uploads -type f | wc -l
   # Expect 800+ files

4) Add Nginx static serving BEFORE location / (use deploy/nginx/uploads.conf):
   location /uploads/ {
       alias /var/www/saiflower-vps/uploads/;
       expires 7d;
       try_files $uri =404;
   }
   sudo nginx -t && sudo systemctl reload nginx

5) Rebuild and restart:
   cd /var/www/saiflower-vps
   npm install
   rm -rf apps/web/.next
   npm run build
   pm2 restart all

6) Verify:
   curl -s http://127.0.0.1:4000/health | grep uploadFiles
   curl -sI https://saiflower.com/uploads/sections/img_6998729febff3_IMG3579scaled.webp | grep content-type
   # Must be image/webp — NOT image/png
```

## From your PC (if you have the old uploads folder)

```bash
npm run uploads:pack
scp dist/saiflower-uploads.tar.gz root@YOUR_VPS_IP:/tmp/
```

Then on VPS:

```bash
mkdir -p /var/www/saiflower-vps/uploads
tar -xzf /tmp/saiflower-uploads.tar.gz -C /var/www/saiflower-vps/uploads
bash scripts/vps/setup-uploads.sh
```

## How it works in code

| Layer | Behavior |
|-------|----------|
| API | `mediaUrl()` returns `/uploads/...` from DB (no logo default) |
| Express | Serves `UPLOADS_DIR` at `/uploads` |
| Next.js | Rewrites `/uploads/*` → Express `:4000` |
| Nginx | (recommended) Serves `/uploads/` directly from disk |
| Frontend | `resolveImageSrc()` uses Unsplash placeholder only when image truly missing |

## Env

| Variable | Example |
|----------|---------|
| `UPLOADS_DIR` | `/var/www/saiflower-vps/uploads` |

Do **not** set `NEXT_PUBLIC_MEDIA_ORIGIN=https://saiflower.com` after DNS cutover.
