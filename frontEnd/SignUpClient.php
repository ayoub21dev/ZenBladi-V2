<?php
// Include database connection
require_once '../BackEnd/db.php';

$message = '';
$messageType = '';

// Initialize form fields to prevent undefined variable notices in the HTML form
$firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
$lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
// Password is not pre-filled for security

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Re-assign from POST for processing
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $message = 'جميع الحقول مطلوبة';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'البريد الإلكتروني غير صحيح';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        $messageType = 'error';
    } else {
        try {
            // Check if email already exists
            $checkEmail = $pdo->prepare("SELECT id FROM customer WHERE email = ?");
            $checkEmail->execute([$email]);
            
            if ($checkEmail->rowCount() > 0) {
                $message = 'البريد الإلكتروني مستخدم بالفعل';
                $messageType = 'error';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new customer
                $stmt = $pdo->prepare("INSERT INTO customer (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
                
                // IMPORTANT: Pass the actual values to execute()
                if ($stmt->execute([$firstName, $lastName, $email, $hashedPassword])) {
                    // Registration successful: Redirect to login page immediately
                    header('Location: login.php');
                    exit; // Terminate script after redirect
                } else {
                    // Registration failed (stmt->execute() returned false, but no PDOException)
                    $message = 'حدث خطأ أثناء إنشاء الحساب. فشل التنفيذ.';
                    $messageType = 'error';
                }
            }
        } catch (PDOException $e) {
            // Log error for developer: error_log('PDOException in SignUpClient: ' . $e->getMessage());
            $message = 'حدث خطأ أثناء إنشاء الحساب. مشكلة في قاعدة البيانات.';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فتح حساب كعميل</title>
    <link rel="stylesheet" href="assest/CSS/SignUpClient.css">
    <link rel="stylesheet" href="../Includes/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
        <h1>فتح حساب جديد</h1>
    </div>
    <div class="form-content">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="firstName">اسم الأول</label>
                    <input type="text" id="firstName" name="firstName" placeholder="ادخل اسمك الأول" value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastName">اسم الأخير</label>
                    <input type="text" id="lastName" name="lastName" placeholder="ادخل اسمك الأخير" value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="ادخل بريدك الإلكتروني" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
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

</body>
</html>