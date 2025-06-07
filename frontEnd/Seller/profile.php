<?php
session_start();
require_once '../../backend/db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header('Location: ../login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];

// Fetch seller info
$seller = [];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update seller info
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $address = $_POST['address'] ?? '';
    $store_name = $_POST['store_name'] ?? '';
    $city = $_POST['city'] ?? '';

    $update_query = "UPDATE seller SET first_name=?, last_name=?, email=?, phone_number=?, address=?, store_name=?, city=? WHERE id=?";
    $stmt = $pdo->prepare($update_query);
    if ($stmt->execute([$first_name, $last_name, $email, $phone_number, $address, $store_name, $city, $seller_id])) {
        $msg = 'تم تحديث المعلومات بنجاح';
    } else {
        $msg = 'حدث خطأ أثناء تحديث المعلومات';
    }
}

// Always fetch the latest info
$info_query = "SELECT first_name, last_name, email, phone_number, address, store_name, city FROM seller WHERE id = ?";
$stmt = $pdo->prepare($info_query);
$stmt->execute([$seller_id]);
$seller = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي للبائع</title>
    <link rel="stylesheet" href="../assest/CSS/Seller.css">
    <link rel="stylesheet" href="../../Includes/Header.css">
    <link rel="stylesheet" href="../assest/CSS/Seller/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;700&display=swap" rel="stylesheet">
 
</head>
<body>
    <a href="Seller.php" style="display:inline-block;margin:30px 0 0 0;padding:10px 28px;background:linear-gradient(135deg,#5E8C6A 60%,#4a7c4a 100%);color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:bold;text-decoration:none;box-shadow:0 2px 8px rgba(46,125,80,0.10);transition:background 0.2s;">&larr; رجوع</a>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <span>👤</span>
            </div>
            <h2><?= htmlspecialchars(($seller['first_name'] ?? '') . ' ' . ($seller['last_name'] ?? '')) ?></h2>
            <span><?= htmlspecialchars($seller['store_name'] ?? '') ?></span>
        </div>
        <?php if ($msg): ?>
            <div class="msg"> <?= htmlspecialchars($msg) ?> </div>
        <?php endif; ?>
        <form class="profile-form" method="post">
            <label for="first_name">الاسم الأول</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($seller['first_name'] ?? '') ?>" required>

            <label for="last_name">اسم العائلة</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($seller['last_name'] ?? '') ?>" required>

            <label for="email">البريد الإلكتروني</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($seller['email'] ?? '') ?>" required>

            <label for="phone_number">رقم الهاتف</label>
            <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($seller['phone_number'] ?? '') ?>" required>

            <label for="address">العنوان</label>
            <input type="text" id="address" name="address" value="<?= htmlspecialchars($seller['address'] ?? '') ?>" required>

            <label for="store_name">اسم المتجر</label>
            <input type="text" id="store_name" name="store_name" value="<?= htmlspecialchars($seller['store_name'] ?? '') ?>" required>

            <label for="city">المدينة</label>
            <input type="text" id="city" name="city" value="<?= htmlspecialchars($seller['city'] ?? '') ?>" required>

            <button type="submit">تحديث المعلومات</button>
        </form>
    </div>
</body>
</html>
