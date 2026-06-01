<!-- 
 صفحة عرض السلة cart.php  
 وظيفتها 
 جلب المنتجات الموجودة داخل السلة من  Session 
عرض المنتجات للمستخدم 
 التحكم بالكمية (+ / -) 
 حذف المنتجات 
حساب الإجمالي النهائي  
 الانتقال إلى صفحة الدفع checkout.php 
-->
<?php
session_start();
include "includes/db.php";
include "includes/header.php";

// إذا لم تكن السلة موجودة، نعطي مصفوفة فارغة
$cart = $_SESSION['cart'] ?? [];
$total = 0; // الإجمالي الكلي للسلة

// عرض رسائل الخطأ إن وجدت
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
    unset($_SESSION['error']); // حذف الرسالة بعد عرضها
}
?>

<link rel="stylesheet" href="assets/css/cart.css">

<div class="container my-5">
    <div class="row">
        
        <!-- المنتجات داخل السلة -->
        <div class="col-lg-8">
            <h4 class="cart-title fw-bold mb-4">حقيبة المشتريات</h4>
            
            <?php if (empty($cart)): ?>
                <!-- حالة السلة الفارغة -->
                <div class="alert alert-info text-center py-4">
                    الحقيبة فارغة حالياً
                </div>
            <?php else: ?>
                
                <?php
                // عرض المنتجات داخل السلة
                foreach ($cart as $id => $qty):
                    // جلب بيانات المنتج من قاعدة البيانات
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // إذا المنتج غير موجود في قاعدة البيانات، نحذفه من السلة
                    if (!$product) {
                        unset($_SESSION['cart'][$id]);
                        continue;
                    }
                    
                    // جلب أول صورة للمنتج
                    $imgStmt = $pdo->prepare("
                        SELECT image 
                        FROM product_images 
                        WHERE product_id = ? 
                        ORDER BY id ASC 
                        LIMIT 1
                    ");
                    $imgStmt->execute([$id]);
                    $image = $imgStmt->fetchColumn() ?? 'no-image.png';
                    
                    // حساب سعر المنتج × الكمية
                    $subtotal = $product['price'] * $qty;
                    
                    // إضافة إلى الإجمالي الكلي
                    $total += $subtotal;
                ?>
                
                <!-- عنصر واحد في السلة -->
                <div class="cart-item d-flex align-items-center mb-4 p-3">
                    
                    <!-- صورة المنتج -->
                    <a href="product.php?id=<?= $product['id'] ?>">
                        <img src="assets/images/<?= htmlspecialchars($image) ?>" 
                             class="cart-img" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                    </a>
                    
                    <!-- اسم المنتج -->
                    <div class="flex-grow-1 mx-3">
                        <h6 class="mb-1 product-name"><?= htmlspecialchars($product['name']) ?></h6>
                    </div>
                    
                    <!-- التحكم في الكمية -->
                    <div class="qty-box">
                        <a href="update_cart.php?id=<?= $id ?>&action=minus" class="qty-btn">−</a>
                        <span class="qty-value"><?= $qty ?></span>
                        <a href="update_cart.php?id=<?= $id ?>&action=plus" class="qty-btn">+</a>
                    </div>
                    
                    <!-- سعر المنتج × الكمية -->
                    <div class="mx-3 fw-bold text-nowrap">
                        <?= $subtotal ?> ج.م
                    </div>
                    
                    <!-- زر حذف المنتج -->
                    <a href="remove_from_cart.php?id=<?= $id ?>" 
                       class="delete-btn"
                       onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟');">
                        <i class="bi bi-trash3-fill"></i>
                    </a>
                </div>
                
                <?php endforeach; ?>
                
            <?php endif; ?>
        </div>
        
        <!-- ملخص السلة -->
        <div class="col-lg-4">
            <div class="summary-box sticky-top">
                <h5 class="summary-title">إجمالي الحقيبة</h5>
                
                <!-- الإجمالي قبل الشحن -->
                <div class="d-flex justify-content-between mb-2">
                    <span>الإجمالي المقدر</span>
                    <span class="fw-bold"><?= $total ?> ج.م</span>
                </div>
                
                <hr>
                
                <!-- زر الانتقال إلى صفحة إتمام الطلب -->
                <a href="checkout.php" 
                   class="btn btn-brand w-100 py-2 <?= empty($cart) ? 'disabled' : '' ?>">
                    إتمام الشراء
                </a>
            </div>
        </div>
        
    </div>
</div>

<?php include "includes/footer.php"; ?>