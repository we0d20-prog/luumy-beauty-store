<!-- الصفحة مسؤولة عن إدارة الطلبات داخل لوحة تحكم الأدمن admin-orders.php
 عرض جميع الطلبات
عرض طلبات اليوم فقط
البحث باسم العميل أو رقم الهاتف
عرض حالة الطلب
تحديث حالة الطلب
عرض إشعار عند وصول طلب جديد
الانتقال لتفاصيل الطلب -->
<?php

require "auth.php";
require "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// حساب عدد طلبات اليوم
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
$countStmt->execute();
$todayOrdersCount = $countStmt->fetchColumn();

// جلب نوع العرض والبحث
$type = $_GET['type'] ?? 'today';
$search = $_GET['search'] ?? '';

// إنشاء استعلام الطلبات
$sql = "SELECT * FROM orders WHERE 1 ";
// مصفوفة القيم
$params = [];

// فلترة طلبات اليوم
if ($type === 'today') {
    $sql .= "AND DATE(created_at) = CURDATE() ";
}

// البحث بالاسم أو الهاتف
if (!empty($search)) {
    $sql .= "AND (customer_name LIKE ? OR phone LIKE ?) ";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// ترتيب من الأحدث للأقدم
$sql .= "ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// إشعار الطلبات الجديدة
// جلب آخر رقم طلب
$lastOrderStmt = $pdo->query("SELECT MAX(id) FROM orders");
// تخزين آخر id
$lastOrderId = $lastOrderStmt->fetchColumn();
// آخر طلب محفوظ بالجلسة
$sessionLast = $_SESSION['last_order_id'] ?? 0;
// متغير الإشعار
$newOrderAlert = false;
// إذا يوجد طلب جديد
if ($lastOrderId > $sessionLast) {
    // تفعيل الإشعار
    $newOrderAlert = true;
    // تحديث آخر طلب
    $_SESSION['last_order_id'] = $lastOrderId;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إدارة الطلبات</title>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">

</head>

<body class="bg-light">

<div class="container mt-4">
<div class="card mb-4 p-3 shadow-sm">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

    <a href="dashboard.php"
       class="btn btn-outline-secondary">
      ⬅️ لوحة التحكم
    </a>
    
    <!-- عنوان الصفحة -->
    <h4 class="mb-0 text-center flex-grow-1">
       إدارة الطلبات

      <!-- عدد طلبات اليوم -->
      <span class="badge bg-danger">
        <?= $todayOrdersCount ?>
      </span>

    </h4>
    
    <div class="btn-group">

      <!-- طلبات اليوم -->
      <a href="orders.php?type=today"
         class="btn <?= ($type=='today')?'btn-dark':'btn-outline-dark' ?>">
         طلبات اليوم
      </a>

      <!-- كل الطلبات -->
      <a href="orders.php?type=all"
         class="btn <?= ($type=='all')?'btn-dark':'btn-outline-dark' ?>">
         كل الطلبات
      </a>

    </div>

</div>
</div>

<!-- البحث -->
<form method="get"
      class="row g-2 mb-3">

    <!-- حفظ نوع الفلترة -->
    <input type="hidden"
           name="type"
           value="<?= $type ?>">

    <!-- حقل البحث -->
    <div class="col-12 col-md-9">
        <input type="text"
               name="search"
               class="form-control"
               placeholder="🔍 اسم العميل أو الهاتف"
               value="<?= htmlspecialchars($search) ?>">
    </div>

    <!-- زر البحث -->
    <div class="col-12 col-md-3">
        <button class="btn btn-primary w-100">
            بحث
        </button>
    </div>

</form>

<!-- إشعار طلب جديد -->
<?php if ($newOrderAlert): ?>
<div class="alert alert-success">
  🔔 تم استقبال طلب جديد!
</div>

<?php endif; ?>

<!-- عرض الطلبات -->
<!-- إذا لا توجد طلبات -->
<?php if (empty($orders)): ?>
    <div class="alert alert-info">
        لا توجد طلبات حالياً
    </div>
<?php else: ?>


<!-- لوب عرض الطلبات -->
<?php foreach ($orders as $o): ?>
<div class="card order-card">
<div class="card-body">

    <!-- رأس الطلب -->
    <div class="order-header">
        <!-- رقم الطلب -->
        <strong>#<?= $o['id'] ?></strong>
        <!-- إجمالي الطلب -->
        <span class="badge bg-success"><?= number_format($o['total'],2) ?> ج.م</span>
    </div>

    <hr>

    <!-- بيانات العميل -->
    <div class="order-meta"> 👤 <?= htmlspecialchars($o['customer_name']) ?></div>
    <div class="order-meta">📞 <?= htmlspecialchars($o['phone']) ?> </div>
    <div class="order-meta">🚚 الشحن:<?= htmlspecialchars($o['shipping_cost']) ?> ج.م</div>
    <div class="order-meta"> 💳 الدفع:<?= $o['payment_method'] == 'cod' ? 'عند الاستلام' : 'بطاقة' ?></div>
    <div class="order-meta">🕒 <?= date("d/m/Y - h:i A", strtotime($o['created_at'])) ?></div>

    <!-- حالة الطلب -->
    <form method="post"action="update_order_status.php"class="mt-2">
    <!-- رقم الطلب -->
    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
              
        <!-- قائمة الحالات -->
        <select name="status"class="form-select form-select-sm mt-2" onchange="this.form.submit()">
            <option value="new"
                <?= $o['status']=='new'?'selected':'' ?>>
                🆕 جديد
            </option>

            <option value="processing"
                <?= $o['status']=='processing'?'selected':'' ?>>
                ⚙ قيد التنفيذ
            </option>

            <option value="shipped"
                <?= $o['status']=='shipped'?'selected':'' ?>>
                🚚 تم الشحن
            </option>

            <option value="completed"
                <?= $o['status']=='completed'?'selected':'' ?>>
                ✅ مكتمل
            </option>

        </select>

    </form>

    <!-- زر تفاصيل الطلب -->
    <a href="order_details.php?id=<?= $o['id'] ?>"
       class="btn btn-primary w-100 mt-3">
       عرض تفاصيل الطلب
    </a>

</div>
</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</body>
</html>