<?php
// This file centralizes all the business logic for the client dashboard.

session_start();
require_once '../../backend/db.php'; // Adjust path as needed

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$customerId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle Account Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (!empty($password) && $password !== $confirmPassword) {
        $message = 'كلمتا المرور غير متطابقتين.';
        $messageType = 'error';
    } else {
        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT id FROM customer WHERE email = ? AND id != ?");
        $stmt->execute([$email, $customerId]);
        if ($stmt->fetch()) {
            $message = 'هذا البريد الإلكتروني مستخدم بالفعل.';
            $messageType = 'error';
        } else {
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE customer SET first_name = ?, last_name = ?, email = ?, password = ? WHERE id = ?");
                $updateStmt->execute([$firstName, $lastName, $email, $hashedPassword, $customerId]);
            } else {
                $updateStmt = $pdo->prepare("UPDATE customer SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                $updateStmt->execute([$firstName, $lastName, $email, $customerId]);
            }
            $message = 'تم تحديث معلومات الحساب بنجاح.';
            $messageType = 'success';
        }
    }
}

// Fetch customer's data
$stmt = $pdo->prepare("SELECT first_name, last_name, email FROM customer WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();
$customerName = $customer ? $customer['first_name'] : 'عميلنا';

// --- Fetch Stats ---
$totalOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM customer_order WHERE customer_id = ?");
$totalOrdersStmt->execute([$customerId]);
$totalOrders = $totalOrdersStmt->fetchColumn();

$inProgressOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM customer_order WHERE customer_id = ? AND status IN ('قيد المعالجة', 'مؤكد', 'تم الشحن')");
$inProgressOrdersStmt->execute([$customerId]);
$inProgressOrders = $inProgressOrdersStmt->fetchColumn();

$deliveredOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM customer_order WHERE customer_id = ? AND status = 'تم التوصيل'");
$deliveredOrdersStmt->execute([$customerId]);
$deliveredOrders = $deliveredOrdersStmt->fetchColumn();

// --- Fetch Orders (for My Orders and Dashboard) ---
$allOrdersStmt = $pdo->prepare("SELECT id, order_date, status FROM customer_order WHERE customer_id = ? ORDER BY order_date DESC");
$allOrdersStmt->execute([$customerId]);
$allOrders = $allOrdersStmt->fetchAll();
$recentOrders = array_slice($allOrders, 0, 5);

function getOrderTotal($orderId, $pdo) {
    $stmt = $pdo->prepare("SELECT SUM(total) FROM order_link WHERE order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetchColumn() ?: 0;
} 