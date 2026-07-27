<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= render_canonical_link() ?>
    <title>Delivery Policy | Sai Flowers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Delivery Policy', 'item' => 'delivery-policy.php']]);
    ?>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
        <link rel="icon" href="/favicon.png" type="image/x-icon">

    <style>
        /* ===== THEME COLORS ===== */
        :root {
            --primary-green: #2f6f4e;
            --soft-pink-bg: #fff1f5;
            --white: #ffffff;
            --text-dark: #333;
        }

        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: var(--soft-pink-bg);
            margin: 0; 
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: var(--white);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 50px;
        }
        @media (min-width: 768px) {
            .container {
                padding: 50px;
            }
        }

        h1 {
            text-align: center;
            color: var(--primary-green);
            padding-top: 40px;
            margin-top: 0;
            margin-bottom: 30px;
        }

        h2 {
            color: var(--primary-green);
            font-size: 20px;
            margin-top: 30px;
        }

        p, li {
            font-size: 15px;
            color: #555;
            margin-bottom: 15px;
        }

        ul { padding-left: 20px; }
        
        strong { color: #333; }
        
        .intro-text {
            text-align: center;
            font-size: 16px;
            margin-bottom: 30px;
            color: #555;
            font-style: italic;
        }

       
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<h1>Delivery Policy</h1>

<div class="container">
    <p class="intro-text">At <strong>Sai Flower</strong>, we strive to deliver your emotions on time, every time. Please review our delivery guidelines to ensure a seamless experience.</p>

    <h2>1. Delivery Areas</h2>
    <p>We currently serve all of Delhi NCR, including New Delhi, Noida, Gurgaon, Ghaziabad, and Faridabad.</p>

    <h2>2. Delivery Slots</h2>
    <ul>
        <li><strong>Standard Delivery:</strong> Between 9:00 AM and 9:00 PM.</li>
        <li><strong>Same-Day Delivery:</strong> Orders must be placed before 6:00 PM (IST) for same-day fulfillment.</li>
        <li><strong>Midnight Delivery:</strong> Delivered between 11:15 PM and 12:15 AM. Please select the date <em>before</em> the occasion (e.g., if the birthday is on the 25th, select the 24th for midnight delivery).</li>
        <li><strong>Fixed Time Delivery:</strong> While we aim for exact timing, please allow a +/- 30-minute buffer due to traffic and weather conditions in Delhi.</li>
    </ul>

    <h2>3. Address Accuracy</h2>
    <p>The customer is responsible for providing a correct and reachable phone number and address. If the recipient is not available, we will attempt to leave the gift with a neighbor or security guard, which will be considered a successful delivery.</p>

    <h2>4. Major Holidays</h2>
    <p>During peak times like Valentine’s Day or Mother’s Day, specific time slots may not be guaranteed. We recommend ordering at least 48 hours in advance.</p>
</div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
