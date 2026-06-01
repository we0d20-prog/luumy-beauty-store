<!--  صفحة طلب إرجاع المنتج refund.php
 الوظيفة
 السماح للعميل بطلب إرجاع منتج
 التحقق من صلاحية الإرجاع
 التحقق من حالة الطلب
 منع تكرار طلبات الإرجاع
 رفع صورة للمنتج (اختياري)
 حفظ الطلب داخل قاعدة البيانات-->
<?php

session_start();

include "includes/db.php";
include "includes/header.php";

// التحقق من وجود رقم الطلب والمنتج
if (
    !isset($_GET['order_id']) ||
    !isset($_GET['product_id'])
) {
    header("Location: track_order.php");
    exit;
}

// تحويل القيم إلى Integer للحماية
$order_id   = intval($_GET['order_id']);
$product_id = intval($_GET['product_id']);

// جلب بيانات المنتج
$stmt = $pdo->prepare("
    SELECT p.name
    FROM products p
    JOIN order_items oi
    ON oi.product_id = p.id
    WHERE oi.order_id = ?
    AND p.id = ?
    LIMIT 1
");
$stmt->execute([$order_id, $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// إذا المنتج غير موجود
if (!$product) {
    echo "المنتج غير موجود.";
    exit;
}

// جلب بيانات الطلب
$stmtOrder = $pdo->prepare("SELECT created_at, status FROM orders WHERE id = ?");
$stmtOrder->execute([$order_id]);
$orderData = $stmtOrder->fetch(PDO::FETCH_ASSOC);

// الحالات المسموح لها بالإرجاع
$allowedStatuses = ['completed','shipped'];

// منع الإرجاع إذا الحالة غير مسموحة
if (!$orderData ||!in_array($orderData['status'], $allowedStatuses)) {

    die("
    <div style='
        max-width:500px;
        margin:60px auto;
        background:#ffe5e5;
        padding:20px;
        border-radius:14px;
        text-align:center;
        font-family:Cairo,sans-serif;
    '>
    لا يمكن طلب إرجاع لهذا الطلب حالياً
    </div>
    ");
}

// عدد الأيام المسموح بها
$allowedDays = 7;
// هل الإرجاع متاح؟
$canRefund = false;
// الأيام المتبقية
$daysLeft = 0;

// التحقق من مدة الإرجاع
if ($orderData) {
    // تاريخ الطلب
    $orderDate = new DateTime(
        $orderData['created_at']
    );
    // تاريخ اليوم
    $today = new DateTime();
    // الفرق بين التاريخين
    $diff = $today->diff($orderDate)->days;
    // إذا ضمن المدة المسموحة
    if ($diff <= $allowedDays) {
        $canRefund = true;
        $daysLeft = $allowedDays - $diff;
    }
}

// معالجة إرسال طلب الإرجاع
if ($_SERVER["REQUEST_METHOD"] == "POST" && $canRefund) {

    // استقبال البيانات
    $reason  = $_POST['reason'];
    $details = $_POST['details'] ?? '';
    $iban    = $_POST['iban'];

    // التحقق من وجود طلب إرجاع مسبق
    $stmtCheck = $pdo->prepare("SELECT id,status FROM refund_requests WHERE order_id = ? AND product_id = ?  LIMIT 1 ");
    $stmtCheck->execute([$order_id, $product_id]);
    $existingRefund = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    // إذا يوجد طلب مسبق
    if ($existingRefund) {

        $refundStatus = $existingRefund['status'];
        // حالة قيد المراجعة
        if($refundStatus == 'pending') {
            $msg = "
            لديك طلب إرجاع قيد المراجعة لهذا المنتج
            ";
        }
        // تمت الموافقة
        elseif($refundStatus == 'approved') {
            $msg = "
            تمت الموافقة مسبقاً على طلب الإرجاع
            ";
        }
        // تم الاسترجاع
        elseif($refundStatus == 'refunded') {
            $msg = "
            تم استرجاع هذا المنتج مسبقاً
            ";
        }
        // أي حالة أخرى
        else {
            $msg = "
            يوجد طلب إرجاع مسبق لهذا المنتج
            ";
        }

        // عرض الرسالة
        die("
        <div style='
            max-width:500px;
            margin:60px auto;
            background:#fff3cd;
            padding:20px;
            border-radius:14px;
            text-align:center;
            font-family:Cairo,sans-serif;
        '>
        $msg
        </div>
        ");
    }

    // رفع صورة المنتج (اختياري)
    $imageName = null;
    // إذا تم رفع صورة
    if (!empty($_FILES['image']['name'])) {
        // مجلد الصور
        $uploadDir = "assets/refunds/";
        // إنشاء المجلد إذا غير موجود
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // اسم الصورة
        $imageName =
            time() . "_" . $_FILES['image']['name'];
        // نقل الصورة للمجلد
        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $uploadDir . $imageName
        );
    }

    // حفظ طلب الإرجاع
    $stmtInsert = $pdo->prepare("
        INSERT INTO refund_requests
        (
            order_id,
            product_id,
            reason,
            details,
            bank_account,
            image,
            status,
            created_at
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?,
            'pending',
            NOW()
        )
    ");
    $stmtInsert->execute([$order_id, $product_id, $reason, $details, $iban, $imageName]);
    $success = true;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1">
<title>
طلب إرجاع
</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/refund.css">
</head>
<body>

<!-- صندوق الإرجاع -->
<div class="container-refund">
<div class="card-refund">

<h4 class="title mb-3 text-center">طلب إرجاع منتج</h4>

<!-- رسالة النجاح -->
<?php if(!empty($success)): ?>

<div class="alert alert-success text-center">
✔ تم إرسال طلب الإرجاع بنجاح
<br>
سيتم مراجعته خلال 24 ساعة
</div>

<!-- نموذج الإرجاع -->
<?php elseif($canRefund): ?>

<p class="text-center mb-2">
<strong>
رقم الطلب:
</strong>
#<?= $order_id ?>
<br>

<strong>
المنتج:
</strong>
<?= htmlspecialchars($product['name']) ?>
</p>

<!-- الأيام المتبقية -->
<div class="alert alert-info text-center">
متبقي
<?= $daysLeft ?>
يوم على انتهاء صلاحية الإرجاع
</div>

<!-- فورم الإرجاع -->
<form method="POST" enctype="multipart/form-data">

<!-- سبب الإرجاع -->
<label class="form-label">سبب الإرجاع</label>
<select name="reason" class="form-control mb-3" required>

<option value="">اختر سبب الإرجاع</option>

<option>المنتج تالف</option>
<option>منتج خاطئ</option>
<option>المقاس غير مناسب</option>
<option>غيرت رأيي</option>
<option>أخرى</option>

</select>

<!-- تفاصيل إضافية -->
<label class="form-label">تفاصيل إضافية (اختياري)</label>
<textarea name="details" class="form-control mb-3" rows="3"></textarea>

<!-- الآيبان -->
<label class="form-label">رقم الآيبان (IBAN)</label>
<input type="text" name="iban" class="form-control mb-3" inputmode="text" placeholder="EGxxxxxxxxxxxxxxxxxxxx"required>

<!-- رفع صورة -->
<label class="form-label">إرفاق صورة (اختياري)</label>
<input type="file" name="image" class="form-control mb-4">

<button type="submit" class="btn-main w-100">إرسال طلب الإرجاع</button>

</form>

<!-- انتهاء مدة الإرجاع -->
<?php else: ?>

<div class="alert alert-danger text-center">
    انتهت مدة الإرجاع 
(<?= $allowedDays ?> أيام فقط مسموحة)
</div>

<?php endif; ?>

</div>
</div>

</body>

<?php include "includes/footer.php"; ?>

</html>