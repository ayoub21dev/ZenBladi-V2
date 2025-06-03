<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Includes/header.css">
    <link rel="stylesheet" href="assest/css/login.css">
</head>
<body>

<?php
// Include the header
include __DIR__ . '/../Includes/header.php';
?>

    <div class="form-container">
        <div class="form-header">
            <h1>تسجيل الدخول</h1>
        </div>
        <div class="form-content">
            <form>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="ادخل بريدك الإلكتروني" required>
                </div>
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="ادخل كلمة المرور" required>
                </div>
                <button type="submit" class="submit-btn">تسجيل الدخول</button>
                <p class="login-link">
                    ليس لديك حساب؟ <a href="SignUpClient.php" id="signup-link">سجل الآن</a> <!-- This ID is handled by navigation.js if you kept that -->
                </p>
            </form>
        </div>
    </div>
    <script src="assest/JS/mainNavigation.js"></script> <!-- For header buttons -->
   
</body>
</html>