# Saiflower eCommerce REST API

Production-ready REST API for the Saiflower eCommerce platform. Built with **Node.js**, **Express**, **TypeScript**, and **Prisma ORM** (MySQL). Designed to serve both web and mobile clients.

## Features

| Module | Endpoints |
|--------|-----------|
| **Auth** | Register, login, logout, refresh tokens, social login, password reset |
| **Products** | List, search, filter, sort, details, related items, stock check |
| **Categories** | Category tree, subcategories, brands |
| **Cart** | Guest & user carts, add/update/remove, merge on login |
| **Checkout** | Summary, delivery slots, payment providers, place order |
| **Orders** | History, detail, cancel, track, invoice |
| **Wishlist** | Add, remove, list |
| **Reviews** | Full CRUD with rating aggregation |
| **Addresses** | CRUD + set default |
| **Coupons** | Validate & apply |
| **Settings** | Currency, branding, maintenance mode, feature flags |

## Security

- JWT access + refresh token rotation
- bcrypt password hashing (12 rounds)
- Rate limiting (global + auth endpoints)
- Helmet security headers
- XSS sanitization on request bodies
- Prisma parameterized queries (SQL injection safe)
- CORS with configurable origins
- Request validation via express-validator

## Quick Start

```bash
cd api
cp .env.example .env
# Edit .env with your MySQL DATABASE_URL and JWT secrets

npm install
npm run db:generate
npm run db:push
npm run db:seed
npm run dev
```

API: `http://localhost:4000/api/v1`  
Swagger: `http://localhost:4000/docs`  
Health: `http://localhost:4000/health`

## Response Format

All endpoints return a consistent JSON structure:

```json
{
  "success": true,
  "message": "Products retrieved",
  "data": { ... },
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "totalPages": 8,
    "hasNextPage": true,
    "hasPrevPage": false
  }
}
```

## Authentication

Include the JWT in the `Authorization` header:

```
Authorization: Bearer <access_token>
```

For guest carts, pass a UUID in the `X-Guest-Id` header. The API returns a `guestId` on first cart access.

## Payment Providers

Toggle providers in `.env`:

```
PAYMENT_STRIPE_ENABLED=false
PAYMENT_RAZORPAY_ENABLED=true
PAYMENT_COD_ENABLED=true
```

Payment intent creation is abstracted in `PaymentService` — wire your provider SDKs there.

## Project Structure

```
api/
├── prisma/
│   ├── schema.prisma      # Database models
│   └── seed.ts            # Sample data
├── src/
│   ├── config/            # App & DB configuration
│   ├── controllers/       # Request handlers
│   ├── docs/              # OpenAPI specification
│   ├── middleware/        # Auth, rate limit, validation, XSS
│   ├── routes/            # Route definitions
│   ├── services/          # Business logic
│   ├── utils/             # Helpers (JWT, errors, sanitize)
│   ├── validators/        # Request validation rules
│   ├── app.ts             # Express app setup
│   └── server.ts          # Entry point
├── .env.example
└── package.json
```

## Scripts

| Command | Description |
|---------|-------------|
| `npm run dev` | Start dev server with hot reload |
| `npm run build` | Compile TypeScript |
| `npm start` | Run production build |
| `npm run db:migrate` | Run Prisma migrations |
| `npm run db:seed` | Seed sample data |

## Demo Credentials

After seeding:

- **Email:** `demo@saiflower.com`
- **Password:** `Password123!`
