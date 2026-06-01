<!-- الصفحة مسؤولة عن إدارة المنتجات داخل لوحة تحكم الأدمن admin-products.php
 عرض جميع المنتجات
إضافة منتج جديد مع صور متعددة
تعديل بيانات المنتج
حذف المنتج
البحث عن منتج
عرض كمية المخزون والتنبيه عند قلة المخزون
ربط المنتجات بالأقسام
إدارة صور المنتجات -->
<?php

require "auth.php";
require "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// جلب جميع الأقسام
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب كلمة البحث من الرابط
$search = $_GET['search'] ?? '';

// استعلام المنتجات
$sql = "
SELECT products.*, categories.name AS category_name
FROM products
JOIN categories ON products.category_id = categories.id
";

// مصفوفة القيم
$params = [];

// إذا يوجد بحث
if (!empty($search)) {
    $sql .= " WHERE products.name LIKE ? ";
    $params[] = "%$search%";
}

// ترتيب المنتجات من الأحدث
$sql .= " ORDER BY products.id DESC ";

// تنفيذ الاستعلام
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// حساب المنتجات قليلة المخزون
$lowStockStmt = $pdo->query("
SELECT COUNT(*) FROM products
WHERE stock <= 5
");
// عدد المنتجات القليلة
$lowStockCount = $lowStockStmt->fetchColumn();
// جلب منتج للتعديل
$editProduct = null;

// إذا يوجد id للتعديل
if (isset($_GET['edit'])) {
    // جلب بيانات المنتج
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
}

// تحديث منتج
if (isset($_POST['update_product'])) {
    // بيانات المنتج
    $id          = $_POST['id'];
    $name        = $_POST['name'];
    $price       = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $stock       = $_POST['stock'];

    // تحديث في قاعدة البيانات
    $stmt = $pdo->prepare("
        UPDATE products 
        SET name=?, price=?, description=?, category_id=?, stock=?
        WHERE id=?
    ");
    $stmt->execute([
        $name,
        $price,
        $description,
        $category_id,
        $stock,
        $id
    ]);

    header("Location: products.php");
    exit;
}

// إضافة منتج جديد
if (isset($_POST['add_product'])) {
    // بيانات المنتج
    $name        = $_POST['name'];
    $price       = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $stock       = $_POST['stock'];

    // إدخال المنتج
    $stmt = $pdo->prepare("
        INSERT INTO products (name, price, description, stock, category_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $name,
        $price,
        $description,
        $stock,
        $category_id
    ]);
    // الحصول على id المنتج
    $product_id = $pdo->lastInsertId();

    // رفع صور المنتج
    if (!empty($_FILES['images']['name'][0])) {

        foreach ($_FILES['images']['name'] as $key => $image_name) {
            // اسم جديد للصورة
            $new_name = time() . "_" . rand(1000,9999) . "_" . $image_name;
            // رفع الصورة
            move_uploaded_file(
                $_FILES['images']['tmp_name'][$key],
                "../assets/images/" . $new_name
            );
            // حفظ الصورة في قاعدة البيانات
            $stmt = $pdo->prepare("
                INSERT INTO product_images (product_id, image)
                VALUES (?, ?)
            ");
            $stmt->execute([$product_id, $new_name]);
        }
    }

    header("Location: products.php");
    exit;
}

// حذف منتج
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // حذف صور المنتج من الجدول
    $stmt = $pdo->prepare("
        DELETE FROM product_images
        WHERE product_id = ?
    ");
    $stmt->execute([$id]);
    // حذف المنتج
    $stmt = $pdo->prepare("
        DELETE FROM products
        WHERE id = ?
    ");
    $stmt->execute([$id]);

    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>إدارة المنتجات</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">

</head>

<body>

<div class="container mt-4">

<!-- الهيدر -->
<div class="card mb-4 p-3 shadow-sm">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

    <h3 class="mb-0"> إدارة المنتجات</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary"> ⬅️ لوحة التحكم</a>

</div>
</div>

<!-- تنبيه المخزون -->
<?php if($lowStockCount > 0): ?>
<div class="alert alert-danger">
⚠ يوجد <strong><?= $lowStockCount ?></strong> منتج الكمية فيه قليلة
</div>
<?php endif; ?>

<!-- البحث -->
<form method="GET" class="row mb-3">
<div class="col-12 col-md-9">
<input type="text"
       name="search"
       class="form-control"
       placeholder="🔍 ابحث عن منتج"
       value="<?= htmlspecialchars($search) ?>">
</div>

<div class="col-12 col-md-3">
<button class="btn btn-primary w-100">بحث</button>

</div>

</form>

<!-- فورم الإضافة / التعديل -->
<form method="POST" enctype="multipart/form-data" class="mb-4">
<input type="hidden" name="id" value="<?= $editProduct['id'] ?? '' ?>">

<div class="mb-2">
<label>اسم المنتج</label>
<input type="text"
       name="name"
       class="form-control"
       value="<?= $editProduct['name'] ?? '' ?>"
       required>
</div>

<div class="mb-2">
<label>السعر</label>
<input type="number"
       name="price"
       class="form-control"
       value="<?= $editProduct['price'] ?? '' ?>"
       required>
</div>

<div class="mb-2">
<label>الوصف</label>
<textarea name="description"
          class="form-control">
<?= $editProduct['description'] ?? '' ?>
</textarea>
</div>

<div class="mb-2">
<label>القسم</label>
<select name="category_id" class="form-control" required>
<?php foreach ($categories as $cat): ?>
<option value="<?= $cat['id'] ?>"
        <?= (isset($editProduct) && $editProduct['category_id']==$cat['id'])?'selected':'' ?>>
<?= $cat['name'] ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="mb-2">
<label>الكمية</label>
<input type="number"
       name="stock"
       class="form-control"
       value="<?= $editProduct['stock'] ?? 1 ?>">
</div>

<?php if (!$editProduct): ?>
<div class="mb-3">
<label>صور المنتج</label>
<input type="file"
       name="images[]"
       class="form-control"
       multiple
       required>
</div>
<?php endif; ?>

<button name="<?= $editProduct ? 'update_product' : 'add_product' ?>"
        class="btn btn-<?= $editProduct ? 'warning' : 'success' ?>">
<?= $editProduct ? 'تعديل المنتج' : 'إضافة المنتج' ?>
</button>

<?php if ($editProduct): ?>
<a href="products.php" class="btn btn-secondary">
إلغاء
</a>
<?php endif; ?>

</form>

<!-- جدول المنتجات -->
<div class="table-responsive">
<table class="table table-bordered text-center align-middle">

<tr>

<th>#</th>
<th>الصورة</th>
<th>الاسم</th>
<th>السعر</th>
<th>القسم</th>
<th>المخزون</th>
<th>إجراءات</th>

</tr>

<?php foreach ($products as $p): ?>

<?php
// جلب صورة واحدة للمنتج
$stmt = $pdo->prepare("
SELECT image 
FROM product_images 
WHERE product_id = ? 
ORDER BY id ASC 
LIMIT 1
");
$stmt->execute([$p['id']]);
$img = $stmt->fetch();
?>

<tr>

<td data-label="#"> 
<?= $p['id'] ?> 
</td>

<td data-label="الصورة">
<?php if ($img): ?>
<img src="../assets/images/<?= $img['image'] ?>" class="product-img">
<?php else: ?>
—
<?php endif; ?>
</td>

<td data-label="الاسم">
<?= $p['name'] ?>
</td>

<td data-label="السعر">
<?= $p['price'] ?> ج.م
</td>

<td data-label="القسم">
<?= $p['category_name'] ?>
</td>

<td data-label="المخزون">
<?= $p['stock'] ?>
</td>

<td data-label="إجراءات">

<a href="products.php?edit=<?= $p['id'] ?>"
   class="btn btn-warning btn-sm mb-1">
تعديل
</a>

<a href="products.php?delete=<?= $p['id'] ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('هل تريد حذف المنتج؟')">
حذف
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</div>

</body>
</html>