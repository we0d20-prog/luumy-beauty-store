<!-- هذه الصفحة مسؤولة عن header.php
عرض الأقسام ,الهيدر الرئيسي للموقع ,عرض الشعار, البحث المباشر
عرض السلة ,حساب عدد المنتجات ,تتبع الطلب ,تأثيرات الهيدر, تشغيل البحث اللحظي AJAX-->
<?php
include 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$cart_count = 0;

if(isset($_SESSION['cart'])) {
    // جمع كميات المنتجات داخل السلة
    $cart_count = array_sum($_SESSION['cart']);
}

// جلب جميع الأقسام من قاعدة البيانات
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$current_category = $_GET['category'] ?? null;

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>متجر التجميل</title>
 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/main.css">

</head>
<body>

<header class="site-header">

<div class="top-header">
    <div class="container header-flex">

        <div class="header-right">
            <a href="index.php" class="logo">
                <img src="assets/images/logo-luumy.webp" alt="Logo">
            </a>
        </div>
           
        <div class="header-center">
           <div class="search-box">
              <div class="search-wrapper">
                  <i class="fas fa-search search-icon"></i>
                <input 
                    type="text" 
                    id="liveSearch"
                    placeholder="ابحث عن منتج..."
                    class="search-input"
                    autocomplete="off"
                >
              </div>
                <div id="searchResults" class="live-search-results"></div>
           </div>
        </div>
    
        <div class="header-left">
            <a href="track_order.php" class="header-link">
                <i class="fa-solid fa-location-dot"></i>
                تتبع طلبك
            </a>

            <a href="cart.php" class="cart-btn position-relative">
                <i class="fa-solid fa-bag-shopping"></i>
                <!-- عدد المنتجات داخل السلة -->
                <span id="cart-count" class="cart-count">
                    <?= $cart_count ?>
                </span>
            </a>
            
        </div>
        
    </div>
</div>

<!-- الأقسام -->
<div class="bottom-header ">
    <div class="container">

        <ul class="nav justify-content-center">
            <!-- لوب لعرض جميع الأقسام -->
            <?php foreach($categories as $cat): ?>

                <li class="nav-item">
                    <!-- رابط القسم -->
                    <a class="nav-link <?= ($current_category == $cat['id']) ? 'active-category' : ''; ?>"
                       href="products.php?category=<?= $cat['id']; ?>">
                        <!-- اسم القسم -->
                        <?= htmlspecialchars($cat['name']); ?>
                    </a>
                </li>

            <?php endforeach; ?>
        </ul>

    </div>
</div>

</header>

<script>

 document.addEventListener("DOMContentLoaded", function() {

    const input = document.getElementById("liveSearch");
    const resultsBox = document.getElementById("searchResults");

    if (!input || !resultsBox) return;

    let timeout;

    input.addEventListener("keyup", function() {

        clearTimeout(timeout);

        timeout = setTimeout(() => {

            let query = input.value.trim();

            if (query.length < 1) {

                resultsBox.style.display = "none";
                resultsBox.innerHTML = "";
                return;
            }

            fetch("search_products.php?ajax=1&search=" + encodeURIComponent(query))

                .then(res => res.text())
                .then(data => {

                    resultsBox.innerHTML = data.trim() !== "" 
                        ? data 
                        : "<div style='padding:10px'>لا توجد نتائج</div>";
                    resultsBox.style.display = "block";
                })

                .catch(err => console.error("Search Error:", err));
        }, 300);
    });

    document.addEventListener("click", function(e) {

        if (!e.target.closest(".search-box")) {
            resultsBox.style.display = "none";
        }
    });

    window.addEventListener("scroll", function () {

        let header = document.querySelector(".site-header");

        if (header) {
            header.classList.toggle("scrolled", window.scrollY > 10);
        }
    });

 });

</script>