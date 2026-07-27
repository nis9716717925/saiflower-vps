<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= render_canonical_link() ?>
    <title>Grievance Redressal Mechanism | Sai Flowers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Grievance', 'item' => 'grievnce.php']]);
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
        }
        @media (min-width: 768px) {
            .container {
                padding: 50px;
            }
        }

        h1 {
            text-align: center;
            color: var(--primary-green);
            margin-top: 40px;
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
        
        .contact-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid var(--primary-green);
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<h1>Grievance Redressal Mechanism</h1>

<div class="container">
    <p>In accordance with the Information Technology Act, 2000 and Consumer Protection (E-Commerce) Rules, 2020, Sai Flower is committed to resolving customer grievances in a timely and transparent manner.</p>
    
    <p>If you have any complaints regarding your order, delivery, or our services, please reach out to our Grievance Officer directly using the contact information below:</p>

    <div class="contact-info">
        <p><strong>Name:</strong> Krishan Kumar</p>
        <p><strong>Designation:</strong> Grievance Officer</p>
        <p><strong>Email Address:</strong> <a href="mailto:saiflower03@gmail.com" style="color: var(--primary-green); text-decoration: none;">saiflower03@gmail.com</a></p>
        <p><strong>Contact Number:</strong> <a href="tel:+918802004527" style="color: var(--primary-green); text-decoration: none;">+91-8802004527</a></p>
        <p><strong>Operating Address:</strong> Shop No. 1, Lodhi Road, Sai Baba Mandir, New Delhi Office Address in Delhi</p>
    </div>

    <h2>Resolution Time</h2>
    <p>We will acknowledge your complaint within <strong>48 hours</strong> and strive to resolve it within <strong>3 to 5 business days</strong>.</p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
