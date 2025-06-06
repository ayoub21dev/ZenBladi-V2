<?php
session_start();
require_once '../BackEnd/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['login_message'] = 'البريد الإلكتروني وكلمة المرور مطلوبان.';
        $_SESSION['login_message_type'] = 'error';
    } else {
        // Try to authenticate as a client
        $stmt = $pdo->prepare("SELECT id, password FROM customer WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = 'client';
            header('Location: Client/Client.php');
            exit;
        }

        // Try to authenticate as a seller
        $stmt = $pdo->prepare("SELECT id, password FROM seller WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['seller_id'] = $user['id'];  // Changed from user_id to seller_id
            $_SESSION['user_type'] = 'seller';
            header('Location: Seller/Seller.php');
            exit;
        }

        // Try to authenticate as an admin
        $stmt = $pdo->prepare("SELECT id, password FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = 'admin';
            header('Location: Admin/admin.php');
            exit;
        }

        // If no user found or password incorrect
        $_SESSION['login_message'] = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
        $_SESSION['login_message_type'] = 'error';
    }
    
    // Redirect to the same page to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get message from session and clear it
$message = isset($_SESSION['login_message']) ? $_SESSION['login_message'] : '';
$messageType = isset($_SESSION['login_message_type']) ? $_SESSION['login_message_type'] : '';
unset($_SESSION['login_message'], $_SESSION['login_message_type']);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Includes/header.css">
    <link rel="stylesheet" href="assest/css/login.css">
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
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
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form action="" method="post">
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
                    ليس لديك حساب؟ <a href="SignUpClient.php" id="signup-link">سجل الآن</a>
                </p>
            </form>
        </div>
    </div>
    <script src="assest/JS/mainNavigation.js"></script> <!-- Or your combined navigation.js -->

</body>
</html>