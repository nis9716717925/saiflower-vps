<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined&display=block" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet"/>

<!-- Tailwind Config -->
<script>
    <?php
    if(!isset($settings) && isset($conn)) {
        $sQ = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
        $settings = mysqli_fetch_assoc($sQ);
    }
    $primary = !empty($settings['theme_primary']) ? $settings['theme_primary'] : '#2f6f4e'; 
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
