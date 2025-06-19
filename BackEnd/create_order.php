<?php
session_start();
require_once 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or show an error
    header('Location: ../frontEnd/login.php?error=login_required');
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect back or show an error
    header('Location: ../frontEnd/index.php');
    exit;
}

// --- Input validation and sanitation ---
$customerId = $_SESSION['user_id'];
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$totalPrice = filter_input(INPUT_POST, 'total_price', FILTER_VALIDATE_FLOAT);

$shippingFullname = filter_input(INPUT_POST, 'shipping_fullname', FILTER_SANITIZE_STRING);
$shippingPhone = filter_input(INPUT_POST, 'shipping_phone', FILTER_SANITIZE_STRING);
$shippingAddress = filter_input(INPUT_POST, 'shipping_address', FILTER_SANITIZE_STRING);
$shippingCity = filter_input(INPUT_POST, 'shipping_city', FILTER_SANITIZE_STRING);

// Basic validation check
if (!$productId || !$quantity || !$totalPrice || empty($shippingFullname) || empty($shippingPhone) || empty($shippingAddress) || empty($shippingCity)) {
    // Handle error - maybe redirect back with an error message
    header('Location: ../frontEnd/product_detail.php?id=' . $productId . '&error=invalid_input');
    exit;
}

// --- Database Operations ---
try {
    $pdo->beginTransaction();

    // 1. Insert into customer_order table
    $orderSql = "INSERT INTO customer_order (customer_id, order_date, shipping_fullname, shipping_phone, shipping_address, shipping_city, status) 
                 VALUES (:customer_id, CURDATE(), :shipping_fullname, :shipping_phone, :shipping_address, :shipping_city, 'قيد المعالجة')";
    
    $orderStmt = $pdo->prepare($orderSql);
    $orderStmt->execute([
        ':customer_id' => $customerId,
        ':shipping_fullname' => $shippingFullname,
        ':shipping_phone' => $shippingPhone,
        ':shipping_address' => $shippingAddress,
        ':shipping_city' => $shippingCity,
    ]);

    // 2. Get the last inserted order ID
    $orderId = $pdo->lastInsertId();

    // 3. Insert into order_link table
    $linkSql = "INSERT INTO order_link (order_id, product_id, quantity, total) 
                VALUES (:order_id, :product_id, :quantity, :total)";
    
    $linkStmt = $pdo->prepare($linkSql);
    $linkStmt->execute([
        ':order_id' => $orderId,
        ':product_id' => $productId,
        ':quantity' => $quantity,
        ':total' => $totalPrice,
    ]);

    // If all good, commit the transaction
    $pdo->commit();

    // Redirect to a success page
    header('Location: ../frontEnd/order_confirmation.php?order_id=' . $orderId);
    exit;

} catch (PDOException $e) {
    // If something went wrong, roll back
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log the error and redirect
    error_log('Order creation failed: ' . $e->getMessage());
    header('Location: ../frontEnd/product_detail.php?id=' . $productId . '&error=order_failed');
    exit;
} 