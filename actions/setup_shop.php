<?php
require_once __DIR__ . '/../config.php';

function install_shop($conn) {
    // 1. PRODUCTS TABLE
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image VARCHAR(255) DEFAULT 'default.jpg',
        description TEXT,
        category VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $sql);

    // 2. INSERT DUMMY PRODUCTS (If empty)
    $check = mysqli_query($conn, "SELECT id FROM products LIMIT 1");
    if (mysqli_num_rows($check) == 0) {
        $products = [
            ['Red Rose Bouquet', 799, 'rose.jpg', 'Classic red roses for your loved one.', 'Bouquet'],
            ['Pink Lily Bunch', 999, 'lily.jpg', 'Elegant pink lilies wrapped in style.', 'Bouquet'],
            ['Mixed Carnations', 599, 'carnation.jpg', 'Colorful carnations to brighten the day.', 'Bouquet'],
            ['Orchid Vase', 1299, 'orchid.jpg', 'Exotic purple orchids in a glass vase.', 'Vase'],
            ['Chocolate Hamper', 1499, 'choco.jpg', 'Ferrero Rocher and Roses combo.', 'Gift'],
            ['Teddy & Roses', 999, 'teddy.jpg', 'Cute teddy bear with 6 red roses.', 'Gift']
        ];

        foreach ($products as $p) {
            $n=$p[0]; $pr=$p[1]; $i=$p[2]; $d=$p[3]; $c=$p[4];
            mysqli_query($conn, "INSERT INTO products (name, price, image, description, category) VALUES ('$n', $pr, '$i', '$d', '$c')");
        }
    }

    // 3. ORDERS TABLE
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $sql);

    // 4. ORDER ITEMS TABLE
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT DEFAULT 1,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    if(mysqli_query($conn, $sql)) {
        echo "Shop tables installed successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if ($conn) {
    install_shop($conn);
}
?>
