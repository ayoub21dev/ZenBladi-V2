<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فتح حساب كعميل</title>
    <link rel="stylesheet" href="assest/CSS/SignUpClient.css">
    <link rel="stylesheet" href="../Includes/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php
// Include the header
include __DIR__ . '/../Includes/header.php';
?>

<div class="form-container">
    <div class="form-header">
        <h1>فتح حساب جديد</h1>
    </div>
    <div class="form-content">
        <form action="#" method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="firstName">اسم الأول</label>
                    <input type="text" id="firstName" name="firstName" placeholder="ادخل اسمك الأول" required>
                </div>
                <div class="form-group">
                    <label for="lastName">اسم الأخير</label>
                    <input type="text" id="lastName" name="lastName" placeholder="ادخل اسمك الأخير" required>
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="ادخل بريدك الإلكتروني" required>
                </div>
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="ادخل كلمة المرور" required>
                </div>
               
               
            </div>
            <button type="submit" class="submit-btn">تسجيل</button>
            <p class="login-link" id="login-link-client">
                لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
            </p>
            <p class="login-link" id="signup-seller-link">
                هل أنت بائع؟ <a href="SignUpSeller.php">إفتح حساب</a>
            </p>
        </form>
    </div>
</div>

<script src="assest/JS/mainNavigation.js"></script> <!-- For header buttons -->

</body>
</html>