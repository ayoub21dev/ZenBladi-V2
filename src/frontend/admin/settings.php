<?php
require_once __DIR__ . '/../../Includes/session_config.php';
require_once '../../backend/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Example: Change admin password (you can add more settings as needed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $admin_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT password FROM admin WHERE id = ?');
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($old_password, $admin['password'])) {
        $message = 'كلمة المرور القديمة غير صحيحة';
        $messageType = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'كلمتا المرور غير متطابقتين';
        $messageType = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف أو أكثر';
        $messageType = 'error';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $pdo->prepare('UPDATE admin SET password = ? WHERE id = ?');
        if ($update->execute([$hashed, $admin_id])) {
            $message = 'تم تغيير كلمة المرور بنجاح';
            $messageType = 'success';
        } else {
            $message = 'حدث خطأ أثناء تغيير كلمة المرور';
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
    <title>إعدادات المنصة - زين بلدي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; direction: rtl; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: linear-gradient(135deg, #2c3e50, #34495e); color: white; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; }
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { font-size: 24px; margin-bottom: 5px; color: #3498db; }
        .sidebar-menu { list-style: none; padding: 20px 0; }
        .sidebar-menu li { margin: 5px 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 15px 25px; color: #ecf0f1; text-decoration: none; transition: all 0.3s ease; border-right: 3px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: rgba(52, 152, 219, 0.2); border-right-color: #3498db; color: white; }
        .sidebar-menu a i { margin-left: 15px; width: 20px; text-align: center; }
        .logout-btn { color: #e74c3c !important; }
        .logout-btn:hover { background-color: rgba(231, 76, 60, 0.2) !important; border-right-color: #e74c3c !important; }
        .main-content { margin-right: 280px; padding: 30px; width: calc(100% - 280px); }
        .page-header { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .page-header h1 { color: #2c3e50; font-size: 28px; margin-bottom: 10px; }
        .settings-section { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
        .settings-section h2 { color: #2c3e50; margin-bottom: 20px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 7px; font-weight: bold; color: #2c3e50; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; background: #f8f9fa; }
        .btn { padding: 10px 22px; border: none; border-radius: 6px; cursor: pointer; background: linear-gradient(90deg, #3498db 80%, #2980b9 100%); color: white; font-weight: bold; font-size: 1rem; transition: background 0.2s; }
        .btn:hover { background: linear-gradient(90deg, #2980b9 80%, #3498db 100%); }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: bold; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 900px) { .main-content { margin-right: 0; padding: 10px; } }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <a href="../index.php" style="display:inline-block;">
                    <img src="../../Includes/logo.png" alt="ZenBladi Logo" style="height:56px;max-width:100%;display:block;margin:0 auto 5px auto;">
                </a>
                <p>لوحة تحكم الإدارة</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="manage-users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                <li><a href="manage-sellers.php"><i class="fas fa-store"></i> إدارة البائعين</a></li>
                <li><a href="manage-products.php"><i class="fas fa-box"></i> إدارة المنتجات</a></li>
                <li><a href="orders-panel.php"><i class="fas fa-shopping-cart"></i> لوحة الطلبات</a></li>
                <li><a href="reports.php"><i class="fas fa-flag"></i> التقارير والشكاوى</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> إعدادات المنصة</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> التحليلات</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="page-header">
                <h1>إعدادات المنصة</h1>
                <p>تغيير كلمة مرور المسؤول</p>
            </div>
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <div class="settings-section">
                <h2>تغيير كلمة المرور</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>كلمة المرور القديمة</label>
                        <input type="password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور الجديدة</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>تأكيد كلمة المرور الجديدة</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn">تغيير كلمة المرور</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
