<!--  صفحة تتبع الطلب track_order.php
 الوظيفة
 البحث عن الطلب باستخدام رقم الهاتف
 عرض حالة الطلب الحالية
 عرض المنتجات داخل الطلب
 عرض حالة طلبات الإرجاع
 السماح بطلب إرجاع لكل منتج -->
<?php

session_start();
include "includes/db.php";
include "includes/header.php";

// بيانات الطلب
$order = null;
// منتجات الطلب
$items = [];
// بيانات الإرجاع
$refundData = null;

// جلب الطلب عبر رقم الهاتف
if (
    isset($_GET['phone'])
    &&
    !empty($_GET['phone'])
) {
    // تنظيف رقم الهاتف
    $phone = trim($_GET['phone']);

    // جلب آخر طلب مرتبط برقم الهاتف
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE phone = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$phone]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // إذا تم العثور على الطلب
    if ($order) {
        // جلب المنتجات داخل الطلب
        $stmtItems = $pdo->prepare("
            SELECT
                order_items.product_id,
                order_items.quantity,
                order_items.price,
                products.name,
                (
                    SELECT image
                    FROM product_images
                    WHERE product_images.product_id = products.id
                    LIMIT 1
                ) AS image
            FROM order_items
            JOIN products
            ON order_items.product_id = products.id
            WHERE order_items.order_id = ?
        ");
        $stmtItems->execute([$order['id']]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // جلب حالة الإرجاع
        $stmtRefund = $pdo->prepare(" SELECT status FROM refund_requests WHERE order_id = ? ORDER BY id DESC LIMIT 1 ");
        $stmtRefund->execute([$order['id']]);
        $refundData = $stmtRefund->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport"content="width=device-width, initial-scale=1">
      
<title>تتبع الطلب</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/track_order.css">

</head>
<body>

<div class="container-track">

<!-- مربع البحث -->
<div class="card-beauty text-center">
<h4 class="title mb-3">تتبعي طلبك</h4>

<!-- فورم البحث -->
<form method="GET">
<input type="tel" name="phone" class="form-control mb-3" placeholder="أدخلي رقم الهاتف" dir="RTL" required>
<button class="btn-track w-100">عرض الطلب</button>
</form>

</div>

<!-- إذا تم العثور على طلب -->
<?php if($order): ?>

<!-- معلومات الطلب -->
<div class="card-beauty">

<strong>رقم الطلب:</strong>#<?= $order['id'] ?>

<br>

<!-- حالة الطلب -->
<strong>الحالة:</strong>

<?php

// الحالة الافتراضية
$statusClass = "secondary";
$statusText  = $order['status'];

// طلب جديد
if($order['status'] == "new") {
    $statusClass = "warning";
    $statusText =
    '<i class="fa-solid fa-bag-shopping"></i> طلب جديد';
}

// قيد التنفيذ
if($order['status'] == "processing") {
    $statusClass = "info";
    $statusText =
    '<i class="fa-solid fa-gear fa-spin"></i> قيد التنفيذ';
}

// تم الشحن
if($order['status'] == "shipped") {
    $statusClass = "primary";
    $statusText =
    '<i class="fa-solid fa-truck"></i> تم الشحن';
}

// مكتمل
if($order['status'] == "completed") {
    $statusClass = "success";
    $statusText =
    '<i class="fa-solid fa-circle-check"></i> مكتمل';
}

// ملغي
if($order['status'] == "cancelled") {
    $statusClass = "danger";
    $statusText =
    '<i class="fa-solid fa-ban"></i> ملغي';
}

// تم الاسترجاع
if($order['status'] == "refunded") {
    $statusClass = "dark";
    $statusText =
    '<i class="fa-solid fa-money-bill-wave"></i> تم الاسترجاع';
}
?>

<!-- Badge الحالة -->
<span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>

<br>

<!-- إجمالي الطلب -->
<strong>المبلغ:</strong>

<?= $order['total'] ?? $order['total_price'] ?? 0 ?>ج.م</div>

<!-- حالة الإرجاع -->
<?php if($refundData): ?>

<?php

$refundText = $refundData['status'];
$refundClass = "warning";

// قيد المراجعة
if($refundData['status'] == "pending") {
    $refundText =
    '<i class="fa-solid fa-hourglass-half"></i> قيد المراجعة';
    $refundClass = "warning";
}

// تمت الموافقة
if($refundData['status'] == "approved") {
    $refundText =
    '<i class="fa-solid fa-circle-check"></i> تمت الموافقة';
    $refundClass = "success";
}

// مرفوض
if($refundData['status'] == "rejected") {
    $refundText =
    '<i class="fa-solid fa-circle-xmark"></i> مرفوض';
    $refundClass = "danger";
}

// تم تحويل المبلغ
if($refundData['status'] == "refunded") {
    $refundText =
    '<i class="fa-solid fa-money-bill-transfer"></i> تم تحويل المبلغ';
    $refundClass = "dark";
}
?>

<!-- تنبيه حالة الإرجاع -->
<div class="alert alert-<?= $refundClass ?> text-center fw-bold"><?= $refundText ?></div>

<?php endif; ?>

<!-- المنتجات -->
<div class="card-beauty">
<h5 class="mb-3">المنتجات</h5>

<?php foreach($items as $item): ?>

<?php

// التحقق هل يوجد طلب إرجاع لهذا المنتج
$checkRefund = $pdo->prepare("SELECT status FROM refund_requests WHERE order_id = ? AND product_id = ? LIMIT 1");
$checkRefund->execute([$order['id'],$item['product_id']]);
$refundExists = $checkRefund->fetch(PDO::FETCH_ASSOC);
?>

<!-- كرت المنتج -->
<div class="product-card">

<!-- صورة المنتج -->
<?php if(
    !empty($item['image'])
    &&
    file_exists("assets/images/".$item['image'])
): ?>
<img src="assets/images/<?= $item['image'] ?>">
<?php endif; ?>

<!-- بيانات المنتج -->
<div class="flex-grow-1 text-end">
<strong>
<?= $item['name'] ?>
</strong>

<br>

<span class="text-muted small">
الكمية:
<?= $item['quantity'] ?>
</span>

<br>

<span class="fw-bold">
<?= $item['price'] ?>
ج.م
</span>

</div>

<?php if(!$refundExists): ?>

<a href="refund.php?order_id=<?= $order['id'] ?>&product_id=<?= $item['product_id'] ?>"
   class="btn-track text-center d-block mt-2 w-100">
   إرجاع المنتج
</a>

<?php else: ?>

<!-- تم إرسال طلب إرجاع -->
<div class="alert alert-warning text-center mt-2 mb-0">
تم إرسال طلب إرجاع لهذا المنتج
</div>

<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif;?>
</div>
</body>

<?php include "includes/footer.php"; ?>

</html>