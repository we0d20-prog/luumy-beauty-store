<?php
session_start();

// نحذف كل بيانات الجلسة الخاصة بالأدمن
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']); // لو كنت مخزن اسم الأدمن

// تدمير الجلسة بالكامل
session_destroy();

// إعادة التوجيه لصفحة تسجيل الدخول
header("Location: login.php");
exit;