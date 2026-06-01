<!--  صفحة نجاح الطلب success.php
 الوظيفة
 عرض رسالة نجاح بعد إتمام الطلب
 عرض رقم الطلب للعميل
 توفير زر تتبع الطلب
 توفير زر الرجوع للمتجر
 تشغيل مؤثر احتفالي Confetti -->
<?php

// إذا لم يوجد order_id → null
$order_id = $_GET['order_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>
        تم تأكيد الطلب
    </title>
    <meta name="viewport"content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/success.css">

</head>
<body>

<div class="success-card">

    <div class="success-icon">
       <i class="bi bi-check-lg"></i>
    </div>

    <!-- رسالة النجاح -->
    <h4 class="fw-bold mb-2">
        تم تأكيد طلبك 🎉
    </h4>

    <p class="text-muted mb-1">
        شكرًا لتسوقك معنا
    </p>

    <!-- عرض رقم الطلب -->
    <?php if ($order_id): ?>
        <div class="order-number">
            رقم الطلب:
            <?= htmlspecialchars($order_id) ?>
        </div>
    <?php endif; ?>

    <!-- زر تتبع الطلب -->
    <a href="track_order.php"
       class="btn btn-track w-100">
        📦 تتبع حالة الطلب
    </a>

    <!-- زر الرجوع للمتجر -->
    <a href="index.php"
       class="btn-home">
        ⬅ الرجوع للمتجر
    </a>

</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<!-- تشغيل المؤثر الاحتفالي -->
<script>

 window.onload = function() {
    confetti({
        // عدد القصاصات
        particleCount: 120,
        // مدى الانتشار
        spread: 70,
        // نقطة البداية
        origin: { y: 0.6 }
    });
 };
</script>

</body>
</html>