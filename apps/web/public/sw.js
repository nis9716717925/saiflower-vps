/* SaiFlower static asset / image cache service worker */
const CACHE = 'sf-static-v1';
const MAX_ENTRIES = 180;

const CACHEABLE = [
  /\/_next\/static\//,
  /\/uploads\/.+\.(webp|jpe?g|png|gif|svg)(\?|$)/i,
  /\/assets\/images\/.+\.(webp|jpe?g|png|gif|svg)(\?|$)/i,
  /\/celebrations\/.+\.(webp|jpe?g|png|gif|svg)(\?|$)/i,
  /\/assets\/css\//,
  /\/favicon\.png$/,
];

function isCacheable(url) {
  try {
    const u = new URL(url);
    if (u.origin !== self.location.origin) return false;
    return CACHEABLE.some((re) => re.test(u.pathname));
  } catch {
    return false;
  }
}

async function trim(cache) {
  const keys = await cache.keys();
  if (keys.length <= MAX_ENTRIES) return;
  await Promise.all(keys.slice(0, keys.length - MAX_ENTRIES).map((k) => cache.delete(k)));
}

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(caches.open(CACHE));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      const names = await caches.keys();
      await Promise.all(names.filter((n) => n !== CACHE).map((n) => caches.delete(n)));
      await self.clients.claim();
    })(),
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;
  if (!isCacheable(request.url)) return;

  event.respondWith(
    (async () => {
      const cache = await caches.open(CACHE);
      const cached = await cache.match(request);
      if (cached) {
        // Stale-while-revalidate
        event.waitUntil(
          fetch(request)
            .then((res) => {
              if (res && res.ok) {
                cache.put(request, res.clone());
                return trim(cache);
              }
            })
            .catch(() => {}),
        );
        return cached;
      }

      try {
        const res = await fetch(request);
        if (res && res.ok) {
          cache.put(request, res.clone());
          event.waitUntil(trim(cache));
        }
        return res;
      } catch (err) {
        return cached || Response.error();
      }
    })(),
  );
});
