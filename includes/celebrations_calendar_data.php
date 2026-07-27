<?php
/**
 * Celebrations Calendar — static card data for homepage section.
 * Ordered chronologically (Jan → Dec). Variable festival dates use 2026 observance.
 */

if (!function_exists('celebrations_calendar_get_items')) {
    function celebrations_calendar_get_items(): array
    {
        return [
            [
                'date'  => '1ST JAN',
                'title' => "New Year's Day",
                'image' => '/celebrations/new-year.jpg',
                'slug'  => 'new-years-day',
            ],
            [
                'date'  => '7TH FEB',
                'title' => 'Rose Day',
                'image' => '/celebrations/rose-day.jpg',
                'slug'  => 'rose-day',
            ],
            [
                'date'  => '8TH FEB',
                'title' => 'Propose Day',
                'image' => '/celebrations/propose-day.jpg',
                'slug'  => 'propose-day',
            ],
            [
                'date'  => '9TH FEB',
                'title' => 'Chocolate Day',
                'image' => '/celebrations/chocolate-day.jpg',
                'slug'  => 'chocolate-day',
            ],
            [
                'date'  => '10TH FEB',
                'title' => 'Teddy Day',
                'image' => '/celebrations/teddy-day.jpg',
                'slug'  => 'teddy-day',
            ],
            [
                'date'  => '11TH FEB',
                'title' => 'Promise Day',
                'image' => '/celebrations/promise-day.jpg',
                'slug'  => 'promise-day',
            ],
            [
                'date'  => '12TH FEB',
                'title' => 'Hug Day',
                'image' => '/celebrations/hug-day.jpg',
                'slug'  => 'hug-day',
            ],
            [
                'date'  => '13TH FEB',
                'title' => 'Kiss Day',
                'image' => '/celebrations/kiss-day.jpg',
                'slug'  => 'kiss-day',
            ],
            [
                'date'  => '14TH FEB',
                'title' => "Valentine's Day",
                'image' => '/celebrations/valentines-day.jpg',
                'slug'  => 'valentines-day',
            ],
            [
                'date'  => '8TH MAR',
                'title' => "Women's Day",
                'image' => '/celebrations/womens-day.jpg',
                'slug'  => 'womens-day',
            ],
            [
                'date'  => '10TH MAY',
                'title' => "Mother's Day",
                'image' => '/celebrations/mothers-day.jpg',
                'slug'  => 'mothers-day',
            ],
            [
                'date'  => '21ST JUN',
                'title' => "Father's Day",
                'image' => '/celebrations/fathers-day.jpg',
                'slug'  => 'fathers-day',
            ],
            [
                'date'  => '1ST JUL',
                'title' => "Doctor's Day",
                'image' => '/celebrations/doctors-day.jpg',
                'slug'  => 'doctors-day',
            ],
            [
                'date'  => '2ND AUG',
                'title' => 'Friendship Day',
                'image' => '/celebrations/friendship-day.jpg',
                'slug'  => 'friendship-day',
            ],
            [
                'date'  => '28TH AUG',
                'title' => 'Raksha Bandhan',
                'image' => '/celebrations/raksha-bandhan.jpg',
                'slug'  => 'raksha-bandhan',
            ],
            [
                'date'  => '5TH SEP',
                'title' => "Teacher's Day",
                'image' => '/celebrations/teachers-day.jpg',
                'slug'  => 'teachers-day',
            ],
            [
                'date'  => '13TH SEP',
                'title' => 'Grandparents Day',
                'image' => '/celebrations/grandparents-day.jpg',
                'slug'  => 'grandparents-day',
            ],
            [
                'date'  => '14TH SEP',
                'title' => 'Janmashtami',
                'image' => '/celebrations/janmashtami.jpg',
                'slug'  => 'janmashtami',
            ],
            [
                'date'  => '21ST SEP',
                'title' => 'Wife Appreciation Day',
                'image' => '/celebrations/wife-appreciation-day.jpg',
                'slug'  => 'wife-appreciation-day',
            ],
            [
                'date'  => '29TH OCT',
                'title' => 'Karwa Chauth',
                'image' => '/celebrations/karwa-chauth.jpg',
                'slug'  => 'karwa-chauth',
            ],
            [
                'date'  => '5TH NOV',
                'title' => 'Dhanteras',
                'image' => '/celebrations/dhanteras.jpg',
                'slug'  => 'dhanteras',
            ],
            [
                'date'  => '12TH NOV',
                'title' => 'Diwali',
                'image' => '/celebrations/diwali.jpg',
                'slug'  => 'diwali',
            ],
            [
                'date'  => '14TH NOV',
                'title' => "Children's Day",
                'image' => '/celebrations/childrens-day.jpg',
                'slug'  => 'childrens-day',
            ],
            [
                'date'  => '15TH NOV',
                'title' => 'Bhai Dooj',
                'image' => '/celebrations/bhai-dooj.jpg',
                'slug'  => 'bhai-dooj',
            ],
            [
                'date'  => '19TH NOV',
                'title' => "International Men's Day",
                'image' => '/celebrations/mens-day.jpg',
                'slug'  => 'mens-day',
            ],
            [
                'date'  => '25TH DEC',
                'title' => 'Christmas',
                'image' => '/celebrations/christmas.jpg',
                'slug'  => 'christmas',
            ],
        ];
    }
}

if (!function_exists('celebrations_calendar_month_key')) {
    /**
     * Parse "1ST JAN" style labels into a sort key + display month.
     *
     * @return array{month:string,sort:int}|null
     */
    function celebrations_calendar_month_key(string $dateLabel): ?array
    {
        static $months = [
            'JAN' => ['January', 1],
            'FEB' => ['February', 2],
            'MAR' => ['March', 3],
            'APR' => ['April', 4],
            'MAY' => ['May', 5],
            'JUN' => ['June', 6],
            'JUL' => ['July', 7],
            'AUG' => ['August', 8],
            'SEP' => ['September', 9],
            'OCT' => ['October', 10],
            'NOV' => ['November', 11],
            'DEC' => ['December', 12],
        ];

        if (!preg_match('/\b([A-Z]{3})\b/i', $dateLabel, $m)) {
            return null;
        }
        $abbr = strtoupper($m[1]);
        if (!isset($months[$abbr])) {
            return null;
        }

        return [
            'month' => $months[$abbr][0],
            'sort'  => $months[$abbr][1],
        ];
    }
}

if (!function_exists('celebrations_calendar_group_by_month')) {
    /**
     * @param list<array<string,mixed>> $items
     * @return list<array{month:string,sort:int,items:list<array<string,mixed>>}>
     */
    function celebrations_calendar_group_by_month(array $items): array
    {
        $groups = [];
        foreach ($items as $item) {
            $meta = celebrations_calendar_month_key((string) ($item['date'] ?? ''));
            $month = $meta['month'] ?? 'Year-round';
            $sort = $meta['sort'] ?? 99;
            if (!isset($groups[$month])) {
                $groups[$month] = [
                    'month' => $month,
                    'sort'  => $sort,
                    'items' => [],
                ];
            }
            $groups[$month]['items'][] = $item;
        }
        $out = array_values($groups);
        usort($out, static fn ($a, $b) => $a['sort'] <=> $b['sort']);

        return $out;
    }
}

if (!function_exists('celebrations_calendar_upcoming')) {
    /**
     * @param list<array<string,mixed>> $items
     * @return list<array<string,mixed>>
     */
    function celebrations_calendar_upcoming(array $items, int $limit = 6): array
    {
        $now = new DateTimeImmutable('today');
        $year = (int) $now->format('Y');
        $scored = [];

        foreach ($items as $item) {
            $dateLabel = strtoupper((string) ($item['date'] ?? ''));
            if (!preg_match('/(\d{1,2}).*?([A-Z]{3})/', $dateLabel, $m)) {
                continue;
            }
            $day = (int) $m[1];
            $abbr = $m[2];
            $monthNum = [
                'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6,
                'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
            ][$abbr] ?? 0;
            if ($monthNum < 1 || $day < 1) {
                continue;
            }
            try {
                $event = DateTimeImmutable::createFromFormat('!Y-n-j', $year . '-' . $monthNum . '-' . $day);
                if (!$event) {
                    continue;
                }
                if ($event < $now) {
                    $event = $event->modify('+1 year');
                }
                $item['_days'] = (int) $now->diff($event)->format('%a');
                $scored[] = $item;
            } catch (Throwable $e) {
                continue;
            }
        }

        usort($scored, static fn ($a, $b) => ($a['_days'] ?? 999) <=> ($b['_days'] ?? 999));
        $out = array_slice($scored, 0, $limit);
        foreach ($out as &$row) {
            unset($row['_days']);
        }
        unset($row);

        return $out;
    }
}
