<?php
require_once __DIR__ . '/../../Includes/session_config.php';
require_once '../../backend/db.php';


// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    
    header('Location: ../login.php');
    exit();
}







$admin_id = $_SESSION['user_id'];

// Fetch admin name
$admin_name = '';
$name_query = "SELECT first_name, last_name FROM admin WHERE id = ?";
$name_stmt = $pdo->prepare($name_query);
$name_stmt->execute([$admin_id]);
if ($row = $name_stmt->fetch()) {
    $admin_name = $row['first_name'] . ' ' . $row['last_name'];
}

// Fetch comprehensive admin statistics
try {
    // Total Users (customers)
    $users_query = "SELECT COUNT(*) as total_users FROM customer";
    $users_stmt = $pdo->prepare($users_query);
    $users_stmt->execute();
    $total_users = $users_stmt->fetch()['total_users'];

    // Total Sellers
    $sellers_query = "SELECT COUNT(*) as total_sellers FROM seller";
    $sellers_stmt = $pdo->prepare($sellers_query);
    $sellers_stmt->execute();
    $total_sellers = $sellers_stmt->fetch()['total_sellers'];

    // Total Products
    $products_query = "SELECT COUNT(*) as total_products FROM product";
    $products_stmt = $pdo->prepare($products_query);
    $products_stmt->execute();
    $total_products = $products_stmt->fetch()['total_products'];

    // Total Orders
    $orders_query = "SELECT COUNT(*) as total_orders FROM customer_order";
    $orders_stmt = $pdo->prepare($orders_query);
    $orders_stmt->execute();
    $total_orders = $orders_stmt->fetch()['total_orders'];

    // Pending Product Approvals
    $pending_products_query = "SELECT COUNT(*) as pending_products FROM product WHERE is_approved = FALSE";
    $pending_products_stmt = $pdo->prepare($pending_products_query);
    $pending_products_stmt->execute();
    $pending_products = $pending_products_stmt->fetch()['pending_products'];

    // New Sellers (last 30 days)
    $new_sellers_query = "SELECT COUNT(*) as new_sellers FROM seller WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $new_sellers_stmt = $pdo->prepare($new_sellers_query);
    $new_sellers_stmt->execute();
    $new_sellers = $new_sellers_stmt->fetch()['new_sellers'];

    // Total Sales (completed orders)
    $sales_query = "SELECT COALESCE(SUM(ol.total), 0) as total_sales FROM order_link ol JOIN customer_order co ON ol.order_id = co.id WHERE co.status = 'تم التوصيل'";
    $sales_stmt = $pdo->prepare($sales_query);
    $sales_stmt->execute();
    $total_sales = $sales_stmt->fetch()['total_sales'];

    // Complaints/Reports (using a reports table - we'll create this)
    $reports_query = "SELECT COUNT(*) as total_reports FROM reports WHERE status = 'pending'";
    try {
        $reports_stmt = $pdo->prepare($reports_query);
        $reports_stmt->execute();
        $total_reports = $reports_stmt->fetch()['total_reports'];
    } catch (PDOException $e) {
        // If reports table doesn't exist, set to 0
        $total_reports = 0;
    }

    // Recent Activities
    $recent_orders_query = "SELECT co.id, co.order_date, co.status, co.created_at, c.first_name, c.last_name 
                           FROM customer_order co 
                           JOIN customer c ON co.customer_id = c.id 
                           ORDER BY co.created_at DESC LIMIT 5";
    $recent_orders_stmt = $pdo->prepare($recent_orders_query);
    $recent_orders_stmt->execute();
    $recent_orders = $recent_orders_stmt->fetchAll();

    // Recent Products
    $recent_products_query = "SELECT p.id, p.name, p.price, p.is_approved, p.created_at, s.store_name 
                             FROM product p 
                             JOIN seller s ON p.seller_id = s.id 
                             ORDER BY p.created_at DESC LIMIT 5";
    $recent_products_stmt = $pdo->prepare($recent_products_query);
    $recent_products_stmt->execute();
    $recent_products = $recent_products_stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $total_users = $total_sellers = $total_products = $total_orders = $pending_products = $new_sellers = $total_sales = $total_reports = 0;
    $recent_orders = $recent_products = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الإدارة - زين بلدي</title>
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

        .dashboard-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-left: 20px;
        }

        .stat-card.users .stat-icon {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .stat-card.sellers .stat-icon {
            background: linear-gradient(135deg, #e67e22, #d35400);
            color: white;
        }

        .stat-card.products .stat-icon {
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
        }

        .stat-card.orders .stat-icon {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
        }

        .stat-card.sales .stat-icon {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }

        .stat-card.pending .stat-icon {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .stat-card.reports .stat-icon {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
        }

        .stat-card.new-sellers .stat-icon {
            background: linear-gradient(135deg, #1abc9c, #16a085);
            color: white;
        }

        .stat-info h3 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .action-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .action-btn i {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .recent-activities {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .activity-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .activity-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-info {
            flex: 1;
        }

        .activity-info h4 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .activity-info p {
            color: #7f8c8d;
            font-size: 12px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
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

            .recent-activities {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../../Includes/logo.png" alt="ZenBladi Logo" style="height:60px; display:block; margin:auto;">
                <p>لوحة تحكم الإدارة</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin.php" class="active"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="manage-users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
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
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>مرحباً <?= htmlspecialchars($admin_name) ?>!</h1>
                <p>راقب وأدر منصتك بتحكم كامل</p>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="manage-products.php?filter=pending" class="action-btn">
                    <i class="fas fa-clock"></i>
                    الموافقة على المنتجات
                </a>
                <a href="manage-sellers.php?filter=new" class="action-btn">
                    <i class="fas fa-user-check"></i>
                    مراجعة البائعين
                </a>
                <a href="orders-panel.php" class="action-btn">
                    <i class="fas fa-shopping-cart"></i>
                    مراقبة الطلبات
                </a>
                <a href="reports.php" class="action-btn">
                    <i class="fas fa-flag"></i>
                    معالجة التقارير
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card users">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($total_users) ?></h3>
                        <p>إجمالي المستخدمين</p>
                    </div>
                </div>
                <div class="stat-card sellers">
                    <div class="stat-icon"><i class="fas fa-store"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($total_sellers) ?></h3>
                        <p>إجمالي البائعين</p>
                    </div>
                </div>
                <div class="stat-card products">
                    <div class="stat-icon"><i class="fas fa-box"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($total_products) ?></h3>
                        <p>إجمالي المنتجات</p>
                    </div>
                </div>
                <div class="stat-card orders">
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($total_orders) ?></h3>
                        <p>إجمالي الطلبات</p>
                    </div>
                </div>
                <div class="stat-card sales">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($total_sales, 2) ?> DH</h3>
                        <p>إجمالي المبيعات</p>
                    </div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($pending_products) ?></h3>
                        <p>منتجات في انتظار الموافقة</p>
                    </div>
                </div>
                <div class="stat-card new-sellers">
                    <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($new_sellers) ?></h3>
                        <p>بائعين جدد (30 يوم)</p>
                    </div>
                </div>
                <div class="stat-card reports">
                    <div class="stat-icon"><i class="fas fa-flag"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($total_reports) ?></h3>
                        <p>تقارير معلقة</p>
                    </div>
                </div>
            </div>

          
            <!-- Recent Activities -->
            <div class="recent-activities">
                <div class="activity-section">
                    <h3>أحدث الطلبات</h3>
                    <?php if (empty($recent_orders)): ?>
                        <p>لا توجد طلبات حديثة</p>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="activity-item">
                                <div class="activity-info">
                                    <h4>طلب #<?= $order['id'] ?></h4>
                                    <p><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?> - <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></p>
                                </div>
                                <span class="status-badge status-<?= $order['status'] == 'تم التوصيل' ? 'delivered' : 'pending' ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="activity-section">
                    <h3>أحدث المنتجات</h3>
                    <?php if (empty($recent_products)): ?>
                        <p>لا توجد منتجات حديثة</p>
                    <?php else: ?>
                        <?php foreach ($recent_products as $product): ?>
                            <div class="activity-item">
                                <div class="activity-info">
                                    <h4><?= htmlspecialchars($product['name']) ?></h4>
                                    <p><?= htmlspecialchars($product['store_name']) ?> - <?= number_format($product['price'], 2) ?>DH</p>
                                </div>
                                <span class="status-badge status-<?= $product['is_approved'] ? 'approved' : 'pending' ?>">
                                    <?= $product['is_approved'] ? 'مُوافق عليه' : 'في انتظار الموافقة' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>