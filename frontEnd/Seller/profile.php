<?php
// This is a partial file for the profile section.
// It expects $seller_id and $pdo to be defined from seller_logic.php

$profile_message = '';
$profile_message_type = '';

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $store_name = trim($_POST['store_name']);
    $city = trim($_POST['city']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($password) && $password !== $confirm_password) {
        $profile_message = 'كلمتا المرور غير متطابقتين.';
        $profile_message_type = 'error';
    } else {
        // Check if email is used by another seller
        $email_stmt = $pdo->prepare("SELECT id FROM seller WHERE email = ? AND id != ?");
        $email_stmt->execute([$email, $seller_id]);
        if ($email_stmt->fetch()) {
            $profile_message = 'هذا البريد الإلكتروني مستخدم بالفعل.';
            $profile_message_type = 'error';
        } else {
            // Update logic
            $sql_parts = [];
            $params = [];

            $sql_parts[] = "first_name = ?";
            $params[] = $first_name;
            $sql_parts[] = "last_name = ?";
            $params[] = $last_name;
            $sql_parts[] = "email = ?";
            $params[] = $email;
            $sql_parts[] = "phone_number = ?";
            $params[] = $phone_number;
            $sql_parts[] = "address = ?";
            $params[] = $address;
            $sql_parts[] = "store_name = ?";
            $params[] = $store_name;
            $sql_parts[] = "city = ?";
            $params[] = $city;

            if (!empty($password)) {
                $sql_parts[] = "password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $params[] = $seller_id;
            $sql = "UPDATE seller SET " . implode(', ', $sql_parts) . " WHERE id = ?";
            
            $update_stmt = $pdo->prepare($sql);
            $update_stmt->execute($params);

            $profile_message = 'تم تحديث الملف الشخصي بنجاح.';
            $profile_message_type = 'success';
        }
    }
}

// Fetch current seller data for the form
$seller_data_stmt = $pdo->prepare("SELECT * FROM seller WHERE id = ?");
$seller_data_stmt->execute([$seller_id]);
$seller_data = $seller_data_stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي للبائع</title>
    <link rel="stylesheet" href="../assest/CSS/Seller.css">
    
    <link rel="stylesheet" href="../assest/CSS/Seller/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;700&display=swap" rel="stylesheet">
 
</head>
<body>
    <a href="Seller.php" style="display:inline-block;margin:30px 0 0 0;padding:10px 28px;background:linear-gradient(135deg,#5E8C6A 60%,#4a7c4a 100%);color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:bold;text-decoration:none;box-shadow:0 2px 8px rgba(46,125,80,0.10);transition:background 0.2s;">&larr; رجوع</a>
    <section id="profile" class="dashboard-section">
        <div class="profile-container">
            
            <div class="profile-header">
                <div class="avatar">
                    <span><i class="fas fa-user"></i></span>
                </div>
                <h2><?= htmlspecialchars($seller_data['first_name'] . ' ' . $seller_data['last_name']) ?></h2>
                <p><?= htmlspecialchars($seller_data['store_name']) ?></p>
            </div>

            <?php if (!empty($profile_message)): ?>
                <div class="message <?= htmlspecialchars($profile_message_type) ?>">
                    <?= htmlspecialchars($profile_message) ?>
                </div>
            <?php endif; ?>

            <form class="profile-form" action="#profile" method="post">
                <div class="form-section-header">
                    <h3>معلومات الحساب</h3>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">الاسم الأول</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($seller_data['first_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">الاسم الأخير</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($seller_data['last_name']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($seller_data['email']) ?>" required>
                    </div>
                     <div class="form-group">
                        <label for="phone_number">رقم الهاتف</label>
                        <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($seller_data['phone_number']) ?>" required>
                    </div>
                </div>
                 <div class="form-section-header">
                    <h3>معلومات المتجر</h3>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="store_name">اسم المتجر</label>
                        <input type="text" id="store_name" name="store_name" value="<?= htmlspecialchars($seller_data['store_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="city">المدينة</label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($seller_data['city']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">العنوان</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($seller_data['address']) ?>" required>
                </div>
                <div class="form-section-header">
                    <h3>تغيير كلمة المرور</h3>
                    <p>(اترك الحقول فارغة لعدم التغيير)</p>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">كلمة المرور الجديدة</label>
                        <input type="password" id="password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">تأكيد كلمة المرور</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </section>
</body>
</html>
