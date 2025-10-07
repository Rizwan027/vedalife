<?php
require_once __DIR__ . '/../admin/admin_auth.php';

$conn = getAdminDbConnection();

$rows = [
    ['Chyawanprash', 'Classic Ayurvedic formulation to support immunity, strength, and respiratory wellness. Rich in Amla and revitalizing herbs.', 399.00, 'images/products/chavanprash.png', 'supplements', 10],
    ['Conditioner', 'Nourishing hair conditioner that smooths, detangles, and adds natural shine without weighing hair down.', 349.00, 'images/products/conditioner.png', 'haircare', 10],
    ['Face Pack', 'Clarifying herbal face pack that gently removes impurities and brightens the skin for a refreshed look.', 299.00, 'images/products/facepack.png', 'skincare', 10],
    ['Hair Colour', 'Ammonia-free hair color that provides natural-looking coverage and a healthy sheen.', 249.00, 'images/products/haircolor.png', 'haircare', 10],
    ['Herbal Hair Oil', 'Infused with traditional herbs to strengthen roots, reduce hair fall, and promote healthy growth.', 349.00, 'images/products/herbal hair oil.png', 'haircare', 10],
    ['Honey', 'Pure, natural honey with a rich aroma—perfect as a natural sweetener and daily wellness support.', 349.00, 'images/products/honey.png', 'food', 10],
    ['Milk Protein Shampoo', 'Gentle cleansing shampoo enriched with milk proteins to strengthen and soften hair.', 329.00, 'images/products/Milk protein shampoo.png', 'haircare', 10],
    ['Neem Shampoo', 'Purifying shampoo with neem to help control dandruff and maintain a healthy scalp.', 299.00, 'images/products/neem shampoo.png', 'haircare', 10],
    ['Pain Relief Oil', 'Traditional herbal oil for soothing muscle and joint discomfort with a warming, relaxing feel.', 399.00, 'images/products/pain relief oil.png', 'oils', 10],
    ['Pure Ghee', 'A2-inspired pure ghee with a rich aroma and golden texture—ideal for cooking and daily nourishment.', 699.00, 'images/products/Pure ghee.png', 'food', 10],
    ['Sahi Gulab Face Wash', 'Refreshing face wash with rose essence to gently cleanse, hydrate, and revitalize the skin.', 299.00, 'images/products/Sahi gulab Face wash.jpeg', 'skincare', 10],
];

// Remove all existing products (demo/placeholder)
$conn->query('DELETE FROM products');

$stmt = $conn->prepare('INSERT INTO products (name, description, price, image, category, stock, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())');
foreach ($rows as $r) {
    [$name, $desc, $price, $image, $category, $stock] = $r;
    // Normalize image paths (slashes, no leading slash)
    $image = str_replace('\\\\', '/', $image);
    $image = str_replace('\\', '/', $image);
    $image = ltrim($image, '/');
    $stmt->bind_param('ssdssi', $name, $desc, $price, $image, $category, $stock);
    $stmt->execute();
}

echo "Seeded 11 real products successfully.\n";
