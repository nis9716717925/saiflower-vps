<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= $pageTitle ?? 'Sai Flower' ?></title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>

<!-- Tailwind Config -->
<script>
    <?php
    if(!isset($settings) && isset($conn)) {
        $sQ = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
        $settings = mysqli_fetch_assoc($sQ);
    }
    // Deep Green & Gold (Default/Original)
    $defPrimary = '#2f6f4e'; 
    $defSecondary = '#d4af37';
    
    // Fetch from DB or use defaults
    $primary = !empty($settings['theme_primary']) ? $settings['theme_primary'] : $defPrimary;
    // We can add secondary if needed in tailwind config, for now overriding primary
    ?>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "<?= $primary ?>",
                    "background-light": "#f6f8f6",
                    "background-dark": "#102216",
                },
                fontFamily: {
                    "display": ["Plus Jakarta Sans"]
                },
                borderRadius: {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
                },
            },
        },
    }
</script>

<style type="text/tailwindcss">
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .masonry-grid {
        columns: 1;
        column-gap: 0.5rem;
    }
    @media (min-width: 640px) {
        .masonry-grid {
            columns: 2;
        }
    }
    @media (min-width: 1024px) {
        .masonry-grid {
            columns: 3;
        }
    }
    @media (min-width: 1280px) {
        .masonry-grid {
            columns: 4;
        }
    }
    .masonry-item {
        break-inside: avoid;
        margin-bottom: 0.5rem;
    }
</style>
