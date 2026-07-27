<?php
/**
 * Branded 404 — keep users shopping.
 */
http_response_code(404);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

$settingsQuery = @mysqli_query($conn, 'SELECT * FROM settings WHERE id=1');
$settings = $settingsQuery ? (mysqli_fetch_assoc($settingsQuery) ?: []) : [];
$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';
$wa_num = '8802004527';
$wa_link = 'https://wa.me/918802004527';
$requested = htmlspecialchars((string) ($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8');

$shortcuts = [
    ['label' => 'Shop Flowers', 'href' => '/flowers', 'icon' => 'local_florist'],
    ['label' => 'Birthday Gifts', 'href' => '/occasion/birthday', 'icon' => 'cake'],
    ['label' => 'Roses', 'href' => '/flowers/roses', 'icon' => 'favorite'],
    ['label' => 'Same Day', 'href' => '/collection/same-day-delivery', 'icon' => 'bolt'],
    ['label' => 'Celebrations', 'href' => '/celebration-calendar', 'icon' => 'calendar_month'],
    ['label' => 'Contact', 'href' => '/contact', 'icon' => 'chat'],
];
?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page not found | Sai Flowers</title>
    <meta name="description" content="This page doesn’t exist — browse fresh flower collections, celebrations, and same-day delivery from Sai Flowers.">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="https://saiflower.com/404">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        :root {
            --n4-primary: <?= htmlspecialchars($pCol) ?>;
            --n4-accent: <?= htmlspecialchars($sCol) ?>;
            --n4-ink: #1c1814;
            --n4-muted: #6a6258;
            --n4-bg: #f6f2ea;
        }
        * { box-sizing: border-box; }
        body.n4-page {
            margin: 0;
            font-family: 'Manrope', system-ui, sans-serif;
            color: var(--n4-ink);
            background:
                radial-gradient(1000px 480px at 12% -10%, rgba(47, 111, 78, 0.1), transparent 55%),
                radial-gradient(900px 420px at 100% 0%, rgba(212, 175, 55, 0.14), transparent 50%),
                var(--n4-bg);
            min-height: 100vh;
        }
        .n4-hero {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            padding: 2.5rem 1rem 1.5rem;
        }
        .n4-hero::before {
            content: "";
            position: absolute;
            inset: -20% 40% auto -10%;
            height: 70%;
            background: radial-gradient(circle, rgba(47, 111, 78, 0.16), transparent 70%);
            z-index: -1;
            animation: n4-bloom 8s ease-in-out infinite alternate;
        }
        @keyframes n4-bloom {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(4%, 6%) scale(1.08); }
        }
        .n4-wrap {
            width: min(920px, 100%);
            margin: 0 auto;
            text-align: center;
        }
        .n4-code {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: #fff;
            border: 1px solid rgba(28, 24, 20, 0.08);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--n4-primary);
            margin-bottom: 1rem;
        }
        .n4-title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(2.4rem, 8vw, 4.2rem);
            line-height: 1.05;
            margin: 0 0 0.75rem;
            font-weight: 700;
        }
        .n4-lead {
            margin: 0 auto 1.35rem;
            max-width: 38ch;
            color: var(--n4-muted);
            font-size: 1rem;
            line-height: 1.55;
        }
        .n4-path {
            display: inline-block;
            max-width: 100%;
            margin: 0 auto 1.5rem;
            padding: 0.55rem 0.9rem;
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.7);
            border: 1px dashed rgba(28, 24, 20, 0.14);
            font-size: 0.78rem;
            color: var(--n4-muted);
            word-break: break-all;
        }
        .n4-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.65rem;
            margin-bottom: 2rem;
        }
        .n4-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            min-height: 48px;
            padding: 0.7rem 1.25rem;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.9rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .n4-btn:hover { transform: translateY(-2px); }
        .n4-btn--primary {
            background: var(--n4-primary);
            color: #fff;
            box-shadow: 0 12px 28px rgba(47, 111, 78, 0.28);
        }
        .n4-btn--accent {
            background: var(--n4-accent);
            color: #1c1814;
        }
        .n4-btn--ghost {
            background: #fff;
            color: var(--n4-ink);
            border: 1px solid rgba(28, 24, 20, 0.1);
        }
        .n4-search {
            width: min(460px, 100%);
            margin: 0 auto 2.25rem;
            position: relative;
        }
        .n4-search form {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            background: #fff;
            border: 1px solid rgba(28, 24, 20, 0.1);
            border-radius: 999px;
            padding: 0.35rem 0.4rem 0.35rem 1rem;
            box-shadow: 0 10px 30px rgba(28, 24, 20, 0.06);
        }
        .n4-search input {
            flex: 1;
            border: 0;
            outline: none;
            background: transparent;
            font: inherit;
            min-width: 0;
            font-size: 0.95rem;
        }
        .n4-search button {
            border: 0;
            border-radius: 999px;
            background: var(--n4-primary);
            color: #fff;
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            cursor: pointer;
        }
        .n4-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            text-align: left;
            margin-bottom: 2.5rem;
        }
        .n4-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1rem;
            background: #fff;
            border-radius: 1rem;
            text-decoration: none;
            color: inherit;
            border: 1px solid rgba(28, 24, 20, 0.07);
            box-shadow: 0 8px 24px rgba(28, 24, 20, 0.04);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .n4-card:hover {
            transform: translateY(-2px);
            border-color: rgba(47, 111, 78, 0.35);
        }
        .n4-card .material-icons-outlined {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(47, 111, 78, 0.1);
            color: var(--n4-primary);
            flex-shrink: 0;
        }
        .n4-card strong {
            display: block;
            font-size: 0.92rem;
        }
        .n4-card span {
            font-size: 0.75rem;
            color: var(--n4-muted);
        }
        .n4-foot {
            padding-bottom: 2.5rem;
            color: var(--n4-muted);
            font-size: 0.85rem;
        }
        .n4-foot a { color: var(--n4-primary); font-weight: 700; }
        @media (min-width: 720px) {
            .n4-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .n4-hero { padding-top: 3.5rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .n4-hero::before { animation: none; }
            .n4-btn:hover, .n4-card:hover { transform: none; }
        }
    </style>
</head>
<body class="n4-page">
<?php include __DIR__ . '/partials/navbar.php'; ?>

<section class="n4-hero" aria-labelledby="n4-title">
    <div class="n4-wrap">
        <p class="n4-code"><span class="material-icons-outlined" style="font-size:1rem" aria-hidden="true">spa</span> Error 404</p>
        <h1 id="n4-title" class="n4-title">This bouquet took a wrong turn</h1>
        <p class="n4-lead">The page you’re looking for isn’t blooming here. Let’s get you back to fresh flowers, celebrations, and same-day delivery.</p>
        <p class="n4-path" title="Requested URL">Missing: <?= $requested ?></p>

        <div class="n4-actions">
            <a class="n4-btn n4-btn--primary" href="/">Go to homepage</a>
            <a class="n4-btn n4-btn--accent" href="/flowers">Shop flowers</a>
            <a class="n4-btn n4-btn--ghost" href="<?= htmlspecialchars($wa_link) ?>" target="_blank" rel="noopener noreferrer">WhatsApp us</a>
        </div>

        <div class="n4-search">
            <form action="/search-results" method="get" role="search">
                <label class="sr-only" for="n4Search" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)">Search</label>
                <input id="n4Search" name="q" type="search" placeholder="Search roses, birthday, cakes…" autocomplete="off" enterkeyhint="search">
                <button type="submit" aria-label="Search"><span class="material-icons-outlined">search</span></button>
            </form>
        </div>

        <div class="n4-grid" aria-label="Popular destinations">
            <?php foreach ($shortcuts as $s): ?>
            <a class="n4-card" href="<?= htmlspecialchars($s['href']) ?>">
                <span class="material-icons-outlined" aria-hidden="true"><?= htmlspecialchars($s['icon']) ?></span>
                <span>
                    <strong><?= htmlspecialchars($s['label']) ?></strong>
                    <span>Continue shopping</span>
                </span>
            </a>
            <?php endforeach; ?>
        </div>

        <p class="n4-foot">Still stuck? <a href="/contact">Contact support</a> or browse the <a href="/celebration-calendar">celebrations calendar</a>.</p>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
