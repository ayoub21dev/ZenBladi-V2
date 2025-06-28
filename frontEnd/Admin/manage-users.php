<?php
require_once __DIR__ . '/../../Includes/session_config.php';
require_once '../../backend/db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        try {
            // Delete user orders first (foreign key constraint)
            $delete_orders = $pdo->prepare("DELETE FROM customer_order WHERE customer_id = ?");
            $delete_orders->execute([$user_id]);
            
            // Delete user
            $delete_user = $pdo->prepare("DELETE FROM customer WHERE id = ?");
            if ($delete_user->execute([$user_id])) {
                $message = 'تم حذف المستخدم بنجاح.';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'خطأ في حذف المستخدم: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['reset_password'])) {
        $user_id = $_POST['user_id'];
        $new_password = 'password123'; // Default password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        try {
            $reset_password = $pdo->prepare("UPDATE customer SET password = ? WHERE id = ?");
            if ($reset_password->execute([$hashed_password, $user_id])) {
                $message = 'تم إعادة تعيين كلمة المرور إلى: password123';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'خطأ في إعادة تعيين كلمة المرور.';
            $messageType = 'error';
        }
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}



$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get users with order count
$users_query = "SELECT c.*, 
                COUNT(co.id) as total_orders,
                COALESCE(SUM(ol.total), 0) as total_spent
                FROM customer c 
                LEFT JOIN customer_order co ON c.id = co.customer_id 
                LEFT JOIN order_link ol ON co.id = ol.order_id
                $where_clause
                GROUP BY c.id 
                ORDER BY c.created_at DESC";

try {
    $users_stmt = $pdo->prepare($users_query);
    $users_stmt->execute($params);
    $users = $users_stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $message = 'خطأ في جلب بيانات المستخدمين: ' . $e->getMessage();
    $messageType = 'error';
}

// Get statistics
try {
    $total_users_stmt = $pdo->prepare("SELECT COUNT(*) FROM customer");
    $total_users_stmt->execute();
    $total_users = $total_users_stmt->fetchColumn();
} catch (PDOException $e) {
    $total_users = 0;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين - زين بلدي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            direction: rtl;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #3498db;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s ease;
            border-right: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(52, 152, 219, 0.2);
            border-right-color: #3498db;
            color: white;
        }

        .sidebar-menu a i {
            margin-left: 15px;
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            color: #e74c3c !important;
        }

        .logout-btn:hover {
            background-color: rgba(231, 76, 60, 0.2) !important;
            border-right-color: #e74c3c !important;
        }

        .main-content {
            margin-right: 280px;
            padding: 30px;
            width: calc(100% - 280px);
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-card.total h3 { color: #3498db; }
        .stat-card.active h3 { color: #27ae60; }
        .stat-card.blocked h3 { color: #e74c3c; }

        .filters-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #ecf0f1;
        }

        .table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .actions {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-right: 0;
                width: 100%;
            }

            .table {
                font-size: 12px;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>زين بلدي</h2>
                <p>لوحة تحكم الإدارة</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="manage-users.php" class="active"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                <li><a href="manage-sellers.php"><i class="fas fa-store"></i> إدارة البائعين</a></li>
                <li><a href="manage-products.php"><i class="fas fa-box"></i> إدارة المنتجات</a></li>
                <li><a href="orders-panel.php"><i class="fas fa-shopping-cart"></i> لوحة الطلبات</a></li>
                <li><a href="reports.php"><i class="fas fa-flag"></i> التقارير والشكاوى</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> إعدادات المنصة</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> التحليلات</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>إدارة المستخدمين</h1>
                <p>عرض وإدارة جميع مستخدمي المنصة</p>
            </div>

            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-row">
                <div class="stat-card total">
                    <h3><?= number_format($total_users) ?></h3>
                    <p>إجمالي المستخدمين</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET">
                    <div class="filters-grid">
                        <div class="form-group">
                            <label>البحث</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="اسم أو بريد إلكتروني">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">تطبيق الفلاتر</button>
                    <a href="manage-users.php" class="btn btn-warning">إعادة تعيين</a>
                </form>
            </div>

            <!-- Users Table -->
            <div class="users-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>المعرف</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>تاريخ التسجيل</th>
                            <th>عدد الطلبات</th>
                            <th>إجمالي الإنفاق</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 50px;">لا توجد مستخدمين</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                    <td><?= number_format($user['total_orders']) ?></td>
                                    <td><?= number_format($user['total_spent'], 2) ?>DH</td>
                                    <td>
                                        <div class="actions">
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من إعادة تعيين كلمة المرور؟')">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" name="reset_password" class="btn btn-sm btn-primary">
                                                    إعادة تعيين كلمة المرور
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ سيتم حذف جميع طلباته أيضاً.')">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>