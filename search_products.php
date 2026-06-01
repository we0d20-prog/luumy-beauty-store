<!--  صفحة البحث عن المنتجات search_products.php
 الوظيفة
 البحث عن المنتجات بالاسم
 دعم البحث المباشر AJAX
 عرض نتائج البحث -->
<?php
include "includes/db.php";

$isAjax = isset($_GET['ajax']);
$search = trim($_GET['search'] ?? '');

if ($isAjax) {
    $stmt = $pdo->prepare("
        SELECT p.*, pi.image AS main_image
        FROM products p
        LEFT JOIN product_images pi ON pi.product_id = p.id
        WHERE p.name LIKE ?
        GROUP BY p.id
        LIMIT 5
    ");
    $stmt->execute(["%$search%"]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        echo '
        <a href="product.php?id='.$p['id'].'">
            <img src="assets/images/'.($p['main_image'] ?? 'no-image.png').'">
            <div class="search-info">
                <span class="search-name">'.htmlspecialchars($p['name']).'</span>
                <span class="search-price">'.$p['price'].' ج.م</span>
            </div>
        </a>';
    }
    exit;
}
?>