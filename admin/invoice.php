<!-- الصفحة مسؤولة عن عرض فاتورة الطلب invoice.php
 جلب بيانات الطلب من قاعدة البيانات
جلب المنتجات الموجودة داخل الطلب
حساب المجموع والشحن والإجمالي
عرض فاتورة كاملة للعميل
إمكانية طباعة الفاتورة أو حفظها PDF -->
<?php

require "auth.php";
require "../includes/db.php";

// التحقق من رقم الطلب
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("رقم الطلب غير صحيح");
}

// تحويل رقم الطلب إلى Integer للحماية
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب بيانات الطلب
$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id=?");
$orderStmt->execute([$order_id]);
$order = $orderStmt->fetch(PDO::FETCH_ASSOC);

// إذا الطلب غير موجود
if (!$order) {
    die("الطلب غير موجود");
}

// جلب المنتجات الخاصة بالطلب
$itemsStmt = $pdo->prepare("
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$order_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// متغير المجموع
$subtotal = 0;

// لوب على المنتجات
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// المجموع + تكلفة الشحن
$total = $subtotal + $order['shipping_cost'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title> فاتورة طلب #<?= $order_id ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">

</head>
<body>

<div class="invoice-box mt-4">
<div class="invoice-actions no-print">

    <a href="order_details.php?id=<?= $order_id ?>"
       class="btn btn-outline-secondary">
        ⬅️ رجوع لتفاصيل الطلب
    </a>

    <button onclick="window.print()"
            class="btn btn-primary">
        🖨️ طباعة / حفظ PDF
    </button>

</div>
<hr>

<!-- عنوان الفاتورة -->
<h4 class="text-center mb-4">
    🧾 فاتورة طلب #<?= $order_id ?>
</h4>

<!-- بيانات العميل -->
<p><strong>العميل:</strong><?= htmlspecialchars($order['customer_name']) ?>
<p><strong>الهاتف:</strong><?= $order['phone'] ?></p>
<p><strong>العنوان:</strong><?= $order['address'] ?></p>
<p><strong>التاريخ:</strong><?= $order['created_at'] ?></p>

<hr>

<!-- جدول المنتجات -->
<div class="table-responsive">
<table class="table table-bordered text-center">
<thead class="table-light">

<tr>
  <th>المنتج</th>
  <th>السعر</th>
  <th>الكمية</th>
  <th>الإجمالي</th>
</tr>

</thead>
<tbody>
<!-- لوب عرض المنتجات -->
<?php foreach ($items as $item): ?>

<tr>
  <td><?= $item['name'] ?> </td>
  <td><?= number_format($item['price'],2) ?> ج.م </td>
  <td><?= $item['quantity'] ?> </td>
  <td><?= number_format($item['price'] * $item['quantity'],2) ?> ج.م </td>
</tr>

<?php endforeach; ?>

</tbody>
</table>
</div>

<hr>

<!-- ملخص الفاتورة -->
<div class="text-end">

<p><strong>المجموع:</strong><?= number_format($subtotal,2) ?> ج.م</p>
<p><strong>الشحن:</strong><?= number_format($order['shipping_cost'],2) ?> ج.م</p>
<h5><strong>الإجمالي:</strong><?= number_format($total,2) ?> ج.م</h5>

</div>

</div>
</body>
</html>