<!-- صفحة معلومات ديناميكية pages.php
 وظيفتها عرض صفحات ثابتة مثل
سياسة الاستخدام
الخصوصية
الشروط والأحكام
الاستبدال والإرجاع
التواصل-->
<?php 
include "includes/header.php";

$type = $_GET['type'] ?? '';

$title = '';
$content = '';

switch($type) {

    case 'policy':
        $title = "سياسة الاستخدام";
        $content = "باستخدامك لهذا الموقع فإنك توافقين على الالتزام بكافة الشروط والأحكام الخاصة بنا...";
        break;

    case 'privacy':
        $title = "الخصوصية والأمان";
        $content = "نحن نحترم خصوصيتك، وجميع بياناتك محمية ولا يتم مشاركتها مع أي طرف ثالث...";
        break;

    case 'terms':
        $title = "الشروط والأحكام";
        $content = "جميع الطلبات تخضع لتوفر المنتجات. يحق لنا تعديل الأسعار دون إشعار مسبق...";
        break;

    case 'return':
        $title = "الاستبدال والإرجاع";
        $content = "يمكنك طلب الاستبدال والاسترجاع خلال 7 أيام من استلام الطلب بشرط أن يكون المنتج بحالته الأصلية...";
        break;

    case 'contact':
        $title = "تواصلي معنا";
        $content = "يمكنك التواصل معنا عبر البريد الإلكتروني: luume-story@gmail.com";
        break;

    default:
        $title = "الصفحة غير موجودة";
        $content = "عذراً، الصفحة المطلوبة غير متوفرة.";
}
?>
<link rel="stylesheet" href="assets/css/pages.css">

<div class="container my-5 page-box">

    <h3 class="page-title"><?= $title ?></h3>

    <div class="page-content">
        <p><?= $content ?></p>
    </div>

</div>

<?php include "includes/footer.php"; ?>