<!-- الصفحة مسؤولة عن تسجيل دخول الأدمن admin-login.php
 استقبال اسم المستخدم وكلمة المرور
التحقق من بيانات الأدمن داخل قاعدة البيانات
مقارنة كلمة المرور المشفرة
إنشاء Session للأدمن
تحويله إلى لوحة التحكم عند نجاح تسجيل الدخول -->
<?php

include "../includes/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// متغير لتخزين رسالة الخطأ
$error = "";

// إذا تم إرسال البيانات بطريقة POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // جلب اسم المستخدم من الفورم
    $username = $_POST['username'];
    // جلب كلمة المرور من الفورم
    $password = $_POST['password'];

    // البحث عن الأدمن داخل قاعدة البيانات
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // إذا تم العثور على الأدمن وكلمة المرور صحيحة
    if ($admin && password_verify($password, $admin['password'])) {
        // إنشاء Session جديدة للحماية
        session_regenerate_id(true);

        // تخزين بيانات الأدمن داخل Session
        // رقم الأدمن
        $_SESSION['admin_id'] = $admin['id'];
        // اسم الأدمن
        $_SESSION['admin_username'] = $admin['full_name'];
        // وقت آخر نشاط
        $_SESSION['last_activity'] = time();

        // تحويل الأدمن للوحة التحكم
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "اسم المستخدم أو كلمة المرور غير صحيحة";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>تسجيل دخول الأدمن</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="auth-page" >

<div class="login-card">

<h4 class="text-center mb-4">لوحة تحكم الأدمن</h4>

<?php if (!empty($error)): ?>
<div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" id="loginForm">

<div class="mb-3">
<label>اسم المستخدم</label>
<input type="text" name="username" class="form-control" placeholder="أدخل اسم المستخدم" required>
</div>

<div class="mb-3">
<label>كلمة المرور</label>
<input type="password" name="password" class="form-control" placeholder="أدخل كلمة المرور" required>
</div>

<button type="submit" class="btn-main w-100" id="loginBtn">دخول</button>

</form>

</div>

<script>
 document.getElementById("loginForm").addEventListener("submit", function(){
    const btn = document.getElementById("loginBtn");
    btn.innerHTML = "جاري التحقق...";
    btn.disabled = true;
 });

</script>

</body>
</html>