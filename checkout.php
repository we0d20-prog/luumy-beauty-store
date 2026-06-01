<!-- صفحة اتمام الطلب 
 عرض ملخص الطلب
عرض المنتجات الموجودة بالسلة
حساب الإجمالي والشحن
إدخال بيانات العميل
اختيار طريقة الشحن
اختيار طريقة الدفع
إرسال الطلب إلى place_order.php-->
<?php

session_start();

include "includes/db.php";
include "includes/header.php";

$shipping_cairo   = 40;   // تكلفة الشحن داخل القاهرة
$shipping_outside = 70;   // تكلفة الشحن خارج القاهرة

//جلب السلة من الجلسة 
$cart = $_SESSION['cart'] ?? [];   // إذا لم تكن السلة موجودة، نعطي مصفوفة فارغة
$subtotal = 0;   // إجمالي السعر قبل الشحن
?>

<link rel="stylesheet" href="assets/css/checkout.css">

<div class="container my-5">
    <div class="row g-4">

        <!-- ملخص الطلب  -->
        <div class="col-lg-5">
            <div class="checkout-box">

                <!-- رأس الملخص (قابل للطي على الموبايل) -->
                <div class="summary-header" onclick="toggleSummary()">
                    <h5 class="mb-0 fw-bold">تفاصيل الفاتورة</h5>
                    <span id="arrow">▼</span>
                </div>

                <div class="summary-body mt-3" id="summaryBody">

                    <?php foreach ($cart as $product_id => $quantity): ?>

                        <?php
                        // جلب بيانات المنتج من قاعدة البيانات
                        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->execute([$product_id]);
                        $product = $stmt->fetch();

                        // جلب أول صورة للمنتج
                        $imgStmt = $pdo->prepare("
                            SELECT image 
                            FROM product_images 
                            WHERE product_id = ? 
                            ORDER BY id ASC 
                            LIMIT 1
                        ");
                        $imgStmt->execute([$product_id]);
                        $image = $imgStmt->fetchColumn() ?? 'no-image.png';

                        // إذا لم يوجد المنتج، نتخطاه
                        if (!$product) continue;

                        // حساب سعر هذا المنتج × الكمية
                        $line_total = $product['price'] * $quantity;
                        $subtotal += $line_total;
                        ?>

                        <div class="checkout-item">
                            <a href="product.php?id=<?= $product['id'] ?>">
                                <img src="assets/images/<?= htmlspecialchars($image) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                            </a>
                            <div class="flex-grow-1">
                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                <div class="text-muted small">الكمية: <?= $quantity ?></div>
                            </div>

                            <span class="fw-bold"><?= $line_total ?> ج.م</span>
                        </div>

                    <?php endforeach; ?>

                    <hr>

                    <!-- المجموع قبل الشحن -->
                    <div class="d-flex justify-content-between">
                        <span>المجموع</span>
                        <strong id="subtotal"><?= $subtotal ?> ج.م</strong>
                    </div>

                    <!-- تكلفة الشحن -->
                    <div class="d-flex justify-content-between mt-2">
                        <span>الشحن</span>
                        <strong id="shipping"></strong>
                    </div>

                    <hr>

                    <!-- الإجمالي النهائي -->
                    <div class="d-flex justify-content-between fs-5 fw-bold">
                        <span>الإجمالي</span>
                        <span id="total"><?= $subtotal ?> ج.م</span>
                    </div>

                </div>
            </div>
        </div>

        <!-- بيانات الشحن والدفع -->
        <div class="col-lg-7">
            <div class="checkout-box">

                <h5 class="mb-4 fw-bold">بيانات الشحن</h5>
                 <!-- بيانات العميل -->
                <form method="post" action="place_order.php" onsubmit="return checkCart()">
                    <input type="text" name="name" class="form-control mb-3 payment-input"
                           placeholder="الاسم الكامل" required>

                    <input type="tel" name="phone" class="form-control mb-3 payment-input"
                           placeholder="رقم الهاتف" dir="RTL" required>

                    <input type="text" name="address" class="form-control mb-3 payment-input"
                           placeholder="العنوان بالتفصيل" required>

                   <!--  طريقة الشحن -->
                    <h6 class="mt-4 fw-bold">طريقة الشحن</h6>

                    <div class="form-check shipping-option">
                        <input class="form-check-input" type="radio" name="shipping" 
                               value="<?= $shipping_cairo ?>" checked
                               onclick="calcTotal(this.value)">
                        <label class="form-check-label">
                            داخل القاهرة (<?= $shipping_cairo ?> ج.م)
                        </label>
                    </div>

                    <div class="form-check shipping-option">
                        <input class="form-check-input" type="radio" name="shipping" 
                               value="<?= $shipping_outside ?>"
                               onclick="calcTotal(this.value)">
                        <label class="form-check-label">
                            خارج القاهرة (<?= $shipping_outside ?> ج.م)
                        </label>
                    </div>

                    <!-- طريقة الدفع -->
                    <h6 class="mt-4 fw-bold">💳 طريقة الدفع</h6>

                    <!-- الدفع عند الاستلام -->
                    <div class="form-check shipping-option">
                        <input class="form-check-input" type="radio" name="payment" 
                               value="cod" checked>
                        <label class="form-check-label">الدفع عند الاستلام</label>
                    </div>

                    <!-- الدفع بالبطاقة -->
                    <div class="form-check shipping-option">
                        <input class="form-check-input" type="radio" name="payment" 
                               value="card">
                        <label class="form-check-label">الدفع بالبطاقة</label>
                    </div>

                    <!-- حقول بيانات البطاقة (تظهر فقط عند اختيار الدفع بالبطاقة) -->
                    <div id="cardFields" style="display: none;">
                        <input type="text" class="form-control mb-2 payment-input"
                               placeholder="رقم البطاقة">

                        <div class="row">
                            <div class="col">
                                <input type="text" class="form-control payment-input"
                                       placeholder="MM / YY">
                            </div>
                            <div class="col">
                                <input type="text" class="form-control payment-input"
                                       placeholder="CVV">
                            </div>
                        </div>
                    </div>

                    <!-- زر التأكيد -->
                    <button type="submit" class="btn btn-primary w-100 mt-4 py-3 d-none d-md-block">
                        تأكيد الطلب والدفع
                    </button>

                </form>
            </div>
        </div>

    </div>
</div>

<!--  زر ثابت في الأسفل للموبايل  -->
<div class="mobile-fixed-btn">
    <button onclick="if(checkCart()) document.querySelector('form').submit()"
            class="btn btn-primary w-100 py-3">
        تأكيد الطلب – <span id="mobileTotal"><?= $subtotal ?> ج.م</span>
    </button>
</div>


<script>
  
 // دالة حساب الإجمالي عند تغيير طريقة الشحن
    function calcTotal(shipping) {
        const subtotal = <?= $subtotal ?>;const total    = subtotal + parseInt(shipping);

        document.getElementById("shipping").innerText     = shipping + " ج.م";
        document.getElementById("total").innerText        = total + " ج.م";
        document.getElementById("mobileTotal").innerText  = total + " ج.م";
    }

    // تشغيل الحساب أول مرة (القاهرة افتراضيًا)
    calcTotal(<?= $shipping_cairo ?>);
    // دالة إظهار/إخفاء ملخص الفاتورة (للموبايل)
    function toggleSummary() {
        const body  = document.getElementById('summaryBody');
        const arrow = document.getElementById('arrow');

        if (body.style.display === 'none') {
            body.style.display = 'block';
            arrow.innerHTML = '▲';
        } else {
            body.style.display = 'none';
            arrow.innerHTML = '▼';
        }
    }

    // إظهار/إخفاء حقول البطاقة حسب طريقة الدفع
    document.addEventListener("DOMContentLoaded", function() {
        
        const paymentRadios = document.querySelectorAll('input[name="payment"]');
        const cardFields    = document.getElementById("cardFields");

        paymentRadios.forEach(radio => {
            radio.addEventListener("change", function() {
                if (this.value === "card") {
                    cardFields.style.display = "block";
                } else {
                    cardFields.style.display = "none";
                }
            });
        });

        // التحقق من أن السلة ليست فارغة قبل إرسال الطلب
        window.checkCart = function() {
            const cartCount = <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>;

            if (cartCount === 0) {
                alert("السلة فاضية! يجب إضافة منتج واحد على الأقل.");
                return false;
            }
            return true;
        };
    });

    const FREE_SHIPPING = 999;

</script>

<?php include "includes/footer.php";?>
