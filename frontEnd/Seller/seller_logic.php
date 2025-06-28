<?php
// This file centralizes all the business logic for the seller dashboard.

require_once '../../backend/db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header('Location: ../login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];

// Fetch seller name
$stmt = $pdo->prepare("SELECT first_name, last_name FROM seller WHERE id = ?");
$stmt->execute([$seller_id]);
$seller = $stmt->fetch();
$seller_name = $seller ? $seller['first_name'] . ' ' . $seller['last_name'] : ' بائعنا';

// --- Fetch Seller Statistics ---
try {
    // Total Sales: Sum of totals for completed orders related to this seller's products
    $sales_stmt = $pdo->prepare("
        SELECT COALESCE(SUM(ol.total), 0) as total_sales
        FROM order_link ol
        JOIN product p ON ol.product_id = p.id
        JOIN customer_order co ON ol.order_id = co.id
        WHERE p.seller_id = ? AND co.status = 'تم التوصيل'
    ");
    $sales_stmt->execute([$seller_id]);
    $total_sales = $sales_stmt->fetch()['total_sales'];

    // Orders Pending: Count of orders with products from this seller that are pending
    $pending_stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT co.id) as pending_orders
        FROM customer_order co
        JOIN order_link ol ON co.id = ol.order_id
        JOIN product p ON ol.product_id = p.id
        WHERE p.seller_id = ? AND co.status = 'قيد المعالجة'
    ");
    $pending_stmt->execute([$seller_id]);
    $pending_orders = $pending_stmt->fetch()['pending_orders'];
    
    // Total Orders: Count of all orders containing products from this seller
    $orders_stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT co.id) as total_orders
        FROM customer_order co
        JOIN order_link ol ON co.id = ol.order_id
        JOIN product p ON ol.product_id = p.id
        WHERE p.seller_id = ?
    ");
    $orders_stmt->execute([$seller_id]);
    $total_orders = $orders_stmt->fetch()['total_orders'];

    // Total Products
    $products_stmt = $pdo->prepare("SELECT COUNT(*) as total_products FROM product WHERE seller_id = ?");
    $products_stmt->execute([$seller_id]);
    $total_products = $products_stmt->fetch()['total_products'];

    // Latest Orders (last 5)
    $latest_orders_stmt = $pdo->prepare("
        SELECT DISTINCT co.id, co.status, co.order_date, c.first_name, c.last_name
        FROM customer_order co
        JOIN order_link ol ON co.id = ol.order_id
        JOIN product p ON ol.product_id = p.id
        JOIN customer c ON co.customer_id = c.id
        WHERE p.seller_id = ?
        ORDER BY co.order_date DESC
        LIMIT 5
    ");
    $latest_orders_stmt->execute([$seller_id]);
    $latest_orders = $latest_orders_stmt->fetchAll();

    // Latest Products (last 5)
    $latest_products_stmt = $pdo->prepare("
        SELECT id, name, price, image, created_at 
        FROM product 
        WHERE seller_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $latest_products_stmt->execute([$seller_id]);
    $latest_products = $latest_products_stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    // Fallback values
    $total_sales = $pending_orders = $total_orders = $total_products = 0;
    $latest_orders = $latest_products = [];
} 