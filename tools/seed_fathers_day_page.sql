-- Optional: sync Father's Day into dynamic_pages for admin editing.
-- Built-in page at /fathers-day works without this row; skip if slug already exists.

INSERT INTO dynamic_pages (
    title,
    short_description,
    slug,
    content,
    meta_title,
    meta_description,
    meta_keywords,
    status,
    layout_type,
    page_tag,
    faqs
)
SELECT
    'Father''s Day Gifts & Flowers',
    'Honour Dad on 21 June with fresh flowers, cakes & gift hampers — same-day delivery across Delhi NCR.',
    'fathers-day',
    '<p>Celebrate Dad with handpicked flowers, cakes, and gift hampers from Sai Flowers. <a href=\"/flowers\">Shop flowers</a>, <a href=\"/cakes\">cakes</a>, and <a href=\"/gifts\">gifts</a>.</p>',
    'Father''s Day Gifts & Flower Delivery Delhi | Sai Flowers',
    'Order Father''s Day flowers, cakes & gift hampers online. Same-day delivery in Delhi NCR on 21 June 2026.',
    'father''s day gifts, father''s day flowers, father''s day flower delivery delhi, father''s day cake delivery, father''s day 2026',
    1,
    'product_showcase',
    'fathers-day',
    NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM dynamic_pages WHERE slug = 'fathers-day' LIMIT 1);

-- After insert, set canonical URL via admin or use built-in page at https://saiflower.com/fathers-day
