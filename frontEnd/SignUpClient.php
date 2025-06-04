<?php
session_start();
require_once '../BackEnd/db.php';

// نتحققو إذا جاينا من redirect بحالة التسجيل الناجح
$registrationSuccess = isset($_GET['registered']) && $_GET['registered'] == 1;

// نيجيبو الرسالة والـ type من SESSION إذا كانو وحطّهم فنقط محليين (لأخطاء الـ validation)
$message     = isset($_SESSION['signup_error'])      ? $_SESSION['signup_error']      : '';
$messageType = isset($_SESSION['signup_error_type']) ? $_SESSION['signup_error_type'] : '';
// نيجيبو القيم القديمة للحقول من SESSION إذا كانت
$oldInputs = isset($_SESSION['old_inputs']) ? $_SESSION['old_inputs'] : [
    'firstName' => '',
    'lastName'  => '',
    'email'     => '',
];
// نحيدوهم من SESSION باش ما يبقاوش للمرة الجاية
unset($_SESSION['signup_error'], $_SESSION['signup_error_type'], $_SESSION['old_inputs']);

// نعمروا المتغيّرات ديال الفورم بالقيم القديمة (إلا كان فيها)
$firstName = $oldInputs['firstName'];
$lastName  = $oldInputs['lastName'];
$email     = $oldInputs['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // إذا البرنامج جا من POST نديروا الـ validation والإدخال للقاعدة
    $firstName = trim($_POST['firstName']);
    $lastName  = trim($_POST['lastName']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];

    // validation ديال الحقول
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $_SESSION['signup_error']      = 'جميع الحقول مطلوبة';
        $_SESSION['signup_error_type'] = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error']      = 'البريد الإلكتروني غير صحيح';
        $_SESSION['signup_error_type'] = 'error';
    } elseif (strlen($password) < 6) {
        $_SESSION['signup_error']      = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        $_SESSION['signup_error_type'] = 'error';
    } else {
        try {
            // نتأكدوا من عدم وجود الإيميل من قبل
            $checkEmail = $pdo->prepare("SELECT id FROM customer WHERE email = ?");
            $checkEmail->execute([$email]);

            if ($checkEmail->rowCount() > 0) {
                $_SESSION['signup_error']      = 'البريد الإلكتروني مستخدم بالفعل';
                $_SESSION['signup_error_type'] = 'error';
            } else {
                // نهشّو كلمة المرور
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO customer (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");

                if ($stmt->execute([$firstName, $lastName, $email, $hashedPassword])) {
                    // إذا نجح التسجيل، Redirect لذات الصفحة مع علامة ?registered=1
                    header('Location: SignUpClient.php?registered=1');
                    exit;
                } else {
                    $_SESSION['signup_error']      = 'حدث خطأ أثناء إنشاء الحساب. فشل التنفيذ.';
                    $_SESSION['signup_error_type'] = 'error';
                }
            }
        } catch (PDOException $e) {
            // يمكن تسجيل الأخطاء فـ logfile لو بغينا
            $_SESSION['signup_error']      = 'حدث خطأ أثناء إنشاء الحساب. مشكلة في قاعدة البيانات.';
            $_SESSION['signup_error_type'] = 'error';
        }
    }

    // نخزّنو القيم القديمة من الحقول فالـ session باش نردّوها للفورم (إلا كان خطأ)
    $_SESSION['old_inputs'] = [
        'firstName' => $firstName,
        'lastName'  => $lastName,
        'email'     => $email,
    ];

    // Redirect لنفس الصفحة (GET) باش نعرضو الأخطاء
    header('Location: SignUpClient.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" >
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
            opacity: 1;
            transition: opacity 0.5s;
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

<?php include __DIR__ . '/../Includes/header.php'; ?>

<div class="form-container">
    <div class="form-header">
        <h1>فتح حساب جديد</h1>
    </div>
    <div class="form-content">
        <?php if ($registrationSuccess): ?>
            <!-- رسالة النجاح قبل التحويل للـ login.php -->
            <div class="message success" id="success-message">
                تم التسجيل بنجاح! سوف يتم تحويلك إلى صفحة تسجيل الدخول خلال ثوانٍ قليلة...
            </div>
        <?php elseif ($message): ?>
            <!-- رسالة الخطأ -->
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!$registrationSuccess): ?>
        <form action="" method="post" id="signup-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="firstName">الاسم الأول</label>
                    <input
                        type="text"
                        id="firstName"
                        name="firstName"
                        placeholder="أدخل اسمك الأول"
                        value="<?php echo htmlspecialchars($firstName); ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="lastName">الاسم الأخير</label>
                    <input
                        type="text"
                        id="lastName"
                        name="lastName"
                        placeholder="أدخل اسمك الأخير"
                        value="<?php echo htmlspecialchars($lastName); ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="أدخل بريدك الإلكتروني"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="أدخل كلمة المرور"
                        required
                    >
                </div>
            </div>
            <button type="submit" class="submit-btn">تسجيل</button>
            <p class="login-link" id="login-link-client">
                لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
            </p>
            <p class="login-link" id="signup-seller-link">
                هل أنت بائع؟ <a href="SignUpSeller.php">افتح حساب</a>
            </p>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // إذا كانت هناك رسالة خطأ، نخليها تختفي بعد 5 ثواني
    document.addEventListener('DOMContentLoaded', function() {
        const errMsg = document.querySelector('.message.error');
        if (errMsg) {
            setTimeout(() => {
                errMsg.style.opacity = '0';
                setTimeout(() => errMsg.remove(), 500);
            }, 5000);
        }
    });

    // إذا كانت حالة التسجيل ناجحة، نوجّهو المستخدم بعد 5 ثواني للـ login.php
    <?php if ($registrationSuccess): ?>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 5000);
    });
    <?php endif; ?>
</script>
<script src="assest/JS/mainNavigation.js"></script> <!-- Or your combined navigation.js -->

</body>
</html>
