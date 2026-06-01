<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تحديد نوع البيانات المرسلة للمتصفح (JSON)
header('Content-Type: application/json');

// إخفاء الأخطاء حتى لا تظهر كـ HTML وتكسر JSON
error_reporting(0);

// استقبال id المنتج من POST
$id = $_POST['id'] ?? null;

// استقبال الكمية، وإذا غير موجودة تكون 1
$qty = isset($_POST['qty'])
    ? (int)$_POST['qty']
    : 1;

// التحقق من وجود id
if (!$id) {

    // إرجاع رسالة خطأ بصيغة JSON
    echo json_encode([
        "status" => "error",
        "message" => "Invalid product"
    ]);

    exit; 
}

// منع القيم السالبة أو الصفر
if ($qty < 1) {
    $qty = 1;
}

// إذا السلة غير موجودة، ننشئها
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// إذا المنتج غير موجود في السلة، نبدأه بـ 0
if (!isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id] = 0;
}

// إضافة الكمية إلى المنتج داخل السلة
$_SESSION['cart'][$id] += $qty;

// حساب إجمالي عدد المنتجات في السلة
$count = array_sum($_SESSION['cart']);

// إرجاع النتيجة للـ AJAX
echo json_encode([
    "status" => "success",
    "count" => $count
]);

exit;