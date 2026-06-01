<!-- الصفحة مسؤولة عن التقارير والإحصائيات admin-reports.php
 عرض الطلبات ضمن فترة زمنية محددة
فلترة الطلبات بالتاريخ (من تاريخ → إلى تاريخ)
حساب إجمالي المبيعات
حساب إجمالي تكاليف الشحن
حساب عدد الطلبات
عرض رسم بياني للمبيعات باستخدام Chart.js
عرض جدول كامل للطلبات خلال الفترة المحددة -->
<?php

session_start();
require "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// تحديد فترة التقرير
// تاريخ البداية
$from = $_GET['from'] ?? date('Y-m-01');
// تاريخ النهاية
$to   = $_GET['to'] ?? date('Y-m-d');

// استعلام الطلبات حسب التاريخ
$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE DATE(created_at) BETWEEN ? AND ?
    ORDER BY created_at DESC
");
$stmt->execute([$from, $to]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// حساب الإحصائيات
// عدد الطلبات
$totalOrders = count($orders);
// إجمالي المبيعات
$totalSales = 0;
// إجالي الشحن
$totalShipping = 0;

// لوب لحساب الإجماليات
foreach ($orders as $o) {
    // جمع قيمة الطلبات
    $totalSales += $o['total'];
    // جمع تكاليف الشحن
    $totalShipping += $o['shipping_cost'];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>التقارير</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="bg-light">

<div class="container my-4">

<!-- الهيدر -->
<div class="card mb-4 p-3 shadow-sm">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

    <h3 class="mb-0"> 📊 التقارير</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary"> ⬅️ لوحة التحكم</a>

</div>
</div>

<!-- فلترة التاريخ -->
<form method="get" class="row g-3 mb-4">

    <div class="col-md-4">
        <label>من تاريخ</label>
        <input type="date"
               name="from"
               value="<?= $from ?>"
               class="form-control">
    </div>

    <div class="col-md-4">
        <label>إلى تاريخ</label>
        <input type="date"
               name="to"
               value="<?= $to ?>"
               class="form-control">
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary w-100">
            عرض التقرير
        </button>
    </div>

</form>

<!-- بطاقات الإحصائيات -->
<div class="row mb-4">

    <!-- عدد الطلبات -->
    <div class="col-md-4">
        <div class="card report-card text-center p-3">
            <h6>عدد الطلبات</h6>
            <h4><?= $totalOrders ?></h4>
        </div>
    </div>

    <!-- إجمالي المبيعات -->
    <div class="col-md-4">
        <div class="card report-card text-center p-3">
            <h6>إجمالي المبيعات</h6>
            <h4><?= number_format($totalSales,2) ?> ج.م</h4>
        </div>
    </div>

    <!-- إجمالي الشحن -->
    <div class="col-md-4">
        <div class="card report-card text-center p-3">
            <h6>إجمالي الشحن</h6>
            <h4><?= number_format($totalShipping,2) ?> ج.م</h4>
        </div>
    </div>

</div>

<!-- الرسم البياني -->
<div class="card mb-4">
<div class="card-header bg-warning">
📈 رسم المبيعات
</div>
<div class="card-body">
<canvas id="salesChart" height="120"></canvas>
</div>
</div>

<!-- جدول الطلبات -->
<div class="card">
<div class="card-header bg-primary text-white">
الطلبات خلال الفترة
</div>

<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-bordered mb-0 text-center align-middle">
<thead class="table-light">

<tr>

<th>#</th>
<th>العميل</th>
<th>الهاتف</th>
<th>الشحن</th>
<th>الإجمالي</th>
<th>التاريخ</th>

</tr>

</thead>

<tbody>

<!-- إذا لا توجد طلبات -->
<?php if (empty($orders)): ?>

<tr>

<td colspan="6" class="text-muted">
لا توجد طلبات
</td>

</tr>
<?php else: ?>

<!-- عرض الطلبات -->
<?php foreach ($orders as $o): ?>

<tr>

<td data-label="#">
<?= $o['id'] ?>
</td>

<td data-label="العميل">
<?= htmlspecialchars($o['customer_name']) ?>
</td>

<td data-label="الهاتف">
<?= htmlspecialchars($o['phone']) ?>
</td>

<td data-label="الشحن">
<?= number_format($o['shipping_cost'],2) ?> ج.م
</td>

<td data-label="الإجمالي">
<?= number_format($o['total'],2) ?> ج.م
</td>

<td data-label="التاريخ">
<?= date("d/m/Y", strtotime($o['created_at'])) ?>
</td>

</tr>

<?php endforeach; ?>
<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<script>

 const ctx = document.getElementById('salesChart');

 new Chart(ctx, {

    type: 'bar',
    data: {

        labels: <?= json_encode(array_column($orders, 'created_at')) ?>,
        datasets: [{

            label: 'إجمالي الطلب',

            data: <?= json_encode(array_column($orders, 'total')) ?>,

        }]
    },

    options: {

        responsive: true,

        plugins: {

            legend: { display: false }

        }
    }
 });

</script>

</body>
</html>