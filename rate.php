<?php
include "includes/db.php";

$product_id = $_POST['product_id'];
$rating = $_POST['rating'];

$stmt = $pdo->prepare("INSERT INTO ratings (product_id, rating) VALUES (?, ?)");
$stmt->execute([$product_id, $rating]);

header("Location: product.php?id=$product_id");