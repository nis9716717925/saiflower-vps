const fs = require('fs');
const path = require('path');
const html = fs.readFileSync(path.join(__dirname, 'live_homepage.html'), 'utf8');

function decode(s) {
  return (s || '').replace(/&amp;/g, '&').replace(/&#039;/g, "'").trim();
}

function phpExport(val, indent = 4) {
  const pad = ' '.repeat(indent);
  if (Array.isArray(val)) {
    if (!val.length) return '[]';
    return '[\n' + val.map((v) => pad + phpExport(v, indent + 4)).join(',\n') + '\n' + ' '.repeat(indent - 4) + ']';
  }
  if (val !== null && typeof val === 'object') {
    const entries = Object.entries(val);
    if (!entries.length) return '[]';
    return '[\n' + entries.map(([k, v]) => pad + `'${k}' => ` + phpExport(v, indent + 4)).join(',\n') + '\n' + ' '.repeat(indent - 4) + ']';
  }
  if (typeof val === 'number') return String(val);
  return `'${String(val).replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'`;
}

function imgPath(src) {
  return (src || '').replace(/^\//, '');
}

function parsePrice(s) {
  return (s || '0').replace(/,/g, '');
}

// Slides
const slides = [];
const slideHtml = html.split('id="sliderTrack"')[1]?.split('</section>')[0] || '';
for (const chunk of slideHtml.match(/<div class="w-full min-w-full h-full relative flex-shrink-0">[\s\S]*?<\/div>\s*(?=<motion|<div class="w-full min-w-full|<\/div>\s*<\/div>)/g) || []) {
  const desktop = chunk.match(/<img src="([^"]+)"/)?.[1];
  const mobile = chunk.match(/srcset="([^"]+)"/)?.[1] || desktop;
  const link = chunk.match(/<a href="([^"]*)"/)?.[1] || '';
  if (desktop) slides.push({ image: imgPath(desktop), mobile_image: imgPath(mobile), link, status: 1, sort_order: slides.length });
}

// Circles - all 8 from igp-border-container
const circles = [];
const circleHtml = html.split('grid grid-cols-4 igp-border-container')[1]?.split('</section>')[0] || '';
for (const m of circleHtml.matchAll(/<a href="([^"]*)"[\s\S]*?src="(\/uploads\/circles\/[^"]+)"[\s\S]*?px-1">([^<]+)<\/span>/g)) {
  circles.push({ name: decode(m[3]), image: imgPath(m[2]), link: m[1], status: 1, sort_order: circles.length });
}

function parseProductCardChunk(chunk) {
  const link = chunk.match(/<a href="([^"]+)"/)?.[1] || '#';
  const image = chunk.match(/<img src="([^"]+)"/)?.[1];
  const title = decode(chunk.match(/<h3[^>]*>([^<]+)<\/h3>/)?.[1]);
  const price = parsePrice(chunk.match(/leading-none">₹([\d,]+)</)?.[1]);
  const original_price = parsePrice(chunk.match(/line-through[^>]*>₹([\d,]+)</)?.[1] || '');
  const discount_label = chunk.match(/tracking-wide leading-none ml-1">([^<]+)</)?.[1] || '';
  const delivery_info = decode(chunk.match(/<span class="truncate">([^<]+)<\/span>/)?.[1] || '');
  if (!image) return null;
  return {
    image: imgPath(image),
    mobile_image: '',
    title,
    subtitle: '',
    price,
    link,
    top_badge_text: '',
    badge_text: '',
    rating: '',
    delivery_info,
    original_price,
    discount_label,
  };
}

function parseAllProductCards(block) {
  const items = [];
  const parts = block.split('<motion class="product-card w-[180px]');
  const parts2 = block.split('<div class="product-card w-[180px]');
  const segments = parts2.length > parts.length ? parts2 : parts;
  for (let i = 1; i < segments.length; i++) {
    const item = parseProductCardChunk('<div class="product-card w-[180px]' + segments[i]);
    if (item) items.push(item);
  }
  return items;
}

function parseCircleCarousel(block) {
  const items = [];
  for (const m of block.matchAll(/<a href="([^"]*)" class="master-card m-circle">[\s\S]*?src="([^"]+)"[\s\S]*?master-label-ext">([^<]+)</g)) {
    items.push({ image: imgPath(m[2]), title: decode(m[3]), link: m[1], subtitle: '', price: '0', mobile_image: '' });
  }
  return items;
}

function parseCalendar(block) {
  const items = [];
  for (const m of block.matchAll(/<a href="([^"]*)" class="block flex-shrink-0[\s\S]*?src="([^"]+)"[\s\S]*?<h3[^>]*>([^<]+)<\/h3>/g)) {
    items.push({ image: imgPath(m[2]), title: decode(m[3]), link: m[1], subtitle: '', price: '0', mobile_image: '' });
  }
  return items;
}

const promos = [{ code: 'GIFTING A SMILE', discount_text: '10% off on order above 499', min_order_amount: 499, status: 1, is_featured: 1 }];

const sections = [];
const allItems = [];
let secId = 1;
let itemId = 1;

function addSection(title, type, items, subtitle = '') {
  sections.push({ id: secId, title, subtitle, type, status: 1, sort_order: secId - 1 });
  items.forEach((item, idx) => {
    allItems.push({ ...item, id: itemId++, section_id: secId, sort_order: idx });
  });
  secId++;
}

const bestSellersBlock = html.split('Best Sellers')[1]?.split('Budget-Friendly Gifts')[0] || '';
addSection('Best Sellers', 'carousel', parseAllProductCards(bestSellersBlock));

const giftsBlock = html.split('Gifts They Will Love')[1]?.split('Pick Their Fav Flower')[0] || '';
addSection('Gifts They Will Love', 'carousel', parseAllProductCards(giftsBlock));

const flowerBlock = html.split('Pick Their Fav Flower')[1]?.split('Make Someone Smile')[0] || '';
addSection('Pick Their Fav Flower', 'circle_carousel', parseCircleCarousel(flowerBlock));

addSection('Make Someone Smile', 'cta_banner', [{ image: '', title: '', link: 'flowers.php', subtitle: '', price: '0', mobile_image: '' }]);

const bannerM = html.match(/<a href="([^"]*)" class="block rounded-2xl overflow-hidden shadow-md bg-gray-50[\s\S]*?srcset="([^"]+)"[\s\S]*?<h3[^>]*>([^<]+)<\/h3>/);
if (bannerM) {
  addSection('', 'banner', [{
    image: imgPath(bannerM[2]),
    mobile_image: imgPath(bannerM[2]),
    link: bannerM[1],
    title: decode(bannerM[3]),
    subtitle: '',
    price: '0',
  }]);
}

const calBlock = html.split('Celebrations Calendar')[1]?.split('Order Flowers Online')[0] || '';
addSection('Celebrations Calendar', 'calendar', parseCalendar(calBlock));

const out = `<?php
/**
 * Static homepage content — snapshot from live saiflower.com (May 2026).
 */

function homepage_get_slides(): array {
    static $slides = ${phpExport(slides, 8)};
    return $slides;
}

function homepage_get_circles(): array {
    static $circles = ${phpExport(circles, 8)};
    return $circles;
}

function homepage_get_sections(): array {
    static $sections = ${phpExport(sections, 8)};
    return $sections;
}

function homepage_get_section_items(): array {
    static $items = ${phpExport(allItems, 8)};
    return $items;
}

function homepage_get_section_items_grouped(): array {
    static $grouped = null;
    if ($grouped !== null) {
        return $grouped;
    }
    $grouped = [];
    foreach (homepage_get_section_items() as $item) {
        $sid = (int) $item['section_id'];
        if (!isset($grouped[$sid])) {
            $grouped[$sid] = [];
        }
        $grouped[$sid][] = $item;
    }
    return $grouped;
}

function homepage_get_reviews(): array {
    return [];
}

function homepage_get_promos(): array {
    static $promos = ${phpExport(promos, 8)};
    return $promos;
}

function homepage_display_price($price): string {
    return number_format((float) $price);
}
`;

fs.writeFileSync(path.join(__dirname, '..', 'includes', 'homepage_data.php'), out);
console.log(JSON.stringify({ slides: slides.length, circles: circles.length, sections: sections.length, items: allItems.length }, null, 2));
