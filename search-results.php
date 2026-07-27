<?php
mysqli_report(MYSQLI_REPORT_OFF); 
require_once __DIR__ . '/config.php';

// 1. THEME SETTINGS
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);

$pCol    = $settings['theme_primary'] ?? '#2f6f4e';
$sCol    = $settings['theme_secondary'] ?? '#d4af37';
$bgColor = $settings['theme_bg_color'] ?? '#ffffff';
$tCol    = $settings['theme_text_color'] ?? '#333333';
$fFam    = $settings['theme_font'] ?? "'Playfair Display', serif";

// 2. SEARCH LOGIC
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($query !== '') {
    $param = "%$query%";

    // Flowers
    $stmt1 = $conn->prepare("SELECT id, name, image, 'flower' as type FROM flowers WHERE name LIKE ? LIMIT 12");
    $stmt1->bind_param("s", $param);
    $stmt1->execute();
    $res1 = $stmt1->get_result();
    while($row = $res1->fetch_assoc()) $results[] = $row;

    // Events
    $stmt2 = $conn->prepare("SELECT id, title as name, cover_image as image, 'event' as type FROM events WHERE title LIKE ? LIMIT 12");
    $stmt2->bind_param("s", $param);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while($row = $res2->fetch_assoc()) $results[] = $row;

    // Gallery
    $stmt3 = $conn->prepare("SELECT id, title as name, image, 'gallery' as type FROM gallery WHERE title LIKE ? LIMIT 12");
    $stmt3->bind_param("s", $param);
    $stmt3->execute();
    $res3 = $stmt3->get_result();
    while($row = $res3->fetch_assoc()) $results[] = $row;
    // Cakes
    $stmt4 = $conn->prepare("SELECT id, name, image, 'cake' as type FROM cakes WHERE name LIKE ? LIMIT 12");
    $stmt4->bind_param("s", $param);
    $stmt4->execute();
    $res4 = $stmt4->get_result();
    while($row = $res4->fetch_assoc()) $results[] = $row;

    // Gifts
    $stmt5 = $conn->prepare("SELECT id, name, image, 'gift' as type FROM gifts WHERE name LIKE ? LIMIT 12");
    $stmt5->bind_param("s", $param);
    $stmt5->execute();
    $res5 = $stmt5->get_result();
    while($row = $res5->fetch_assoc()) $results[] = $row;
}

if ($query !== '') {
    set_page_canonical_url(seo_site_base_url() . '/search-results?q=' . rawurlencode($query));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/partials/tailwind_config.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_canonical_link() ?>
    <title>Search Results | Sai Flower</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
     
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <style>
        :root {
            --primary: <?= $pCol ?>;
            --accent: <?= $sCol ?>;
            --bg-site: <?= $bgColor ?>;
            --text-main: <?= $tCol ?>;
            --font-main: <?= $fFam ?>;
        }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg-site); color: var(--text-main); }
        nav { position: relative !important; background: #fff !important; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .container { max-width: 1200px; margin: auto; padding: 0 20px; }
        .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; margin-top: 40px; padding-bottom: 80px; }
        
        .result-card {
            display: flex; flex-direction: column; text-decoration: none; color: inherit;
            background: #fff; border-radius: 18px; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,.06); transition: 0.3s ease; height: 100%;
            border: 1px solid #eee;
        }
        .result-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        .result-card img { width: 100%; height: 230px; object-fit: cover; }
        .card-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .tag { font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--accent); margin-bottom: 8px; letter-spacing: 1px; }
        .card-title { margin: 0; font-size: 1.2rem; color: var(--primary); font-family: var(--font-main); }
        
        @media(max-width: 768px) {
            .results-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .result-card img { height: 160px; }
            .card-title { font-size: 1rem; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <div style="text-align: center; padding: 60px 0 20px;">
        <h1 style="font-family: var(--font-main); color: var(--primary); font-size: 3rem; margin: 0;">Search Results</h1>
        <p style="color: #888;">Matches for "<?= htmlspecialchars($query) ?>"</p>
    </div>

    <div class="results-grid">
        <?php if (count($results) > 0): ?>
            <?php foreach ($results as $item): 
                $id = $item['id'];
                $dbImage = $item['image'];

                // SMART IMAGE PATHING
                // If DB has "uploads/", use it directly with leading slash.
                // If not (like Blogs), add "/uploads/" prefix.
                if (strpos($dbImage, 'uploads/') === 0) {
                    $finalPath = "/" . $dbImage;
                } else {
                    $finalPath = "/uploads/" . $dbImage;
                }

                if (in_array($item['type'], ['flower', 'cake', 'gift', 'event'], true)) {
                    $link = ltrim(product_url($item), '/');
                } else {
                    $link = "gallery-detail.php?id=$id";
                }
            ?>
            <a href="<?= $link ?>" class="result-card">
                <img src="<?= $finalPath ?>" 
                     onerror="this.src='https://images.unsplash.com/photo-1519225495806-7d5225a0d16a?q=80&w=800';"
                     alt="<?= htmlspecialchars($item['name']) ?>">
                <div class="card-content">
                    <span class="tag"><?= $item['type'] ?></span>
                    <h3 class="card-title"><?= htmlspecialchars($item['name']) ?></h3>
                    <p style="margin-top: auto; font-size: 13px; font-weight: bold; color: var(--primary);">View Details <i class="fas fa-arrow-right" style="font-size: 10px; margin-left: 5px;"></i></p>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
                <i class="fas fa-search" style="font-size: 3rem; color: #eee; margin-bottom: 20px;"></i>
                <h3 style="color: #999;">No results found.</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>