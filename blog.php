<?php
// 1. CONNECT & FETCH BLOGS
require_once __DIR__ . '/config.php';

// Fetch Blogs
$sql = "SELECT id, title, slug, image, LEFT(content, 140) AS excerpt 
        FROM blogs 
        WHERE status = 1 
        ORDER BY id DESC";

$blogs = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/partials/tailwind_config.php"; ?>
    <meta charset="UTF-8">
    <?php require_once __DIR__ . '/includes/seo_helper.php'; ?>
    <?= render_seo('blog.php'); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Blog', 'item' => 'blog.php']]);
    ?>
<style>
        /* ===== PAGE LAYOUT ===== */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        .section {
            max-width: 1200px;
            margin: auto;
            padding: 30px 20px;
        }
        
        .blog-page-title {
            text-align: center;
            margin-bottom: 40px;
        }

        /* ===== BLOG GRID ===== */
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        a.blog-card {
            display: block;
            text-decoration: none;
            color: inherit;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            transition: transform 0.3s ease;
        }
        a.blog-card:hover {
            transform: translateY(-5px);
        }

        .blog-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        
        .blog-content {
            padding: 20px;
        }
        
        .blog-content h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .blog-content p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .read-more-btn {
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            font-size: 14px;
            color: inherit;
            opacity: 0.8;
        }

        @media(max-width: 992px) { .blog-grid { grid-template-columns: repeat(2, 1fr); } }
        @media(max-width: 600px) { .blog-grid { grid-template-columns: 1fr; } }

        /* FLOATING ICONS */
        .floating-icons {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 9999;
        }

        .float-btn {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            transition: transform 0.3s ease;
            animation: jump 2s infinite ease-in-out;
        }

        .float-btn:hover { transform: translateY(-5px); }
        .float-btn.whatsapp { background: #25D366; animation-delay: 0s; }
        .float-btn.whatsapp svg { width: 30px; height: 30px; fill: #fff; }
        .float-btn.call { background: #2f6f4e; color: #fff; font-size: 24px; animation-delay: 1s; }

        @keyframes jump {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @media (max-width: 768px) {
            .float-btn { width: 45px; height: 45px; font-size: 20px; }
            .float-btn.whatsapp svg { width: 24px; height: 24px; }
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<section class="section">
    <h1 class="blog-page-title">Latest Updates</h1>

    <div class="blog-grid">
        <?php if ($blogs && mysqli_num_rows($blogs) > 0): ?>
            <?php while($b = mysqli_fetch_assoc($blogs)): ?>
            
            <?php 
                $blogSlug = !empty($b['slug']) ? $b['slug'] : $b['id'];
                $blogUrl = "/blog/" . $blogSlug;
                
                // Clean markdown links from excerpt for cleaner look
                $cleanExcerpt = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '$1', $b['excerpt']);
                $cleanExcerpt = strip_tags($cleanExcerpt);
            ?>
            <a href="<?= $blogUrl ?>" class="blog-card">
             <img src="/uploads/<?= htmlspecialchars($b['image']) ?>" 
                 onerror="this.src='https://via.placeholder.com/600x400?text=Krish+Florist+Blog'"
                 alt="<?= htmlspecialchars($b['title']) ?>" loading="lazy" width="600" height="400">
                
                <div class="blog-content">
                    <h3><?= htmlspecialchars($b['title']) ?></h3>
                    <p><?= $cleanExcerpt ?>...</p>
                    <span class="read-more-btn">Read Full Article →</span>
                </div>
            </a>
            
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: #777;">
                <h3>No blogs posted yet.</h3>
                <p>Check back later for updates!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>