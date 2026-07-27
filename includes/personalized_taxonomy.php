<?php
/**
 * Personalised gift landings registry.
 * URLs: /personalized | /personalized/{slug}
 */

if (!function_exists('personalized_taxonomy_all')) {
    /**
     * @return array<string, array<string, mixed>>
     */
    function personalized_taxonomy_all(): array
    {
        return [
            '' => [
                'slug' => '',
                'title' => 'Personalised Gifts',
                'h1' => 'Personalised Gifts Online — Coming Soon',
                'badge' => 'Personalised',
                'status' => 'available_soon',
                'status_label' => 'Available Soon',
                'short' => 'Photo frames, engraved keepsakes and custom message cards — crafting something special. Meanwhile, surprise them with a fresh bouquet today.',
                'hero' => 'https://images.unsplash.com/photo-1513201099705-a9746e1e201f?auto=format&fit=crop&w=1600&q=80',
                'recommend_line' => 'While we finish personalised keepsakes, these bouquets make a heartfelt gift right now.',
                'bouquet_keywords' => null,
            ],
            'photo-frames' => [
                'slug' => 'photo-frames',
                'title' => 'Photo Frames & Keepsakes',
                'h1' => 'Personalised Photo Frames & Keepsakes',
                'badge' => 'Photo Gifts',
                'status' => 'available_soon',
                'status_label' => 'Available Soon',
                'short' => 'Custom photo frames and memory keepsakes are on the way. Send fresh blooms while you wait — they never go out of style.',
                'hero' => 'https://images.unsplash.com/photo-1513519245088-0e12902e35a6?auto=format&fit=crop&w=1600&q=80',
                'recommend_line' => 'Pair a future photo gift with flowers they’ll love today.',
                'bouquet_keywords' => ['rose', 'mixed', 'premium'],
            ],
            'custom-message-cards' => [
                'slug' => 'custom-message-cards',
                'title' => 'Custom Message Cards',
                'h1' => 'Custom Message Cards with Flower Gifts',
                'badge' => 'Message Cards',
                'status' => 'available_soon',
                'status_label' => 'Available Soon',
                'short' => 'Personal notes and premium message cards are launching soon. Every Sai Flowers bouquet already includes a free handwritten-style card at checkout.',
                'hero' => 'https://images.unsplash.com/photo-1456735190827-d1262f71b8a3?auto=format&fit=crop&w=1600&q=80',
                'recommend_line' => 'Add your note at checkout — these bouquets deliver with a free message card.',
                'bouquet_keywords' => ['birthday', 'love', 'thank'],
            ],
            'engraved-gifts' => [
                'slug' => 'engraved-gifts',
                'title' => 'Engraved Gifts',
                'h1' => 'Engraved Personalised Gifts',
                'badge' => 'Engraved',
                'status' => 'available_soon',
                'status_label' => 'Available Soon',
                'short' => 'Name-engraved gifts are in production. Until then, curated flower bouquets are ready for same-day delivery across Delhi NCR.',
                'hero' => 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=1600&q=80',
                'recommend_line' => 'Looking for something memorable today? These signature bouquets convert beautifully.',
                'bouquet_keywords' => ['premium', 'orchid', 'designer'],
            ],
            'photo-gifts' => [
                'slug' => 'photo-gifts',
                'title' => 'Photo Gifts',
                'h1' => 'Personalised Photo Gifts',
                'badge' => 'Photo Gifts',
                'status' => 'available_soon',
                'status_label' => 'Available Soon',
                'short' => 'Photo mugs, frames and prints are almost here. Fresh rose and mixed bouquets ship today.',
                'hero' => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?auto=format&fit=crop&w=1600&q=80',
                'recommend_line' => 'You may also love these romantic bouquet picks.',
                'bouquet_keywords' => ['rose', 'valentine', 'red'],
            ],
            'name-plates' => [
                'slug' => 'name-plates',
                'title' => 'Name Plates & Custom Tags',
                'h1' => 'Custom Name Plates & Gift Tags',
                'badge' => 'Custom',
                'status' => 'available_soon',
                'status_label' => 'Available Soon',
                'short' => 'Custom name plates are coming soon. Personalise any flower order with a free card note for now.',
                'hero' => 'https://images.unsplash.com/photo-1487530811176-3780da8112fd?auto=format&fit=crop&w=1600&q=80',
                'recommend_line' => 'Go for these bestselling bouquets while custom tags roll out.',
                'bouquet_keywords' => null,
            ],
        ];
    }
}

if (!function_exists('personalized_get')) {
    function personalized_get(?string $slug): ?array
    {
        $slug = strtolower(trim((string) $slug, '/'));
        $all = personalized_taxonomy_all();
        if (!array_key_exists($slug, $all)) {
            return null;
        }
        $item = $all[$slug];
        $item['canonical_path'] = $slug === '' ? '/personalized' : '/personalized/' . $slug;
        return $item;
    }
}

if (!function_exists('personalized_list')) {
    /**
     * @return list<array<string, mixed>>
     */
    function personalized_list(bool $includeHub = false): array
    {
        $out = [];
        foreach (personalized_taxonomy_all() as $slug => $item) {
            if ($slug === '' && !$includeHub) {
                continue;
            }
            $item['canonical_path'] = $slug === '' ? '/personalized' : '/personalized/' . $slug;
            $out[] = $item;
        }
        return $out;
    }
}

if (!function_exists('personalized_url')) {
    function personalized_url(string $slug = ''): string
    {
        $slug = strtolower(trim($slug, '/'));
        return $slug === '' ? '/personalized' : '/personalized/' . $slug;
    }
}
