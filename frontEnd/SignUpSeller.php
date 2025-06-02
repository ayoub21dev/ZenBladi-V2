




<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فتح حساب كبائع</title>
    <link rel="stylesheet" href="assest/CSS/SignUpSeller.css">
    <link rel="stylesheet" href="../Includes/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700&display=swap" rel="stylesheet">

</head>
<body>
 
<?php
// SignUpSeller.php

include __DIR__ . '/../Includes/header.php';
?>



    <div class="form-container">
        <div class="form-header">
            <h1>فتح حساب كبائع</h1>
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
                        <label for="cooperativeName">إسم التعاونية</label>
                        <input type="text" id="cooperativeName" name="cooperativeName" placeholder="ادخل إسم التعاونية" >
                    </div>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" placeholder="ادخل بريدك الإلكتروني" required>
                    </div>
                    <div class="form-group">
                        <label for="password">كلمة المرور</label>
                        <input type="password" id="password" name="password" placeholder="ادخل كلمة المرور" required>
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber">رقم الهاتف</label>
                        <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="ادخل رقم الهاتف" required>
                    </div>
                    <div class="form-group">
                        <label for="address">العنوان</label>
                        <input type="text" id="address" name="address" placeholder="ادخل العنوان" required>
                    </div>
                    <div class="form-group">
                        <label for="city">المدينه</label>
                        <input type="text" id="city" name="city" placeholder="ادخل اسم المدينه" required>
                    </div>
                </div>
                <button type="submit" class="submit-btn">تسجيل</button>
                <p class="login-link">
                    لديك حساب بالفعل؟ <a href="#">تسجيل الدخول</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>