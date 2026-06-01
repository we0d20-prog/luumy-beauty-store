<!-- 
 صفحة عرض منتجات قسم معين products.php
الوظيفة
 جلب جميع المنتجات التابعة لقسم محدد
 عرض صورة المنتج
 عرض الاسم والسعر
 توفير زر إضافة للسلة
-->
<?php

include "includes/db.php";
include "includes/header.php";

// إذا لم يوجد category → اجعل القيمة 0
$category_id = $_GET['category'] ?? 0;

// جلب منتجات القسم
$stmt = $pdo->prepare("
    SELECT
        products.*,
        (
            SELECT image
            FROM product_images
            WHERE product_images.product_id = products.id
            LIMIT 1
        ) AS main_image
    FROM products
    WHERE category_id = ?
");
$stmt->execute([$category_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="assets/css/products.css?v=<?= time(); ?>">

<div class="container my-5">
    <div class="row">

        <!-- عرض المنتجات -->
        <?php foreach ($products as $product): ?>

        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="p-card">

                <!-- صورة المنتج -->
                <a href="product.php?id=<?= $product['id'] ?>"
                   class="p-img-box">
                    <img src="assets/images/<?= htmlspecialchars($product['main_image'] ?? 'no-image.png') ?>"
                         class="p-img"
                         alt="<?= htmlspecialchars($product['name']) ?>">
                </a>

                <!-- محتوى المنتج -->
                <div class="p-body">

                    <!-- اسم المنتج -->
                    <h6 class="p-title">
                        <?= htmlspecialchars($product['name']) ?>
                    </h6>

                    <!-- سعر المنتج -->
                    <div class="p-price">
                        <?= htmlspecialchars($product['price']) ?>
                        ج.م
                    </div>

                    <!-- زر إضافة للسلة -->
                    <button class="add-to-cart"
                            data-id="<?= $product['id'] ?>">
                        إضافة إلى الحقيبة
                    </button>

                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- إذا لا توجد منتجات -->
        <?php if(empty($products)): ?>

            <div class="col-12">
                <div class="alert alert-info text-center">
                    لا توجد منتجات داخل هذا القسم حالياً
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>