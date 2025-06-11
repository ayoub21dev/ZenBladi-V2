<?php
session_start();
require_once '../../backend/db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Handle seller actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_seller'])) {
        $seller_id = $_POST['seller_id'];
        try {
            // Delete seller's products and related data
            $delete_order_links = $pdo->prepare("DELETE ol FROM order_link ol JOIN product p ON ol.product_id = p.id WHERE p.seller_id = ?");
            $delete_order_links->execute([$seller_id]);
            
            $delete_products = $pdo->prepare("DELETE FROM product WHERE seller_id = ?");
            $delete_products->execute([$seller_id]);
            
            $delete_seller = $pdo->prepare("DELETE FROM seller WHERE id = ?");
            if ($delete_seller->execute([$seller_id])) {
                $message = 'تم حذف البائع ومنتجاته بنجاح.';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'خطأ في حذف البائع: ' . $e->getMessage();
            $messageType = 'error';
        }
    } 
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$city_filter = $_GET['city'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR store_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($city_filter)) {
    $where_conditions[] = "city = ?";
    $params[] = $city_filter;
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get sellers with statistics
$sellers_query = "SELECT s.*, 
                 COUNT(DISTINCT p.id) as total_products,
                 COUNT(DISTINCT CASE WHEN p.is_approved = 1 THEN p.id END) as approved_products,
                 COUNT(DISTINCT ol.order_id) as total_orders,
                 COALESCE(SUM(ol.total), 0) as total_sales
                 FROM seller s 
                 LEFT JOIN product p ON s.id = p.seller_id 
                 LEFT JOIN order_link ol ON p.id = ol.product_id
                 LEFT JOIN customer_order co ON ol.order_id = co.id AND co.status = 'تم التوصيل'
                 $where_clause
                 GROUP BY s.id 
                 ORDER BY s.created_at DESC";

try {
    $sellers_stmt = $pdo->prepare($sellers_query);
    $sellers_stmt->execute($params);
    $sellers = $sellers_stmt->fetchAll();
} catch (PDOException $e) {
    $sellers = [];
    $message = 'خطأ في جلب بيانات البائعين: ' . $e->getMessage();
    $messageType = 'error';
}

// Get cities for filter
try {
    $cities_stmt = $pdo->prepare("SELECT DISTINCT city FROM seller WHERE city IS NOT NULL AND city != '' ORDER BY city");
    $cities_stmt->execute();
    $cities = $cities_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $cities = [];
}

// Get statistics
try {
    $total_sellers_stmt = $pdo->prepare("SELECT COUNT(*) FROM seller");
    $total_sellers_stmt->execute();
    $total_sellers = $total_sellers_stmt->fetchColumn();
} catch (PDOException $e) {
    $total_sellers = 0;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة البائعين - زين بلدي</title>
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
        .stat-card.pending h3 { color: #f39c12; }
        .stat-card.suspended h3 { color: #e74c3c; }

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

        .sellers-table {
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

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-suspended {
            background: #f8d7da;
            color: #721c24;
        }

        .actions {
            display: flex;
            gap: 5px;
            justify-content: center;
            flex-wrap: wrap;
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

        .seller-details {
            font-size: 12px;
            color: #666;
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
                <li><a href="manage-users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                <li><a href="manage-sellers.php" class="active"><i class="fas fa-store"></i> إدارة البائعين</a></li>
                <li><a href="manage-products.php"><i class="fas fa-box"></i> إدارة المنتجات</a></li>
                <li><a href="orders-panel.php"><i class="fas fa-shopping-cart"></i> لوحة الطلبات</a></li>
                <li><a href="reports.php"><i class="fas fa-flag"></i> التقارير والشكاوى</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> إعدادات المنصة</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> التحليلات</a></li>
                <li><a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>إدارة البائعين</h1>
                <p>عرض وإدارة جميع البائعين في المنصة</p>
            </div>

            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-row">
                <div class="stat-card total">
                    <h3><?= number_format($total_sellers) ?></h3>
                    <p>إجمالي البائعين</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET">
                    <div class="filters-grid">
                        <div class="form-group">
                            <label>البحث</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="اسم أو بريد إلكتروني أو اسم المتجر">
                        </div>
                        <div class="form-group">
                            <label>المدينة</label>
                            <select name="city">
                                <option value="">جميع المدن</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= htmlspecialchars($city) ?>" <?= $city_filter === $city ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($city) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>من تاريخ</label>
                            <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                        </div>
                        <div class="form-group">
                            <label>إلى تاريخ</label>
                            <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">تطبيق الفلاتر</button>
                    <a href="manage-sellers.php" class="btn btn-warning">إعادة تعيين</a>
                </form>
            </div>

            <!-- Sellers Table -->
            <div class="sellers-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>المعرف</th>
                            <th>معلومات البائع</th>
                            <th>اسم المتجر</th>
                            <th>المدينة</th>
                            <th>تاريخ التسجيل</th>
                            <th>المنتجات</th>
                            <th>الطلبات</th>
                            <th>المبيعات</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sellers)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 50px;">لا توجد بائعين</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sellers as $seller): ?>
                                <tr>
                                    <td><?= $seller['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']) ?></strong><br>
                                        <div class="seller-details">
                                            <?= htmlspecialchars($seller['email']) ?><br>
                                            <?= htmlspecialchars($seller['phone_number']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($seller['store_name']) ?></td>
                                    <td><?= htmlspecialchars($seller['city']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($seller['created_at'])) ?></td>
                                    <td>
                                        <?= number_format($seller['approved_products']) ?> / <?= number_format($seller['total_products']) ?>
                                        <div class="seller-details">مُوافق / إجمالي</div>
                                    </td>
                                    <td><?= number_format($seller['total_orders']) ?></td>
                                    <td><?= number_format($seller['total_sales'], 2) ?> ر.س</td>
                                    <td>
                                        <div class="actions">
                                            <a href="seller-details.php?id=<?= $seller['id'] ?>" class="btn btn-sm btn-primary">
                                                تفاصيل
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا البائع؟ سيتم حذف جميع منتجاته أيضاً.')">
                                                <input type="hidden" name="seller_id" value="<?= $seller['id'] ?>">
                                                <button type="submit" name="delete_seller" class="btn btn-sm btn-danger">
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