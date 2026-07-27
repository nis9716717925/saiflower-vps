<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= render_canonical_link() ?>
    <title>Terms & Conditions | Sai Flowers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Terms & Conditions', 'item' => 'terms.php']]);
    ?>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
     
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
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

        .last-updated {
            text-align: center;
            font-style: italic;
            color: #888;
            margin-bottom: 30px;
        }

        /* ===== FIXED: WHATSAPP ICON CSS ===== */
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

<h1>Terms and Conditions</h1>

<div class="container">
    <p class="last-updated">Last Updated: January 2026</p>

    <p>Welcome to <strong>Sai Flower</strong>. By accessing our website (saiflower.com) and placing an order, you agree to the following terms and conditions.</p>

    <h2>1. General</h2>
    <p>These terms govern your use of our website and services. We reserve the right to update or modify these terms at any time without prior notice.</p>

    <h2>2. Products and Substitution Policy</h2>
    <ul>
        <li><strong>Freshness:</strong> We deal in perishable items like flowers and cakes. While we strive to match the images on our website exactly, natural variations in color, size, and blooming stages may occur.</li>
        <li><strong>Substitutions:</strong> In the event of temporary, regional availability issues, our expert florists may substitute specific flowers, colors, or vases with items of equal or higher value to ensure your gift is delivered on time. We maintain the overall look, theme, and quality of the arrangement.</li>
    </ul>

    <h2>3. Pricing and Payments</h2>
    <ul>
        <li>All prices listed on the website are in Indian Rupees (INR) and are subject to change without notice.</li>
        <li>Full payment must be received before an order is processed and dispatched.</li>
        <li>Discount codes (e.g., code SAI) must be applied at checkout and cannot be claimed after the order is placed. Only one discount code can be used per order.</li>
    </ul>

    <h2>4. User Responsibilities</h2>
    <ul>
        <li><strong>Accurate Information:</strong> You are responsible for providing accurate recipient details (name, address, landmark, and phone number). Sai Flower is not liable for delayed or failed deliveries caused by incorrect or incomplete addresses.</li>
        <li><strong>Account Security:</strong> If you create an account, you are responsible for maintaining the confidentiality of your login credentials.</li>
    </ul>

    <h2>5. Limitation of Liability</h2>
    <p>Sai Flower shall not be held liable for any indirect, incidental, or consequential damages arising from the use of our website or the delay/failure of delivery due to unforeseen circumstances (e.g., severe weather, strikes, or major traffic delays in Delhi NCR).</p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>