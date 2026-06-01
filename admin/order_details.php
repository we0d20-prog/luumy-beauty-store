<!-- الصفحة المسؤولة عن عرض تفاصيل الطلب داخل لوحة تحكم الأدمن admin-order_details.php
 جلب بيانات الطلب
عرض معلومات العميل
عرض المنتجات الموجودة داخل الطلب
حساب المجموع والشحن والإجمالي النهائي
عرض صور المنتجات
إعطاء زر لتحميل الفاتورة-->
<?php
require "auth.php";
require "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
// جلب id من الرابط
$order_id = $_GET['id'] ?? 0;
// جلب بيانات الطلب
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "الطلب غير موجود";
    exit;
}
// جلب منتجات الطلب
$stmt_items = $pdo->prepare("
SELECT 
    oi.quantity,
    oi.price,
    p.name,
    (
        SELECT image
        FROM product_images
        WHERE product_id = p.id
        ORDER BY id ASC
        LIMIT 1
    ) AS image
FROM order_items oi
JOIN products p ON oi.product_id = p.id
WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();
// متغير المجموع
$subtotal = 0;
// لوب على المنتجات
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
// تكلفة الشحن
$shipping = $order['shipping_cost'] ?? 0;
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>

<meta charset="UTF-8">
<title>تفاصيل الطلب #<?= $order_id ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<div class="container py-4">

<h4 class="mb-4">تفاصيل الطلب #<?= $order_id ?></h4>

<!-- الأزرار -->
<div class="d-flex justify-content-between mb-3">

<a href="orders.php" class="btn btn-outline-secondary">
⬅️ رجوع
</a>

<a href="invoice.php?id=<?= $order_id ?>" 
target="_blank"
class="btn btn-success">
🧾 تحميل الفاتورة
</a>

</div>

<!-- معلومات الطلب -->
<div class="card order-card mb-4">
<div class="card-body">

<div class="row">

<div class="col-md-6 mb-2 order-info">
<strong>👤 العميل:</strong><br>
<?= htmlspecialchars($order['customer_name']) ?>
</div>

<div class="col-md-6 mb-2 order-info ">
<strong>📞 الهاتف:</strong><br>
<?= htmlspecialchars($order['phone']) ?>
</div>

<div class="col-md-6 mb-2 order-info">
<strong>🚚 الشحن:</strong><br>
<?= number_format($shipping,2) ?> ج.م
</div>

<div class="col-md-6 mb-2 order-info">
<strong>🕒 التاريخ:</strong><br>
<?= htmlspecialchars($order['created_at']) ?>
</div>

<div class="col-12 mt-2 order-info">
<strong>📍 العنوان:</strong><br>
<?= nl2br(htmlspecialchars($order['address'])) ?>
</div>

</div>

</div>
</div>

<!-- المنتجات -->
<div class="card order-card">
<div class="card-body">

<h5 class="mb-3">المنتجات</h5>

<div class="table-responsive">
<table class="table align-middle">

<thead>
<tr>
<th>الصورة</th>
<th>المنتج</th>
<th>السعر</th>
<th>الكمية</th>
<th>الإجمالي</th>
</tr>
</thead>

<tbody>
<!-- لوب عرض المنتجات -->
<?php foreach($items as $item): ?>

<tr>

<td>
<img src="../assets/images/<?= $item['image'] ?: 'no.png' ?>" class="product-img">
</td>

<td>
<?= htmlspecialchars($item['name']) ?>
</td>

<td>
<?= number_format($item['price'],2) ?> ج.م
</td>

<td>
<?= $item['quantity'] ?>
</td>

<td>
<?= number_format($item['price'] * $item['quantity'],2) ?> ج.م
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>
</div>
</div>
</div>

<!-- الإجمالي -->
<div class="card order-card mt-4">
<div class="card-body">

<div class="d-flex justify-content-between">
<span>المجموع</span>
<span><?= number_format($subtotal,2) ?> ج.م</span>
</div>

<div class="d-flex justify-content-between">
<span>الشحن</span>
<span><?= number_format($shipping,2) ?> ج.م</span>
</div>

<hr>

<div class="d-flex justify-content-between fw-bold fs-5">
<span>الإجمالي النهائي</span>
<span><?= number_format($total,2) ?> ج.م</span>
</div>

</div>
</div>

</div>

</body>
</html>