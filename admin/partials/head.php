<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<title>Sai Flower Admin | <?php echo $pageTitle ?? 'Dashboard'; ?></title>

<link rel="icon" type="image/png" href="/favicon.png?v=<?= time() ?>">
<link rel="apple-touch-icon" href="/favicon.png?v=<?= time() ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="../assets/css/style.css">

<style>
    /* Global Admin Variable Sync */
    <?php
    if(!isset($settings) && isset($conn)) {
        $sQ = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
        $settings = mysqli_fetch_assoc($sQ);
    }
    // Fallback to $s if $settings not set (dashboard case), or defaults
    $admPrimary = $settings['theme_primary'] ?? ($s['theme_primary'] ?? '#326e54');
    $admAccent = $settings['theme_secondary'] ?? ($s['theme_secondary'] ?? '#d4af37');
    ?>
    :root {
        --primary: <?= $admPrimary ?>;
        --accent: <?= $admAccent ?>;
        --bg: #f4f7f6;
        --sidebar-width: 260px;
    }

    body {
        font-family: 'Inter', -apple-system, sans-serif;
    }

    /* Common Sidebar Logic for all pages */
    @media (min-width: 993px) {
        .admin-main {
            margin-left: var(--sidebar-width);
            transition: 0.3s;
        }
    }

    @media (max-width: 992px) {
        .admin-main {
            margin-left: 0 !important;
            width: 100% !important;
            padding-bottom: 80px; /* Room for bottom navigation bar */
        }
    }
</style>