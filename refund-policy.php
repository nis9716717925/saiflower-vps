<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= render_canonical_link() ?>
    <title>Refund & Cancellation Policy | Sai Flowers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Refund Policy', 'item' => 'refund-policy.php']]);
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

        /* ===== FLOATING ICON CSS ===== */
        .floating-icons {
            position: fixed; bottom: 20px; right: 20px; display: flex;
            flex-direction: column; gap: 15px; z-index: 9999;
        }
        .float-btn {
            width: 55px; height: 55px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            transition: transform 0.3s ease;
        }
        .float-btn:hover { transform: translateY(-5px); }
        .float-btn.whatsapp { background: #25D366; }
        .float-btn.whatsapp svg { width: 30px; height: 30px; fill: #fff; }
        .float-btn.call { background: #2f6f4e; color: #fff; font-size: 24px; }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<h1>Refund & Cancellation Policy</h1>

<div class="container">
    <p class="intro-text">Because our products (flowers and cakes) are fresh and perishable, our policy is designed to balance customer satisfaction with the nature of our handcrafted items.</p>

    <h2>1. Cancellations</h2>
    <ul>
        <li>Cancellations made 24 hours or more before the scheduled delivery time are eligible for a full refund or store credit.</li>
        <li>Cancellations made within 24 hours of delivery cannot be refunded as the flowers have already been sourced and the cake prepared.</li>
    </ul>

    <h2>2. Damaged or Incorrect Items</h2>
    <p>If you receive wilted flowers or a damaged cake, please contact us at <strong>8802004527</strong> or email us at <strong>support@saiflower.com</strong> within 2 hours of delivery.</p>
    <p><strong>Important:</strong> You must provide clear photographs of the damaged product and the delivery tag to be eligible for a replacement or refund.</p>

    <h2>3. Refund Processing</h2>
    <p>Approved refunds will be processed within 5–7 business days to your original payment method (Bank Account/UPI/Wallet).</p>

    <h2>4. Substitution Policy</h2>
    <p>Occasionally, substitutions of flowers or containers may be necessary due to seasonal availability. We guarantee that the "look and feel" and the value of the arrangement will remain the same or higher.</p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
