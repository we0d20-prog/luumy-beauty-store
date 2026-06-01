<!-- الصفحة مسؤولة عن إدارة طلبات الإرجاع refunde.php
 عرض جميع طلبات الإرجاع
ربط الطلبات بالطلبات الأصلية عبر رقم الطلب
عرض سبب الإرجاع وصورة الإثبات والحساب البنكي
عرض حالة الطلب (قيد المراجعة / مقبول / مرفوض / تم التحويل)
السماح للأدمن بتغيير حالة الطلب
عرض إحصائيات سريعة لكل حالة -->
<?php

include "../includes/db.php";
require "auth.php";

// جلب كل طلبات الإرجاع مع رقم هاتف العميل
$stmt = $pdo->query("
SELECT rr.*, o.phone 
FROM refund_requests rr
JOIN orders o ON rr.order_id = o.id
ORDER BY rr.created_at DESC
");
$refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// قيد المراجعة
$pending = 0;
// تمت الموافقة
$approved = 0;
// مرفوض
$rejected = 0;
// تم التحويل
$refunded = 0;

// لوب لحساب الحالات
foreach($refunds as $r){
    if($r['status'] == 'pending') $pending++;
    if($r['status'] == 'approved') $approved++;
    if($r['status'] == 'rejected') $rejected++;
    if($r['status'] == 'refunded') $refunded++;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>لوحة طلبات الإرجاع</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
<div class="container mt-4">

<div class="card mb-4 p-3 shadow-sm">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

    <h4 class="fw-bold m-0"> طلبات الإرجاع</h4>
    <a href="dashboard.php" class="btn btn-outline-secondary">⬅️ لوحة التحكم </a>

</div>
</div>

<!-- الإحصائيات -->
<div class="row mb-4">

<!-- قيد المراجعة -->
<div class="col-md-3 mb-3">
<div class="stat-card bg-warning text-dark text-center">
<h5>قيد المراجعة</h5>
<h3><?= $pending ?></h3>
</div>
</div>

<!-- تمت الموافقة -->
<div class="col-md-3 mb-3">
<div class="stat-card bg-success text-center">
<h5>تمت الموافقة</h5>
<h3><?= $approved ?></h3>
</div>
</div>

<!-- مرفوض -->
<div class="col-md-3 mb-3">
<div class="stat-card bg-danger text-center">
<h5>مرفوض</h5>
<h3><?= $rejected ?></h3>
</div>
</div>

<!-- تم التحويل -->
<div class="col-md-3 mb-3">
<div class="stat-card bg-dark text-center">
<h5>تم التحويل</h5>
<h3><?= $refunded ?></h3>
</div>
</div>

</div>

<!-- جدول طلبات الإرجاع -->
<div class="card card-box p-3">
<div class="table-responsive">
<table class="table table-hover text-center align-middle">

<thead>
<tr>

<th># الطلب</th>
<th>الهاتف</th>
<th>السبب</th>
<th>الحساب البنكي</th>
<th>الصورة</th>
<th>الحالة</th>
<th width="150">إجراء</th>

</tr>
</thead>

<tbody>

<!-- عرض الطلبات -->
<?php foreach($refunds as $r): ?>

<tr>

<td data-label="رقم الطلب">
<strong>#<?= $r['order_id'] ?></strong>
</td>

<td data-label="الهاتف">
<?= $r['phone'] ?>
</td>

<td data-label="السبب" style="max-width:200px">
<?= $r['reason'] ?>
</td>

<td data-label="الحساب البنكي">
<?= $r['bank_account'] ?>
</td>

<td data-label="الصورة">
<?php if($r['image']): ?>
<a href="../assets/refunds/<?= $r['image'] ?>" target="_blank">
<img src="../assets/refunds/<?= $r['image'] ?>" class="refund-img">
</a>

<?php else: ?>
—
<?php endif; ?>

</td>

<!-- حالة الطلب -->
<td>

<?php
// تحديد لون الحالة
$statusClass = '';

if($r['status'] == 'pending') $statusClass = 'status-pending';
if($r['status'] == 'approved') $statusClass = 'status-approved';
if($r['status'] == 'rejected') $statusClass = 'status-rejected';
if($r['status'] == 'refunded') $statusClass = 'status-refunded';
?>

<td data-label="الحالة">
   <span class="badge <?= $statusClass ?>">
   <?= $r['status'] ?>
   </span>
</td>

<!-- أزرار التحكم -->
<td data-label="إجراء">

<!-- موافقة -->
<a href="update_refund.php?id=<?= $r['id'] ?>&status=approved"
   class="btn btn-success btn-sm action-btn">
موافقة
</a>

<!-- رفض -->
<a href="update_refund.php?id=<?= $r['id'] ?>&status=rejected"
   class="btn btn-danger btn-sm action-btn">
رفض
</a>

<!-- تم التحويل -->
<a href="update_refund.php?id=<?= $r['id'] ?>&status=refunded"
   class="btn btn-dark btn-sm action-btn">
تم التحويل
</a>

</td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</body>
</html>