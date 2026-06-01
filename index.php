<!-- هذه الصفحة هي الصفحة الرئيسية للموقع index.php
 عرض البانر الرئيسي
عرض الأقسام
عرض أحدث المنتجات
عرض منتجات مخصصة (العناية اليومية)
عرض بانرات دعائية
نقل المستخدم لصفحات المنتجات
إضافة المنتجات-->
<?php
include "includes/header.php";
include "includes/db.php";
?>

<link rel="stylesheet" href="assets/css/main.css">
<link rel="stylesheet" href="assets/css/products.css?v=<?= time(); ?>">
<?php

// جلب جميع الأقسام من قاعدة البيانات
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب أحدث 4 منتجات
$latestStmt = $pdo->query("SELECT * FROM products  ORDER BY id DESC  LIMIT 4");
$latestProducts = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

// جلب منتجات قسم "العناية اليومية"
$promoStmt = $pdo->prepare("
    SELECT p.* 
    FROM products p
    JOIN categories c 
    ON p.category_id = c.id
    WHERE c.name = ?
    ORDER BY p.id DESC
    LIMIT 3
");
$promoStmt->execute(['العناية اليومية']);
$promoProducts = $promoStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section -->
<section class="hero-clean">

    <div class="container-fluid p-0">
        <div class="row g-0 flex-column-reverse flex-lg-row-reverse align-items-stretch">

            <div class="col-lg-6 hero-image">
                <img src="assets/images/hero-beauty.webp" alt="Beauty">
            </div>

            <div class="col-lg-6 hero-content">
                <div class="hero-text text-center">

                    <!-- اللوقو -->
                    <img src="assets/images/logo-hero.webp"
                         alt="Logo"
                         class="hero-logo">

                    <!-- العنوان -->
                    <h1> كل ما تحتاجينه... في مكان واحد </h1>
                       
                    <!-- الوصف -->
                    <p> مكياج، عناية، عدسات من أشهر الماركات بأسعار تنافسية</p>
                       
                    <!-- زر التسوق -->
                    <a href="products.php?category=<?= $categories[4]['id'] ?? '' ?>"
                       class="btn btn-brand px-4 py-2">
                        تسوقي الآن
                    </a>

                </div>
            </div>

        </div>
    </div>

</section>

<!-- قسم الأقسام -->
<div class="container py-5 category-section">
    <!-- عنوان القسم -->
    <h3 class="text-center mb-5 fw-bold section-title">
        تسوقي حسب القسم
    </h3>

    <!-- سلايدر الأقسام -->
    <div class="category-slider">
        <div class="category-track">

            <?php foreach ($categories as $cat): ?>

                <!-- عنصر قسم -->
                <a href="products.php?category=<?= $cat['id'] ?>"
                   class="category-item">

                    <!-- صورة القسم -->
                    <div class="category-image">
                        <img src="assets/images/<?= htmlspecialchars($cat['image']) ?>"
                             alt="<?= htmlspecialchars($cat['name']) ?>">
                    </div>

                    <!-- اسم القسم -->
                    <p class="category-name">
                        <?= htmlspecialchars($cat['name']) ?>
                    </p>
                </a>

            <?php endforeach; ?>

        </div>
    </div>
</div>

<!-- أحدث المنتجات -->
<div class="container py-5">
    <h3 class="text-center mb-5 fw-bold">
        أحدث المنتجات
    </h3>

    <div class="row">

        <?php foreach ($latestProducts as $product): ?>

            <?php

            // جلب أول صورة للمنتج
            $imageStmt = $pdo->prepare("
                SELECT image
                FROM product_images
                WHERE product_id = ?
                LIMIT 1
            ");
            $imageStmt->execute([$product['id']]);
            $imageRow = $imageStmt->fetch(PDO::FETCH_ASSOC);

            // صورة افتراضية إذا لا توجد صورة
            $productImage = $imageRow
                ? $imageRow['image']
                : 'no-image.png';
            ?>

            <!-- كرت المنتج -->
            <div class="col-lg-3 col-md-6 col-6 mb-4">
                <div class="p-card">

                    <!-- صورة المنتج -->
                    <a href="product.php?id=<?= $product['id'] ?>"
                       class="p-img-box">
                        <img src="assets/images/<?= htmlspecialchars($product['main_image'] ?? $productImage ?? 'no-image.png') ?>"
                             class="p-img"
                             alt="<?= htmlspecialchars($product['name'] ?? 'منتج') ?>">
                    </a>

                    <!-- محتوى المنتج -->
                    <div class="p-body">
                        <!-- اسم المنتج -->
                        <h6 class="p-title">
                            <?= htmlspecialchars($product['name']) ?>
                        </h6>

                        <!-- السعر -->
                        <div class="p-price">
                            <?= $product['price'] ?> ج.م
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
    </div>
</div>

<!-- بانر العدسات -->
<section class="promo-banner">
    <div class="container">
        <div class="promo-box">
            <!-- صورة يسار -->
            <div class="promo-img-left">
                <img src="assets/images/banner-left.webp" alt="">
            </div>

            <!-- النص -->
            <div class="promo-content">
                <h2> نظرة تسرق القلوب </h2>
                <p> عدسات طبية وتجميلية بألوان ساحرة وآمنة </p>
                <a href="products.php?category=4" class="btn btn-brand px-4 py-2">
                    تسوقي الآن
                </a>
            </div>

            <!-- صورة يمين -->
            <div class="promo-img-right">
                <img src="assets/images/banner-right.webp" alt="">
            </div>
        </div>
    </div>
</section>

<!-- قسم العناية اليومية -->
<section class="beauty-promo">

    <div class="container">
        <div class="row align-items-center">
            <!-- النص -->
            <div class="col-12 col-lg-4 promo-text">

                <span class="promo-subtitle">
                    بشرة صحية ومشرقة
                </span>

                <h3 class="promo-title">
                    عناية متكاملة تناسب جميع أنواع البشرة
                </h3>

                <a href="products.php?category=1"
                   class="btn btn-brand">
                    اكتشفي المزيد
                </a>

            </div>

            <!-- المنتجات -->
            <div class="col-12 col-lg-8">
                <div class="row">

                    <?php foreach ($promoProducts as $product): ?>

                        <?php
                        // جلب صورة المنتج
                        $imageStmt = $pdo->prepare("
                            SELECT image
                            FROM product_images
                            WHERE product_id = ?
                            LIMIT 1
                        ");
                        $imageStmt->execute([$product['id']]);
                        $imageRow = $imageStmt->fetch(PDO::FETCH_ASSOC);

                        $productImage = $imageRow
                            ? $imageRow['image']
                            : 'no-image.png';
                        ?>

                        <!-- كرت المنتج -->
                        <div class="col-md-4 col-6 mb-4 d-flex">
                            <div class="p-card">

                                <!-- صورة المنتج -->
                                <a href="product.php?id=<?= $product['id'] ?>"
                                   class="p-img-box">
                                    <img src="assets/images/<?= htmlspecialchars($product['main_image'] ?? $productImage ?? 'no-image.png') ?>"
                                         class="p-img"
                                         alt="<?= htmlspecialchars($product['name']) ?>">
                                </a>

                                <!-- محتوى المنتج -->
                                <div class="p-body">

                                    <!-- اسم المنتج -->
                                    <h6 class="p-title">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </h6>

                                    <!-- السعر -->
                                    <div class="p-price">
                                        <?= $product['price'] ?> ج.م
                                    </div>

                                    <!-- زر السلة -->
                                    <button class="add-to-cart"
                                            data-id="<?= $product['id'] ?>">
                                        إضافة إلى الحقيبة
                                    </button>

                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</section>

<!-- قسم المكياج والعناية -->
<section class="beauty-section">

    <div class="container-fluid p-0">
        <div class="row g-0">

            <!-- النص -->
            <div class="col-md-6 d-flex align-items-center justify-content-center text-center beauty-text">

                <div>
                    <h2> مكياج يدوم… ويبهرِك </h2>
                       
                    <p> من كريم الأساس للروج – اختاري طلتك </p>
                       
                    <a href="products.php?category=2"
                       class="btn btn-brand">
                        تسوقي أفضل المكياج
                    </a>
                </div>

            </div>

            <!-- الصورة -->
            <div class="col-12 col-md-6">
                <img src="assets/images/makeup.webp"
                     class="img-fluid w-100"
                     style="object-fit: cover; height: 100%;">
            </div>

        </div>

    </div>

    <!-- قسم العناية -->
    <div class="container-fluid p-0">
        <div class="row g-0">

            <!-- الصورة -->
            <div class="col-12 col-md-6">
                <img src="assets/images/skincare.webp"
                     class="img-fluid w-100 h-100"
                     style="object-fit: cover;">
            </div>

            <!-- النص -->
            <div class="col-md-6 d-flex align-items-center justify-content-center text-center beauty-text-gray">
                <div>

                    <h2> راحتك تبدأ من هنا </h2>
                    
                    <p> عناية متكاملة ببشرتك وجسمك من أفضل الماركات</p>
                      
                    <a href="products.php?category=5"
                       class="btn btn-brand">
                        اكتشفي منتجات العناية
                    </a>

                </div>
            </div>
        </div>
    </div>

</section>

<!-- قسم الأظافر -->
<section class="nail-hero-section">

    <div class="container">
        <div class="nail-hero-box">

            <!-- صورة يسار - مع تحسين تعبئة الفراغ -->
            <div class="nail-hero-img-box nail-hero-left">
                <img src="assets/images/nails-left.webp" alt="أظافر يسار">
            </div>

            <!-- النص -->
            <div class="nail-hero-content">

                <h2>في دقائق أظافر مثالية</h2>
                   
                <p> كل الأدوات والتصاميم بإطلالة أنثوية متكاملة</p>
                
                <a href="products.php?category=3"
                   class="btn btn-brand px-4 py-2">
                    تسوقي الآن
                </a>

            </div>

            <!-- صورة يمين - مع تحسين تعبئة الفراغ -->
            <div class="nail-hero-img-box nail-hero-right">
                <img src="assets/images/nails-right.webp" alt="أظافر يمين">
            </div>

        </div>
    </div>

</section>

<?php include "includes/footer.php"; ?>