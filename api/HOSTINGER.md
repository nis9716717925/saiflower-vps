# Connect Saiflower API to Hostinger MySQL (phpMyAdmin)

Your **phone app** does not connect to phpMyAdmin directly.  
Flow:

```
Phone (Expo)  →  API (Node.js)  →  MySQL on Hostinger
```

You can use Hostinger in **two ways**:

---

## Option A — Recommended if API is already live on Hostinger

If `https://api.saiflower.com` is already deployed and uses the Hostinger database:

### 1. Mobile app `.env`

Edit `saiflower-app/.env`:

```env
EXPO_PUBLIC_API_BASE_URL=https://api.saiflower.com/api/v1
```

Remove or comment out `EXPO_PUBLIC_DEV_MACHINE_IP` (not needed for production API).

### 2. Restart Expo

```powershell
cd saiflower-app
npm run start:clear
```

No local MySQL or local API needed. The app calls your live server.

---

## Option B — Run API on your PC, database on Hostinger

Use this when you develop locally but data lives in Hostinger MySQL.

### Step 1 — Get MySQL details from Hostinger (hPanel)

1. Log in to **Hostinger hPanel**
2. Go to **Databases → Management** (or **MySQL Databases**)
3. Note these values:

| Field | Example | Where to find |
|-------|---------|---------------|
| **Hostname** | `srv1234.hstgr.io` | MySQL server / Remote MySQL |
| **Database name** | `u123456789_saiflower` | MySQL databases list |
| **Username** | `u123456789_admin` | Same page |
| **Password** | (your password) | You set this when creating DB user |
| **Port** | `3306` | Usually 3306 |

You can also see tables in **phpMyAdmin** → select your database → confirm table names.

### Step 2 — Allow your PC to connect (Remote MySQL)

Hostinger blocks remote connections by default.

1. hPanel → **Databases → Remote MySQL**
2. Add your **public IP** (not 192.168.x.x)
   - Google: "what is my ip"
3. Save

> If your home IP changes, update Remote MySQL when connection fails.

### Step 3 — Update API `.env`

Edit `saiflower/api/.env`:

```env
DATABASE_URL="mysql://USERNAME:PASSWORD@HOSTNAME:3306/DATABASE_NAME"
```

**Example:**

```env
DATABASE_URL="mysql://u123456789_admin:MyP@ssw0rd@srv1234.hstgr.io:3306/u123456789_saiflower"
```

**Special characters in password** must be URL-encoded:

| Character | Use |
|-----------|-----|
| `@` | `%40` |
| `#` | `%23` |
| `$` | `%24` |
| `%` | `%25` |

### Step 4 — Check table names in phpMyAdmin

This API expects tables like:

- `api_categories`
- `api_products`
- `api_users`
- … (all prefixed with `api_`)

In phpMyAdmin, open your database and check the **table list**.

| If you see… | What to do |
|-------------|------------|
| Tables named `api_categories`, `api_products`, etc. | Good — same schema. Go to Step 5. |
| Different names (e.g. `categories`, `wp_posts`) | Database is from another system. You need migration or import into `api_*` tables. |
| Empty database | Run `npm run db:push` and `npm run db:seed` once (see Step 5). |

### Step 5 — Connect Prisma (do NOT wipe existing data)

**If data already exists in `api_*` tables:**

```powershell
cd saiflower/api
npm run db:generate
npm run dev
```

Do **not** run `db:push` if tables already have data (unless you know it’s safe).

**If database is empty but tables exist:**

```powershell
npm run db:seed
```

**If no tables yet:**

```powershell
npm run db:push
npm run db:seed
```

**If unsure whether schema matches:**

```powershell
npx prisma db pull
```

This reads your Hostinger DB structure into `schema.prisma` (review changes before committing).

### Step 6 — Test API

```powershell
cd saiflower-app
npm run check-api
```

Categories should return **OK 200**.

### Step 7 — Mobile app (local API + Hostinger DB)

Keep in `saiflower-app/.env`:

```env
EXPO_PUBLIC_DEV_MACHINE_IP=192.168.1.38
EXPO_PUBLIC_API_BASE_URL=http://192.168.1.38:4000/api/v1
```

Start API + Expo as usual.

---

## Quick checklist

| Step | Action |
|------|--------|
| 1 | Copy Hostinger MySQL host, user, password, database name |
| 2 | Add your public IP in Hostinger **Remote MySQL** |
| 3 | Set `DATABASE_URL` in `saiflower/api/.env` |
| 4 | Confirm `api_categories` / `api_products` exist in phpMyAdmin |
| 5 | `npm run db:generate` then `npm run dev` in `saiflower/api` |
| 6 | `npm run check-api` in `saiflower-app` |
| 7 | `npm run start:clear` and reload app |

---

## Troubleshooting

| Error | Fix |
|-------|-----|
| `Can't reach database server` | Remote MySQL not enabled, wrong host, or firewall |
| `Access denied for user` | Wrong username/password in `DATABASE_URL` |
| `Internal server error` on categories | Table missing or wrong name — check phpMyAdmin |
| App still shows `localhost` | Run `npm run start:clear` after `.env` change |

---

## phpMyAdmin tips

- **View data:** phpMyAdmin → your database → table `api_categories` → Browse
- **Export backup:** Export → SQL (before running `db:push`)
- **Import:** Import tab if you have a `.sql` file

Do not share your database password in chat. Only put it in `saiflower/api/.env` (this file is not committed to git).
