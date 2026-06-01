<!-- هذه الصفحة هي صفحة إدارة الأقسام داخل لوحة تحكم الأدمن في متجر التجميل admin-categories.php
وظيفتها الأساسية
إضافة أقسام جديدة
تعديل الأقسام
حذف الأقسام
رفع صور للأقسام
عرض جميع الأقسام داخل جدول -->
<?php
require "auth.php";
include "../includes/db.php";
// إذا لم يكن الأدمن مسجل دخول يتم تحويله لصفحة تسجيل الدخول
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
// مسار رفع الصور داخل مجلد images
$uploadDir = __DIR__ . "/../assets/images/";

// إضافة قسم جديد
if (isset($_POST['add_category'])) {
    // جلب اسم القسم من الفورم
    $name = $_POST['name'];
    // متغير لتخزين اسم الصورة
    $imageName = null;
    // التحقق إذا تم اختيار صورة
    if (!empty($_FILES['image']['name'])) {
        // اسم الصورة الأصلي
        $fileName = $_FILES['image']['name'];
        // المسار المؤقت للصورة
        $tmpName  = $_FILES['image']['tmp_name'];
        // استخراج امتداد الصورة
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        // الامتدادات المسموحة
        $allowed = ['jpg','jpeg','png','webp'];
        // التحقق من الامتداد
        if (in_array($ext, $allowed)) {
            // إنشاء اسم عشوائي جديد للصورة
            $newImageName = time() . "_" . rand(1000,9999) . "." . $ext;
            // نقل الصورة للمجلد
            move_uploaded_file($tmpName, $uploadDir . $newImageName);
            // تخزين الاسم الجديد
            $imageName = $newImageName;
        }
    }
    // إدخال القسم في قاعدة البيانات
    $stmt = $pdo->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
    $stmt->execute([$name, $imageName]);
}

// حذف قسم
if (isset($_GET['delete'])) {
    // جلب رقم القسم
    $id = $_GET['delete'];
    // جلب اسم الصورة من قاعدة البيانات
    $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);
    // إذا توجد صورة يتم حذفها من المجلد
    if ($cat && !empty($cat['image']) && file_exists($uploadDir . $cat['image'])) {
        // حذف الصورة
        unlink($uploadDir . $cat['image']);
    }
    // حذف القسم من قاعدة البيانات
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: categories.php");
    exit;
}

// تعديل قسم
if (isset($_POST['update_category'])) {
    // رقم القسم
    $id = $_POST['id'];
    // الاسم الجديد
    $name = $_POST['name'];
    // جلب الصورة القديمة
    $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $oldCat = $stmt->fetch(PDO::FETCH_ASSOC);
    // تخزين الصورة القديمة مؤقتاً
    $imageName = $oldCat['image'];
    // إذا تم رفع صورة جديدة
    if (!empty($_FILES['image']['name'])) {
        // اسم الصورة
        $fileName = $_FILES['image']['name'];
        // المسار المؤقت
        $tmpName  = $_FILES['image']['tmp_name'];
        // استخراج الامتداد
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        // الامتدادات المسموحة
        $allowed = ['jpg','jpeg','png','webp'];
        // التحقق من الامتداد
        if (in_array($ext, $allowed)) {
            // إنشاء اسم جديد للصورة
            $newImageName = time() . "_" . rand(1000,9999) . "." . $ext;
            // رفع الصورة للمجلد
            move_uploaded_file($tmpName, $uploadDir . $newImageName);
            // حذف الصورة القديمة إذا موجودة
            if (!empty($oldCat['image']) && file_exists($uploadDir . $oldCat['image'])) {
                unlink($uploadDir . $oldCat['image']);
            }
            // تحديث اسم الصورة
            $imageName = $newImageName;
        }
    }
    // تحديث بيانات القسم
    $stmt = $pdo->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
    $stmt->execute([$name, $imageName, $id]);
    header("Location: categories.php");
    exit;
}

// جلب جميع الأقسام
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">

<title>إدارة الأقسام</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="container mt-4">

<!-- الهيدر -->
<div class="card mb-4 p-3 shadow-sm">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

    <h3 class="mb-0">
        إدارة الأقسام
    </h3>

    <a href="dashboard.php" class="btn btn-outline-secondary">
      ⬅️ لوحة التحكم
    </a>

  </div>
</div>

<!-- فورم إضافة قسم -->
<form method="POST" enctype="multipart/form-data" class="row g-2 mb-4">

    <div class="col-12 col-md-4">
        <input type="text"
               name="name"
               class="form-control"
               placeholder="اسم القسم"
               required>
    </div>

    <!-- رفع الصورة -->
    <div class="col-12 col-md-4">
        <input type="file"
               name="image"
               class="form-control"
               required>
    </div>

    <!-- زر الإضافة -->
    <div class="col-12 col-md-4">
        <button name="add_category"
                class="btn btn-success w-100">
            إضافة قسم
        </button>
    </div>

</form>

<!-- جدول عرض الأقسام -->
<div class="table-responsive">

<table class="table table-bordered text-center align-middle">

<tr>
    <th>#</th>
    <th>الصورة</th>
    <th>الاسم</th>
    <th>تعديل</th>
    <th>حذف</th>
</tr>

<!-- لوب عرض الأقسام -->
<?php foreach ($categories as $cat): ?>

<tr>

    <!-- رقم القسم -->
    <td><?= $cat['id'] ?></td>

    <!-- صورة القسم -->
    <td>
    <?php if (!empty($cat['image'])): ?>
        <img src="../assets/images/<?= $cat['image'] ?>"
             loading="lazy">
    <?php else: ?>
        لا يوجد
    <?php endif; ?>
    </td>

    <!-- اسم القسم -->
    <td>
        <?= htmlspecialchars($cat['name']) ?>
    </td>

    <!-- زر التعديل -->
    <td>
        <button class="btn btn-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#editModal<?= $cat['id'] ?>">
            تعديل
        </button>
    </td>

    <!-- زر الحذف -->
    <td>
        <a href="categories.php?delete=<?= $cat['id'] ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('هل أنت متأكد من الحذف؟')">
           حذف
        </a>
    </td>

</tr>

<!-- مودال تعديل القسم -->
<div class="modal fade"
     id="editModal<?= $cat['id'] ?>"
     tabindex="-1">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST" enctype="multipart/form-data">

<div class="modal-header">
    <h5 class="modal-title">
        تعديل القسم
    </h5>
</div>

<div class="modal-body">

    <!-- رقم القسم مخفي -->
    <input type="hidden"
           name="id"
           value="<?= $cat['id'] ?>">

    <!-- اسم القسم -->
    <div class="mb-3">
        <label>اسم القسم</label>
        <input type="text"
               name="name"
               value="<?= $cat['name'] ?>"
               class="form-control"
               required>
    </div>

    <!-- تغيير الصورة -->
    <div class="mb-3">
        <label>تغيير الصورة (اختياري)</label>
        <input type="file"
               name="image"
               class="form-control">
    </div>

</div>

<!-- أزرار المودال -->
<div class="modal-footer">
    <button type="submit"
            name="update_category"
            class="btn btn-success">
        حفظ التعديلات
    </button>
</div>

</form>

</div>
</div>
</div>

<?php endforeach; ?>

</table>
</div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>