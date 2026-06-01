<!-- الصفحة مسؤولة عن تحديث حالة طلبات الإرجاع update_refund.php
استقبال رقم طلب الإرجاع
استقبال الحالة الجديدة
التحقق من الحالات المسموحة
تحديث الحالة داخل قاعدة البيانات
إعادة التوجيه لصفحة الإرجاعات -->
<?php

require "auth.php";
include "../includes/db.php";

// جلب رقم طلب الإرجاع وتحويله إلى رقم صحيح للحماية
$id = (int)$_GET['id'];

// جلب الحالة الجديدة
$status = $_GET['status'];

// الحالات المسموح بها فقط
$allowed = ['approved','rejected','refunded'];

// التحقق أن الحالة مرسلة بشكل صحيح
if(in_array($status, $allowed)) {

    // تحديث حالة طلب الإرجاع
    $stmt = $pdo->prepare("UPDATE refund_requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

header("Location: refunds.php");

exit();