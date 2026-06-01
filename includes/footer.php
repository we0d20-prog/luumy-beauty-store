<!-- وظيفة الصفحة بشكل عام footer.php
تعرض قسم مميزات المتجر (الشحن - الجودة - الدفع الآمن - الدعم).
تعرض الفوتر الخاص بالموقع.
تحتوي على روابط الصفحات المهمة.
تعرض أيقونات السوشيال ميديا.
تعرض شعار المتجر.
تربط ملفات CSS و JavaScript اللازمة لتنسيق وتشغيل الموقع-->
<link rel="stylesheet" href="assets/css/main.css">
<link rel="stylesheet" href="assets/css/footer.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

<!-- قسم المميزات أعلى الفوتر -->
<section class="features-section">

    <div class="container">
        <div class="row text-center align-items-center">

            <!-- ميزة الشحن السريع -->
            <div class="col-6 col-md-3">
                <div class="feature-item">
                    <i class="bi bi-truck"></i>
                    <h6>شحن سريع</h6>
                    <p>توصيل خلال 2-4 أيام</p>
                </div>
            </div>

            <!-- ميزة المنتجات الأصلية -->
            <div class="col-6 col-md-3">
                <div class="feature-item">
                    <i class="bi bi-patch-check"></i>
                    <h6>منتجات أصلية</h6>
                    <p>جودة مضمونة 100%</p>
                </div>
            </div>

            <!-- ميزة الدفع الآمن -->
            <div class="col-6 col-md-3">
                <div class="feature-item">
                    <i class="bi bi-shield-lock"></i>
                    <h6>دفع آمن</h6>
                    <p>حماية كاملة لبياناتك</p>
                </div>
            </div>

            <!-- ميزة الدعم السريع -->
            <div class="col-6 col-md-3">
                <div class="feature-item">
                    <i class="bi bi-headset"></i>
                    <h6>دعم سريع</h6>
                    <p>نحن هنا لمساعدتك</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- بداية الفوتر -->
<footer class="premium-footer">

    <div class="container">
        <div class="footer-wrapper">

            <!-- القسم الأيسر: أيقونات التواصل -->
            <div class="footer-left">

                <!-- روابط السوشيال ميديا -->
                <div class="social-icons">
                    <a href="#">
                        <i class="bi bi-facebook"></i>
                    </a>

                    <a href="#">
                        <i class="bi bi-instagram"></i>
                    </a>

                    <a href="#">
                        <i class="bi bi-twitter-x"></i>
                    </a>

                    <a href="#">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- القسم الأوسط: روابط الصفحات -->
            <div class="footer-center">
                <ul class="footer-links">

                    <li>
                        <a href="pages.php?type=policy">
                            سياسة الاستخدام
                        </a>
                    </li>

                    <li>
                        <a href="pages.php?type=privacy">
                            الخصوصية والأمان
                        </a>
                    </li>

                    <li>
                        <a href="pages.php?type=terms">
                            الشروط والأحكام
                        </a>
                    </li>

                    <li>
                        <a href="pages.php?type=return">
                            الاستبدال والإرجاع
                        </a>
                    </li>

                    <li>
                        <a href="pages.php?type=contact">
                            تواصلي معنا
                        </a>
                    </li>

                </ul>
            </div>

            <!-- القسم الأيمن: شعار الموقع -->
            <div class="footer-right">
                <a href="index.php" class="footer-logo">
                    <img src="assets/images/logo-luumygrd.webp" alt="Luumy Logo">
                </a>
            </div>

        </div>

    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js?v=<?= time(); ?>"></script>

</body>
</html>