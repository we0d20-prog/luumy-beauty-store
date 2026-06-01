<!-- لوحة تحكم الأدمن dashboard.php
 عرض إحصائيات المتجر
إعطاء وصول سريع لإدارة الموقع
التحكم بالأقسام والمنتجات والطلبات
عرض ملخص سريع عن حالة المتجر -->
<?php
require "auth.php";
include "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// عدد الأقسام
$catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
// عدد المنتجات
$productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
// عدد الطلبات
$orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
// طلبات اليوم
$todayOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>لوحة التحكم</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<div class="container my-5 px-2 px-md-0">

  <!-- العنوان -->
  <div class="text-center mb-5">
    <h2>مرحبًا <?= htmlspecialchars($_SESSION['admin_username']) ?></h2>
    <p class="text-muted">لوحة التحكم الرئيسية</p>
  </div>

  <!-- الإحصائيات -->
  <div class="row g-3 mb-4">

  <div class="col-6 col-md-3">
    <div class="stat-card">
      <i class="bi bi-grid text-primary"></i>
      <h4 class="counter" data-target="<?= $catCount ?>">0</h4>
      <small class="text-muted">الأقسام</small>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="stat-card">
      <i class="bi bi-box text-success"></i>
      <h4 class="counter" data-target="<?= $productCount  ?>">0</h4>
      <small class="text-muted">المنتجات</small>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="stat-card">
      <i class="bi bi-bag text-dark"></i>
      <h4 class="counter" data-target="<?= $orderCount  ?>">0</h4>
      <small class="text-muted">كل الطلبات</small>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="stat-card">
      <i class="bi bi-calendar-check text-warning"></i>
      <h4 class="counter" data-target="<?= $todayOrders  ?>">0</h4>
      <small class="text-muted">طلبات اليوم</small>
    </div>
  </div>

</div>

  <!-- الصف الأول -->
  <div class="row g-4">

    <!-- الأقسام -->
    <div class="col-12 col-sm-6 col-md-4">
      <a href="categories.php" class="text-decoration-none text-dark">
        <div class="dashboard-card">
          <i class="bi bi-grid-fill text-primary"></i>
          <h5>إدارة الأقسام</h5>
          <p class="text-muted small">إضافة / تعديل / حذف الأقسام</p>
        </div>
      </a>
    </div>

    <!-- المنتجات -->
    <div class="col-12 col-sm-6 col-md-4">
      <a href="products.php" class="text-decoration-none text-dark">
        <div class="dashboard-card">
          <i class="bi bi-box-seam text-success"></i>
          <h5>إدارة المنتجات</h5>
          <p class="text-muted small">إضافة المنتجات والتحكم بها</p>
        </div>
      </a>
    </div>

    <!-- الطلبات -->
    <div class="col-12 col-sm-6 col-md-4">
      <a href="orders.php" class="text-decoration-none text-dark">
        <div class="dashboard-card">
          <i class="bi bi-bag-check-fill text-dark"></i>
          <h5>إدارة الطلبات</h5>
          <p class="text-muted small">عرض الطلبات والفواتير</p>
        </div>
      </a>
    </div>

  </div>

  <!-- الصف الثاني -->
  <div class="row g-4 mt-2">

    <!-- طلبات الإرجاع -->
    <div class="col-12 col-sm-6 col-md-4">
      <a href="refunds.php" class="text-decoration-none text-dark">
        <div class="dashboard-card">
          <i class="bi bi-arrow-counterclockwise text-danger"></i>
          <h5>طلبات الإرجاع</h5>
          <p class="text-muted small">مراجعة طلبات الاسترجاع</p>
        </div>
      </a>
    </div>

    <!-- التقارير -->
    <div class="col-12 col-sm-6 col-md-4">
      <a href="reports.php" class="text-decoration-none text-dark">
        <div class="dashboard-card">
          <i class="bi bi-bar-chart-fill text-warning"></i>
          <h5>التقارير</h5>
          <p class="text-muted small">إحصائيات المبيعات</p>
        </div>
      </a>
    </div>

    <!-- تسجيل الخروج -->
    <div class="col-12 col-sm-6 col-md-4">
      <a href="logout.php" class="text-decoration-none">
        <div class="dashboard-card logout">
          <i class="bi bi-box-arrow-right"></i>
          <h5>تسجيل خروج</h5>
          <p class="small">الخروج من لوحة التحكم</p>
        </div>
      </a>
    </div>

  </div>

</div>

<script>
 document.addEventListener("DOMContentLoaded", function () {

  const counters = document.querySelectorAll('.counter');

  counters.forEach(counter => {
    const target = parseInt(counter.getAttribute('data-target'));
    let count = 0;

    const updateCount = () => {
      const increment = Math.ceil(target / 30);

      if (count < target) {
        count += increment;
        counter.innerText = count > target ? target : count;
        setTimeout(updateCount, 30);
      } else {
        counter.innerText = target;
      }
    };

    updateCount();
  });

 });
</script>

</body>
</html>