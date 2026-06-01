<!--  صفحة تعديل كمية المنتجات داخل السلة update_cart.php
 الوظيفة
 زيادة كمية المنتج داخل السلة
 تقليل كمية المنتج داخل السلة
 حذف المنتج تلقائياً إذا أصبحت الكمية 0
-->
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// رقم المنتج
$id = $_GET['id'] ?? null;
// نوع العملية (زيادة أو تقليل)
$action = $_GET['action'] ?? null;

// التحقق أن المنتج موجود داخل السلة
if ($id && isset($_SESSION['cart'][$id])) {

    // زيادة الكمية
    if ($action === 'plus') { $_SESSION['cart'][$id]++;}
    // تقليل الكمية
    if ($action === 'minus') { $_SESSION['cart'][$id]--;

        // إذا أصبحت الكمية صفر أو أقل
        // نحذف المنتج من السلة
        if ($_SESSION['cart'][$id] <= 0) { unset($_SESSION['cart'][$id]);

        }
    }
}

header("Location: cart.php");
exit;