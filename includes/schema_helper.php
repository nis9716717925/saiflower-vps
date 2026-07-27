<?php
// includes/schema_helper.php

/**
 * Generate Product and related JSON-LD schemas
 *
 * @param array $product The product associative array from database
 * @param string $category The category string (e.g., 'flower', 'cake', 'gift')
 * @return string The raw HTML script block containing JSON-LD
 */
function generate_product_json_ld($product, $category = 'product') {
    // Basic settings
    $siteName = 'Sai Flowers';
    $siteUrl = 'https://' . $_SERVER['HTTP_HOST'];
    $currentUrl = $siteUrl . $_SERVER['REQUEST_URI'];
    $brand = ['@type' => 'Brand', 'name' => $siteName];

    $schemas = [];

    // Base Product Setup
    $name = !empty($product['meta_title']) ? $product['meta_title'] : $product['name'];
    $description = !empty($product['meta_description']) ? $product['meta_description'] : strip_tags($product['description']);
    // Truncate description to 160 chars for SEO if needed
    $description = mb_strimwidth($description, 0, 160, '...');

    // Handling Image 
    $images = [];
    if (!empty($product['image'])) {
        $imgPath = (strpos($product['image'], 'http') === 0) ? $product['image'] : $siteUrl . (strpos($product['image'], '/') === 0 ? '' : '/') . $product['image'];
        $images[] = $imgPath;
    }
    // Additional gallery images
    if (!empty($product['images_gallery'])) {
        $gallery = json_decode($product['images_gallery'], true);
        if (is_array($gallery)) {
            foreach ($gallery as $gImg) {
                $images[] = (strpos($gImg, 'http') === 0) ? $gImg : $siteUrl . (strpos($gImg, '/') === 0 ? '' : '/') . $gImg;
            }
        }
    }
    
    // A. Product Schema
    $productSchema = [
        '@context' => 'https://schema.org/',
        '@type' => 'Product',
        'name' => htmlspecialchars_decode($product['name']),
        'image' => $images,
        'description' => htmlspecialchars_decode($description),
        'sku' => strtoupper(substr($category, 0, 3)) . '-' . $product['id'],
        'brand' => $brand,
        'offers' => [
            '@type' => 'Offer',
            'url' => $currentUrl,
            'priceCurrency' => 'INR',
            'price' => isset($product['price']) ? (float)$product['price'] : 0,
            'itemCondition' => 'https://schema.org/NewCondition',
            'availability' => (isset($product['in_stock']) && $product['in_stock'] == 1) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        ]
    ];

    // Aggregate Rating
    if (!empty($product['rating'])) {
        $productSchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (float)$product['rating'],
            'reviewCount' => 120 // Static but ideally pulled dynamically
        ];
    }
    
    $schemas[] = $productSchema;

    // B. FAQPage Schema
    if (!empty($product['faqs'])) {
        $faqs = json_decode($product['faqs'], true);
        if (is_array($faqs) && count($faqs) > 0) {
            $faqEntities = [];
            foreach ($faqs as $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $faqEntities[] = [
                        '@type' => 'Question',
                        'name' => htmlspecialchars_decode($faq['question']),
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => htmlspecialchars_decode($faq['answer'])
                        ]
                    ];
                }
            }
            
            if (count($faqEntities) > 0) {
                $schemas[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqEntities
                ];
            }
        }
    }

    $categoryPath = [
        'flower' => 'flowers', 'cake' => 'cakes', 'gift' => 'gifts', 'event' => 'events',
    ][strtolower($category)] ?? (strtolower($category) . 's');

    // C. BreadcrumbList Schema
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => $siteUrl . '/'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => ucfirst($category) . 's',
                'item' => $siteUrl . '/' . $categoryPath
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => htmlspecialchars_decode($product['name']),
                'item' => $currentUrl
            ]
        ]
    ];

    // D. WebPage Schema
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => htmlspecialchars_decode($name),
        'description' => htmlspecialchars_decode($description),
        'url' => $currentUrl
    ];

    // E. ImageObject Schema
    if (count($images) > 0) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'ImageObject',
            'contentUrl' => $images[0],
            'creator' => [
                '@type' => 'Organization',
                'name' => $siteName
            ],
            'creditText' => $siteName,
            'copyrightNotice' => '© ' . date('Y') . ' ' . $siteName
        ];
    }

    // Return the formatted block
    return "<script type=\"application/ld+json\">\n" . json_encode($schemas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n</script>\n";
}

/**
 * Generate Breadcrumb, WebPage, and FAQ schemas for Custom Pages
 *
 * @param array $pageData The page data associative array from dynamic_pages
 * @return string The raw HTML script block containing JSON-LD
 */
function generate_custom_page_json_ld($pageData, array $products = []) {
    if (!$pageData) return '';

    $siteName = 'Sai Flowers';
    $siteUrl = function_exists('seo_site_base_url') ? seo_site_base_url() : ('https://' . ($_SERVER['HTTP_HOST'] ?? 'saiflower.com'));
    $currentUrl = !empty($pageData['seo_canonical'])
        ? rtrim($pageData['seo_canonical'], '/')
        : ($siteUrl . (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
    
    $schemas = [];

    $title = !empty($pageData['meta_title']) ? $pageData['meta_title'] : $pageData['title'];
    $description = !empty($pageData['meta_description']) ? $pageData['meta_description'] : (!empty($pageData['short_description']) ? $pageData['short_description'] : strip_tags($pageData['content'] ?? ''));
    $description = mb_strimwidth(strip_tags($description), 0, 160, "...");
    
    // 1. BreadcrumbList
    $itemList = [];
    $itemList[] = [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Home',
        'item' => $siteUrl . '/'
    ];

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Remove query params or trailing slashes and explode
    $segments = array_values(array_filter(explode('/', $path)));
    
    $pos = 2;
    $accumulatedPath = $siteUrl;

    if (count($segments) > 0) {
        foreach ($segments as $index => $segment) {
            $accumulatedPath .= '/' . $segment;
            $isLast = ($index === count($segments) - 1);
            
            // Format name nicely: convert hyphens to spaces and capitalize
            $segmentName = ucwords(str_replace(['-', '_'], ' ', $segment));
            
            // For the last segment, use the specific database page title
            if ($isLast && !empty($pageData['title'])) {
                $segmentName = htmlspecialchars_decode($pageData['title']);
            }
            
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $pos,
                'name' => $segmentName,
                'item' => $accumulatedPath
            ];
            $pos++;
        }
    } else {
        // Fallback (e.g. if routing leaves it empty)
        $itemList[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => htmlspecialchars_decode($pageData['title']),
            'item' => $currentUrl
        ];
    }

    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        '@id' => $currentUrl . '#breadcrumb',
        'itemListElement' => $itemList
    ];

    // 2. WebPage
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => $currentUrl,
        'url' => $currentUrl,
        'name' => htmlspecialchars_decode($title),
        'description' => htmlspecialchars_decode($description),
        'breadcrumb' => [
            '@id' => $currentUrl . '#breadcrumb'
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $siteName,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $siteUrl . '/favicon.png'
            ]
        ]
    ];

    // 3. FAQPage (if applicable)
    if (!empty($pageData['faqs'])) {
        $faqs = json_decode($pageData['faqs'], true);
        if (is_array($faqs) && count($faqs) > 0) {
            $faqEntities = [];
            foreach ($faqs as $faq) {
                $q = trim($faq['question'] ?? '');
                $a = trim($faq['answer'] ?? '');
                if (!empty($q) && !empty($a)) {
                    $faqEntities[] = [
                        '@type' => 'Question',
                        'name' => strip_tags(htmlspecialchars_decode($q)),
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => strip_tags(htmlspecialchars_decode($a))
                        ]
                    ];
                }
            }
            
            if (count($faqEntities) > 0) {
                $schemas[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    '@id' => $currentUrl . '#faq',
                    'mainEntity' => $faqEntities
                ];
            }
        }
    }

    // 4. Event (occasion landing pages)
    if (!empty($pageData['schema_event']) && is_array($pageData['schema_event'])) {
        $ev = $pageData['schema_event'];
        $eventSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            '@id' => $currentUrl . '#event',
            'name' => $ev['name'] ?? $pageData['title'],
            'description' => $ev['description'] ?? $description,
            'startDate' => $ev['startDate'] ?? null,
            'endDate' => $ev['endDate'] ?? null,
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => [
                '@type' => 'Place',
                'name' => $ev['locationName'] ?? 'Delhi NCR, India',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Delhi',
                    'addressRegion' => 'DL',
                    'addressCountry' => 'IN',
                ],
            ],
            'organizer' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => $siteUrl,
            ],
            'url' => $currentUrl,
        ];
        if (!empty($ev['image'])) {
            $eventSchema['image'] = $ev['image'];
        }
        $schemas[] = $eventSchema;
    }

    // 5. ItemList for product showcase grids
    if (count($products) > 0) {
        $listItems = [];
        $pos = 1;
        foreach ($products as $product) {
            $name = trim($product['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $path = function_exists('occasion_product_url') ? occasion_product_url($product) : '/';
            $itemUrl = (strpos($path, 'http') === 0) ? $path : $siteUrl . (strpos($path, '/') === 0 ? $path : '/' . $path);
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => $name,
                'url' => $itemUrl,
            ];
        }
        if (count($listItems) > 0) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                '@id' => $currentUrl . '#products',
                'name' => htmlspecialchars_decode($pageData['title'] ?? 'Products'),
                'itemListElement' => $listItems,
            ];
        }
    }

    return "<script type=\"application/ld+json\">\n" . json_encode($schemas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n</script>\n";
}

/**
 * Generate Organization and LocalBusiness schema for the home page
 *
 * @return string
 */
function generate_organization_json_ld() {
    $siteName = 'Sai Flower';
    $siteUrl = function_exists('seo_site_base_url') ? seo_site_base_url() : ('https://' . ($_SERVER['HTTP_HOST'] ?? 'saiflower.com'));
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => $siteUrl,
        'logo' => $siteUrl . '/uploads/logo_transparent.png',
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+91-8802004527',
            'contactType' => 'customer service',
            'areaServed' => 'IN',
            'availableLanguage' => ['en', 'hi']
        ],
        'sameAs' => [
            'https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/',
            'https://www.instagram.com/saiflowerofficial',
            'https://x.com/saiflower03'
        ]
    ];

    $localBusiness = [
        '@context' => 'https://schema.org',
        '@type' => 'Florist',
        'name' => $siteName,
        'image' => $siteUrl . '/uploads/logo_transparent.png',
        '@id' => $siteUrl,
        'url' => $siteUrl,
        'telephone' => '+91-8802004527',
        'priceRange' => '₹₹',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Shop No. 1, Lodhi Road',
            'addressLocality' => 'New Delhi',
            'postalCode' => '110003',
            'addressCountry' => 'IN'
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => 28.5912,
            'longitude' => 77.2270
        ],
        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => [
                    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
                ],
                'opens' => '09:00',
                'closes' => '21:00'
            ]
        ]
    ];

    return "<script type=\"application/ld+json\">\n" . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n</script>\n" .
           "<script type=\"application/ld+json\">\n" . json_encode($localBusiness, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n</script>\n";
}

/**
 * Generate WebSite (SearchBox) schema
 *
 * @return string
 */
function generate_website_json_ld() {
    $siteUrl = 'https://' . $_SERVER['HTTP_HOST'];
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'url' => $siteUrl,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $siteUrl . '/search-results.php?q={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];
    return "<script type=\"application/ld+json\">\n" . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n</script>\n";
}

/**
 * Generate Breadcrumb schema for simple pages
 *
 * @param array $crumbs Array of ['name' => 'Name', 'item' => 'URL']
 * @return string
 */
function generate_simple_breadcrumb_json_ld($crumbs) {
    $siteUrl = 'https://' . $_SERVER['HTTP_HOST'];
    $itemList = [];
    $itemList[] = [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Home',
        'item' => $siteUrl . '/'
    ];

    foreach ($crumbs as $index => $crumb) {
        $itemList[] = [
            '@type' => 'ListItem',
            'position' => $index + 2,
            'name' => $crumb['name'],
            'item' => (strpos($crumb['item'], 'http') === 0) ? $crumb['item'] : $siteUrl . '/' . ltrim($crumb['item'], '/')
        ];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $itemList
    ];

    return "<script type=\"application/ld+json\">\n" . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n</script>\n";
}

/**
 * Generate BlogPosting JSON-LD for a single blog post
 */
function generate_blog_json_ld($blog) {
    if (!$blog) return '';
    
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $slug = !empty($blog['slug']) ? $blog['slug'] : $blog['id'];
    $url = $baseUrl . "/blog/" . $slug;
    
    $json = [
        "@context" => "https://schema.org",
        "@type" => "BlogPosting",
        "headline" => strip_tags($blog['title']),
        "image" => !empty($blog['image']) ? $baseUrl . "/uploads/" . $blog['image'] : $baseUrl . "/assets/images/blog-placeholder.jpg",
        "url" => $url,
        "datePublished" => isset($blog['created_at']) ? date("c", strtotime($blog['created_at'])) : date("c"),
        "dateModified" => isset($blog['updated_at']) ? date("c", strtotime($blog['updated_at'])) : (isset($blog['created_at']) ? date("c", strtotime($blog['created_at'])) : date("c")),
        "author" => [
            "@type" => "Organization",
            "name" => "Sai Flowers"
        ],
        "publisher" => [
            "@type" => "Organization",
            "name" => "Sai Flowers",
            "logo" => [
                "@type" => "ImageObject",
                "url" => $baseUrl . "/favicon.png"
            ]
        ],
        "description" => mb_strimwidth(strip_tags($blog['content']), 0, 160, "...")
    ];
    
    return '<script type="application/ld+json">' . json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Generate ItemList schema for listing pages
 *
 * @param array $items Array of products/items
 * @param string $category Category name
 * @param string $pageUrl Current page URL
 * @return string
 */
function generate_listing_json_ld($items, $category, $pageUrl) {
    $siteUrl = 'https://' . $_SERVER['HTTP_HOST'];
    $itemListElement = [];
    
    $i = 1;
    if (is_array($items) || is_object($items)) {
        foreach ($items as $item) {
            $p_link = (!empty($item['slug'])) ? $siteUrl . "/" . $item['slug'] : $siteUrl . "/" . strtolower($category) . "-detail.php?id=" . $item['id'];
            
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $i,
                'url' => $p_link,
                'name' => $item['name'] ?? ($item['title'] ?? '')
            ];
            $i++;
        }
    }

    $schemas = [];
    
    // 1. Breadcrumb
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => $siteUrl . '/'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => ucfirst($category) . 's',
                'item' => $pageUrl
            ]
        ]
    ];

    // 2. ItemList (within CollectionPage)
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => 'Shop ' . ucfirst($category) . 's Online',
        'url' => $pageUrl,
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $itemListElement
        ]
    ];

    return "<script type=\"application/ld+json\">\n" . json_encode($schemas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n</script>\n";
}
?>
