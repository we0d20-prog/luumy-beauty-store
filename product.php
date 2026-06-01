<!--  صفحة عرض تفاصيل المنتج product.php
 الوظيفة
 عرض بيانات المنتج
 عرض صور المنتج
 عرض التقييم والسعر والمخزون
 إضافة المنتج للسلة
 عرض المنتجات المشابهة-->
<?php

include "includes/db.php";
include "includes/header.php";

// التحقق من وجود ID المنتج
if (!isset($_GET['id'])) {
    echo "
    <div class='alert alert-danger text-center'>
        المنتج غير موجود
    </div>
    ";
    include "includes/footer.php";
    exit;
}
// جلب ID المنتج من الرابط
$id = $_GET['id'];

// جلب بيانات المنتج من قاعدة البيانات
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

// إذا المنتج غير موجود
if (!$product) {
    echo "
    <div class='alert alert-danger text-center'>
        المنتج غير موجود
    </div>
    ";
    include "includes/footer.php";
    exit;
}

// جلب جميع صور المنتج
$imgStmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ?");
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll();

// إذا توجد صور → استخدم أول صورة
// إذا لا توجد → استخدم صورة افتراضية
$mainImage = !empty($images) ? $images[0]['image']: 'no-image.png';

// جلب المنتجات المشابهة
$similarStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 4");
$similarStmt->execute([$product['category_id'],$id]);
$similarProducts = $similarStmt->fetchAll();

// إذا المنتجات المشابهة قليلة
// اجلب منتجات عشوائية بديلة
if (count($similarProducts) < 3) {

    $fallbackStmt = $pdo->prepare(" SELECT * FROM products WHERE id != ? LIMIT 5");
    $fallbackStmt->execute([$id]);
    $similarProducts = $fallbackStmt->fetchAll();
}
?>

<link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="assets/css/product.css?v=<?= time(); ?>">

<div class="container product-container">
    <div class="product-row">

        <!-- قسم الصور -->
        <div class="product-media">

            <!-- الصورة الرئيسية -->
            <div class="main-image">
                <img id="mainProductImage"
                     src="assets/images/<?php echo htmlspecialchars($mainImage); ?>"
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <!-- الصور المصغرة -->
            <?php if(count($images) > 1): ?>
            <div class="thumb-gallery" id="thumbnailGallery">
                <?php foreach($images as $index => $img): ?>
                <div class="thumb <?php echo $index === 0 ? 'active' : ''; ?>"
                     data-image="<?php echo htmlspecialchars($img['image']); ?>">
                    <img src="assets/images/<?php echo htmlspecialchars($img['image']); ?>"
                         alt="صورة مصغرة">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>

        <!-- تفاصيل المنتج -->
        <div class="product-details">

            <!-- شارة الأكثر مبيعًا -->
            <div class="best-seller-badge">
                <span>
                    <i class="fas fa-crown"></i>
                    الأكثر مبيعاً
                </span>
                <span>
                    🔥 مبيعات قياسية
                </span>
            </div>

            <!-- اسم المنتج -->
            <h1 class="product-title">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>

            <!-- إحصائيات -->
            <div class="stats-row">

                <!-- عدد المشاهدات -->
                <div class="view-counter">
                    <i class="fas fa-eye"></i>
                    <span id="fakeViewCount">
                        <?php echo number_format(rand(1200, 4500)); ?>
                    </span>
                    مشاهدات
                </div>

                <!-- عدد المشترين -->
                <div class="recent-sales">
                    <i class="fas fa-shopping-bag"></i>
                    <span id="recentPurchaseCount">
                        <?php echo rand(15, 35); ?>
                    </span>
                    شخص اشتروا مؤخراً
                </div>

            </div>

            <!-- التقييم -->
            <div class="rating">

                <div class="stars">
                    <?php
                    // التقييم الافتراضي
                    $rating = $product['rating'] ?? 4.5;
                    // عدد النجوم الكاملة
                    $full = floor($rating);
                    // هل توجد نصف نجمة؟
                    $half = ($rating - $full) >= 0.5;
                    for($i = 1; $i <= 5; $i++):
                        // نجمة كاملة
                        if($i <= $full):
                    ?>
                        <i class="fas fa-star"></i>
                    <?php elseif($half && $i == $full+1): ?>
                        <!-- نصف نجمة -->
                        <i class="fas fa-star-half-alt"></i>
                    <?php else: ?>
                        <!-- نجمة فارغة -->
                        <i class="far fa-star"></i>
                    <?php endif; endfor; ?>
                </div>

                <!-- نص التقييم -->
                <div class="rating-text">
                    (
                    <?php echo number_format($product['reviews_count'] ?? rand(20, 150)); ?>
                    تقييم
                    )
                    •
                    <?php echo number_format($rating, 1); ?>
                    من 5
                </div>

            </div>

            <!-- السعر -->
            <div class="price">
                <?php echo number_format($product['price'], 2); ?>
                ج.م
                <small>
                    شامل الضريبة
                </small>
            </div>

            <!-- معلومات المنتج -->
            <div class="info-box">

                <!-- حالة المخزون -->
                <div class="stock-status <?php echo ($product['stock'] ?? 0) > 0 ? '' : 'out'; ?>">
                    <i class="fas <?php echo ($product['stock'] ?? 0) > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <?php echo ($product['stock'] ?? 0) > 0
                        ? 'متوفر في المخزون'
                        : 'نفد من المخزون'; ?>
                </div>

                <!-- الباركود -->
                <div class="barcode">
                    <i class="fas fa-barcode"></i>
                    <?php echo htmlspecialchars($product['barcode'] ?? '6287029924300'); ?>
                </div>

            </div>

            <!-- زر الإضافة للسلة -->
            <div class="cart-section">
                <button class="main-add-to-cart"
                        id="addToCartBtn"
                        data-id="<?php echo $product['id']; ?>"
                        data-name="<?php echo htmlspecialchars($product['name']); ?>"
                        data-price="<?php echo $product['price']; ?>">
                    إضافة إلى الحقيبة
                </button>
            </div>

            <!-- رسالة الإضافة للسلة -->
            <div class="fake-popup" id="cartMessage"></div>

            <!-- وصف المنتج -->
            <div class="description-section">
                <div class="desc-header">
                    📖 الوصف
                </div>
                <div id="descriptionWrapper">
                    <!-- نص الوصف -->
                    <div id="descriptionText"
                         class="desc-text desc-short">
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $product['description']
                                ?? 'لا يوجد وصف لهذا المنتج حالياً.'
                            )
                        );
                        ?>
                    </div>

                    <!-- زر اقرأ المزيد -->
                    <?php if(strlen($product['description'] ?? '') > 200): ?>
                    <button id="readMoreBtn"
                            class="read-more-btn">
                        اقرأ المزيد
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <?php endif; ?>

                </div>

            </div>
        </div>
    </div>

    <!-- المنتجات المشابهة -->
    <?php if(!empty($similarProducts)): ?>
    <div class="similar-section">
        <div class="similar-title">
            <i class="fas fa-tags"></i>
            منتجات مشابهة
        </div>

        <div class="similar-grid">
            <?php foreach($similarProducts as $similar): ?>
                <?php
                // جلب صورة المنتج المشابه
                $similarImgStmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ? LIMIT 1");
                $similarImgStmt->execute([$similar['id']]);
                $similarImg = $similarImgStmt->fetch();
                ?>
                <!-- كرت المنتج المشابه -->
                <div class="similar-card"
                     onclick="window.location.href='product.php?id=<?php echo $similar['id']; ?>'">
                    <!-- الصورة -->
                    <div class="similar-img">
                        <img src="assets/images/<?php echo htmlspecialchars($similarImg['image'] ?? 'no-image.png'); ?>"
                             alt="<?php echo htmlspecialchars($similar['name']); ?>">
                    </div>

                    <!-- الاسم -->
                    <p>
                        <?php echo htmlspecialchars($similar['name']); ?>
                    </p>

                    <!-- السعر -->
                    <div class="similar-price">
                        <?php echo number_format($similar['price'], 2); ?>
                        ج.م
                    </div>

                    <!-- زر السلة -->
                    <button class="add-to-cart"
                            data-id="<?php echo $similar['id']; ?>"
                            data-name="<?php echo htmlspecialchars($similar['name']); ?>">
                        إضافة إلى الحقيبة
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    // تغيير الصورة الرئيسية عند الضغط على الصور المصغرة
    const thumbs = document.querySelectorAll('.thumb');
    const mainImage = document.getElementById('mainProductImage');

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            // جلب الصورة الجديدة
            const imgSrc = thumb.querySelector('img').src;
            // تغيير الصورة الرئيسية
            mainImage.src = imgSrc;
            // حذف active من جميع الصور
            thumbs.forEach(t => t.classList.remove('active'));
            // إضافة active للصورة الحالية
            thumb.classList.add('active');
        });
    });

    // اقرأ المزيد / إظهار أقل
    const readBtn = document.getElementById('readMoreBtn');
    const descText = document.getElementById('descriptionText');

    let expanded = false;
    readBtn?.addEventListener('click', () => {
        if(expanded) {
            // تصغير النص
            descText.classList.add('desc-short');
            readBtn.innerHTML =
                'اقرأ المزيد <i class="fas fa-chevron-down"></i>';
        } else {

            // إظهار النص كامل
            descText.classList.remove('desc-short');
            readBtn.innerHTML =
                'إظهار أقل <i class="fas fa-chevron-up"></i>';
        }
        expanded = !expanded;
    });

    // عداد المشاهدات 
    let viewCount = parseInt(
        document.getElementById('fakeViewCount')
        ?.innerText
        .replace(/,/g, '')
        || 2430
    );


    // زيادة المشاهدات كل 10 ثواني
    setInterval(() => {
        viewCount += Math.floor(Math.random() * 7) + 2;
        const formatted = viewCount > 9999
            ? (viewCount / 1000).toFixed(1) + 'k'
            : viewCount.toLocaleString();

        if(document.getElementById('fakeViewCount')) {
            document.getElementById('fakeViewCount').innerText = formatted;
        }
    }, 10000);

</script>

<?php include "includes/footer.php"; ?>