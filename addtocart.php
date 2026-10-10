<?php
session_start();
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products.php");
    exit();
}

$id = (int)($_POST['product_id'] ?? 0);

$stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    $_SESSION['cart_msg'] = "That product no longer exists.";
} else {
    $current = $_SESSION['cart'][$id] ?? 0;

    if ($current >= (int)$product['stock']) {
        $_SESSION['cart_msg'] = "Maximum stock reached for this product.";
    } else {
        $_SESSION['cart'][$id] = $current + 1;
    }
}

// Go back to the page the user came from
$back = $_SERVER['HTTP_REFERER'] ?? "products.php";
header("Location: " . $back);
exit();