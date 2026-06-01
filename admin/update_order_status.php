<!-- الصفحة مسؤولة عن تحديث حالة الطلبات داخل لوحة تحكم الأدمن admin-update_order_status.php
استقبال رقم الطلب
استقبال الحالة الجديدة
تحديث حالة الطلب داخل قاعدة البيانات
إعادة التوجيه لصفحة الطلبات -->
<?php

require "auth.php";
include "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    exit;
}

// جلب رقم الطلب من الفورم
$order_id = $_POST['order_id'];

// جلب الحالة الجديدة
$status = $_POST['status'];

// تحديث حالة الطلب داخل قاعدة البيانات
$stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->execute([$status, $order_id]);

header("Location: orders.php");

exit;