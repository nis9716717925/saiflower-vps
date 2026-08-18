import { NextResponse, type NextRequest } from 'next/server';

export const runtime = 'nodejs';
export const dynamic = 'force-dynamic';

/**
 * Legacy PHP admin lived on shared hosting. That website was removed after VPS cutover.
 * Keep /admin as a stable endpoint with a clear message instead of proxying a dead origin.
 */
function adminGoneResponse() {
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin | Sai Flower</title>
  <style>
    body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:Inter,system-ui,sans-serif;background:#f6f2ea;color:#201c17}
    .card{max-width:28rem;padding:2rem;border-radius:1.25rem;background:#fff;border:1px solid #ece7dd;box-shadow:0 16px 40px rgba(32,28,23,.08)}
    h1{margin:0 0 .75rem;font-size:1.35rem}
    p{margin:0 0 1rem;line-height:1.55;color:#4b463e}
    a{color:#1f5138;font-weight:700}
  </style>
</head>
<body>
  <main class="card">
    <h1>Admin panel unavailable</h1>
    <p>The legacy PHP admin was retired with the shared-hosting cutover. The storefront now runs on the VPS.</p>
    <p><a href="/">Return to Sai Flower</a> · <a href="/contact">Contact support</a></p>
  </main>
</body>
</html>`;
  return new NextResponse(html, {
    status: 410,
    headers: { 'content-type': 'text/html; charset=utf-8', 'cache-control': 'no-store' },
  });
}

export async function GET(_req: NextRequest) {
  return adminGoneResponse();
}

export async function POST(_req: NextRequest) {
  return adminGoneResponse();
}

export async function PUT(_req: NextRequest) {
  return adminGoneResponse();
}

export async function PATCH(_req: NextRequest) {
  return adminGoneResponse();
}

export async function DELETE(_req: NextRequest) {
  return adminGoneResponse();
}

export async function HEAD(_req: NextRequest) {
  return new NextResponse(null, { status: 410 });
}
