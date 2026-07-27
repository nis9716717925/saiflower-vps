<?php
require_once __DIR__ . '/config.php';

// FIX: Define format_content if not already defined in config.php to prevent 500 error
if (!function_exists('format_content')) {
    function format_content($content) {
        $content = $content ?? '';
        // Parse [text](url) markdown syntax
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" class="text-primary hover:underline" target="_blank">$1</a>', $content);
        return nl2br($content);
    }
}

// Safe Default Theme Values
$pCol = '#2f6f4e'; // Primary Green
$bgColor = '#ffffff';

try {
    $settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
    if ($settingsQuery && mysqli_num_rows($settingsQuery) > 0) {
        $settings = mysqli_fetch_assoc($settingsQuery);
        $pCol = $settings['theme_primary'] ?? $pCol;
        if (($settings['maintenance_mode'] ?? 0) == 1) {
            header("Location: /maintenance");
            exit;
        }
    }
} catch (Exception $e) {
    // Ignore theme fetch errors to prevent 500s
}

// Auto-patch database for missing blog columns
try {
    @mysqli_query($conn, "ALTER TABLE blogs ADD COLUMN slug VARCHAR(255) DEFAULT NULL UNIQUE AFTER title");
    @mysqli_query($conn, "ALTER TABLE blogs ADD COLUMN meta_title VARCHAR(255) DEFAULT NULL");
    @mysqli_query($conn, "ALTER TABLE blogs ADD COLUMN meta_description TEXT DEFAULT NULL");
    @mysqli_query($conn, "ALTER TABLE blogs ADD COLUMN meta_keywords TEXT DEFAULT NULL");
} catch (Exception $e) {}

// Fetch Blog Content
$blog = null;
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $res = mysqli_query($conn, "SELECT * FROM blogs WHERE id=$id AND status=1 LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            $blog = mysqli_fetch_assoc($res);
        }
    } catch (Exception $e) {}
} elseif (isset($_GET['slug']) && !empty(trim($_GET['slug']))) {
    $slug = mysqli_real_escape_string($conn, trim($_GET['slug']));
    try {
        $res = mysqli_query($conn, "SELECT * FROM blogs WHERE slug='$slug' AND status=1 LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            $blog = mysqli_fetch_assoc($res);
        }
    } catch (Exception $e) {}
}

// Handle Blog Not Found - Redirect once or show error
if (!$blog) {
    header("Location: /blog");
    exit;
}

set_page_canonical_url(get_blog_canonical_url($blog));

// Secure data extraction with fallbacks
$blogTitle = htmlspecialchars($blog['title'] ?? 'Untitled Post');
$blogContent = format_content($blog['content'] ?? '');
$blogImage = htmlspecialchars($blog['image'] ?? '');
$blogDate = date("F j, Y", strtotime($blog['created_at'] ?? 'now'));
$blogSlug = htmlspecialchars($blog['slug'] ?? '');

?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <?= render_canonical_link() ?>
    <title><?= $blogTitle ?> | Sai Flowers</title>
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_blog_json_ld($blog);
    echo generate_simple_breadcrumb_json_ld([
        ['name' => 'Blog', 'item' => 'blog.php'],
        ['name' => $blogTitle, 'item' => 'blog/' . $blogSlug]
    ]);
    ?>
    
    <?php include __DIR__ . "/partials/tailwind_config.php"; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/favicon.png" type="image/x-icon">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #334155; }
        .serif { font-family: 'Playfair Display', serif; }
        .prose p { margin-bottom: 1.5rem; line-height: 1.8; }
        .prose h2, .prose h3 { font-family: 'Playfair Display', serif; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem; }
        .prose h2 { font-size: 1.875rem; }
        .prose h3 { font-size: 1.5rem; }
        .prose ul, .prose ol { padding-left: 1.5rem; margin-bottom: 1.5rem; }
        .prose li { margin-bottom: 0.5rem; }
        .img-hero { max-height: 500px; width: 100%; object-fit: cover; border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="antialiased">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main class="pt-24 pb-16">
    <div class="container mx-auto px-4 max-w-4xl text-center mb-10">
        <span class="inline-block px-4 py-1 bg-green-100 text-green-800 text-xs font-bold uppercase tracking-widest rounded-full mb-4">
            Floral Insights
        </span>
        <h1 class="serif text-4xl md:text-5xl lg:text-6xl font-bold text-slate-900 mb-6 leading-tight">
            <?= $blogTitle ?>
        </h1>
        <div class="flex items-center justify-center gap-4 text-slate-500 text-sm font-medium">
            <span><i class="far fa-calendar mr-2"></i><?= $blogDate ?></span>
            <span class="w-1.5 h-1.5 bg-slate-300 rounded-full"></span>
            <span><i class="far fa-user mr-2"></i>Sai Flowers Editorial</span>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-7xl">
        <div class="grid lg:grid-cols-12 gap-10">
            
            <div class="lg:col-span-8">
                <?php if (!empty($blogImage)): ?>
                <div class="mb-10">
                    <img src="/uploads/<?= $blogImage ?>" 
                         onerror="this.src='https://images.unsplash.com/photo-1490750967868-58cb75069faf?q=80&w=1600'" 
                         class="img-hero" alt="<?= $blogTitle ?>">
                </div>
                <?php endif; ?>

                <article class="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-slate-100 prose max-w-none text-lg">
                    <div class="prose-body"><?= $blogContent ?></div>
                </article>

            </div>

            <div class="lg:col-span-4 space-y-8">
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h3 class="serif text-xl font-bold text-slate-900 mb-4">About the Author</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Sai Flowers is dedicated to creating premium floral arrangements for unforgettable moments. We source the freshest blooms to craft stunning masterpieces for every occasion.
                    </p>
                    <a href="/about" class="text-[#2f6f4e] font-bold text-sm hover:underline inline-flex items-center gap-1">
                        Read more <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>

                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h4 class="font-bold text-slate-900 uppercase tracking-wider text-sm mb-6 border-b border-slate-100 pb-4">Recent Events</h4>
                    <div class="space-y-4">
                        <?php
                        try {
                            $events = mysqli_query($conn, "SELECT id, title, slug, image, cover_image FROM events ORDER BY id DESC LIMIT 3");
                            if ($events && mysqli_num_rows($events) > 0) {
                                while($e = mysqli_fetch_assoc($events)) {
                                    $eImg = htmlspecialchars(!empty($e['cover_image']) ? $e['cover_image'] : ($e['image'] ?? ''));
                                    $eTitle = htmlspecialchars($e['title'] ?? 'Event');
                                    $eventUrl = htmlspecialchars(product_url(['type' => 'event', 'slug' => $e['slug'] ?? '', 'id' => $e['id']]));
                                    echo "
                                    <a href='{$eventUrl}' class='flex items-center gap-4 group'>
                                        <img src='/uploads/{$eImg}' class='w-16 h-16 rounded-xl object-cover group-hover:opacity-80 transition-opacity' alt='{$eTitle} event' onerror=\"this.src='https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=100'\">
                                        <div>
                                            <h5 class='font-bold text-slate-800 text-sm group-hover:text-[#2f6f4e] transition-colors line-clamp-2'>{$eTitle}</h5>
                                            <span class='text-xs text-slate-400'>Read more</span>
                                        </div>
                                    </a>";
                                }
                            } else {
                                echo "<p class='text-sm text-slate-500'>No recent events found.</p>";
                            }
                        } catch (Exception $e) {
                            echo "<p class='text-sm text-slate-500'>Unable to load events.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>