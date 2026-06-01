<!--
معالجة الطلب النهائي بعد إتمام الشراء place_order.php
هذه الصفحة تستقبل بيانات العميل من checkout.php
 ثم تقوم 
 التحقق من وجود منتجات داخل السلة
 حساب إجمالي الطلب
 إنشاء سجل جديد داخل جدول orders
 حفظ المنتجات داخل جدول order_items
 تفريغ السلة بعد نجاح الطلب
 تحويل المستخدم إلى صفحة نجاح الطلب -->

<?php

include "includes/db.php";
session_start();

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    $_SESSION['error'] = "السلة فارغة، أضف منتجات أولاً.";
    header("Location: cart.php");
    exit();
}


$cart = $_SESSION['cart'];

/* بيانات العميل */
$name     = $_POST['name'];
$phone    = $_POST['phone'];
$address  = $_POST['address'];
$shipping = (int)$_POST['shipping'];
$payment_method = $_POST['payment'] ?? 'cod';

$subtotal = 0;

/* حساب المجموع */
foreach($cart as $product_id => $qty){
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id=?");
    $stmt->execute([$product_id]);
    $price = $stmt->fetchColumn();

    $subtotal += $price * $qty;
}

$total = $subtotal + $shipping;

/* إدخال الطلب */
$stmt = $pdo->prepare("
  INSERT INTO orders 
  (customer_name, phone, address, shipping_cost, total, payment_method) 
  VALUES (?,?,?,?,?,?)
");
$stmt->execute([$name, $phone, $address, $shipping, $total, $payment_method]);
$order_id = $pdo->lastInsertId();

/* إدخال المنتجات */
foreach($cart as $product_id => $qty){

    $stmt = $pdo->prepare("SELECT price FROM products WHERE id=?");
    $stmt->execute([$product_id]);
    $price = $stmt->fetchColumn();

    $stmt2 = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?,?,?,?)
    ");
    $stmt2->execute([$order_id, $product_id, $qty, $price]);   
}

/* تفريغ السلة */
unset($_SESSION['cart']);

header("Location: success.php?order_id=$order_id");
exit;
