<?php
session_start();
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

// Fetch admin statistics
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

    // Pending Seller Approvals (assuming there's an approval status)
    $pending_sellers_query = "SELECT COUNT(*) as pending_sellers FROM seller WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $pending_sellers_stmt = $pdo->prepare($pending_sellers_query);
    $pending_sellers_stmt->execute();
    $pending_sellers = $pending_sellers_stmt->fetch()['pending_sellers'];

    // Total Sales
    $sales_query = "SELECT COALESCE(SUM(total_amount), 0) as total_sales FROM customer_order WHERE status = 'completed'";
    $sales_stmt = $pdo->prepare($sales_query);
    $sales_stmt->execute();
    $total_sales = $sales_stmt->fetch()['total_sales'];

    // Recent Activities
    $recent_orders_query = "SELECT co.id, co.total_amount, co.status, co.created_at, c.first_name, c.last_name 
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
    $total_users = $total_sellers = $total_products = $total_orders = $pending_products = $pending_sellers = $total_sales = 0;
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
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
            direction: rtl;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: #ecf0f1;
        }

        .sidebar-header p {
            opacity: 0.8;
            font-size: 0.9rem;
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
            background: rgba(255,255,255,0.1);
            border-right-color: #3498db;
            transform: translateX(-5px);
        }

        .sidebar-menu a i {
            margin-left: 12px;
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important;
            margin: 20px 15px;
            border-radius: 8px;
            border-right: none !important;
        }

        .logout-btn:hover {
            transform: translateX(0) !important;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-right: 280px;
            padding: 30px;
            background: #f8f9fa;
        }

        .dashboard-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            font-size: 2.2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .dashboard-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-right: 5px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .stat-card.users { border-right-color: #3498db; }
        .stat-card.sellers { border-right-color: #2ecc71; }
        .stat-card.products { border-right-color: #f39c12; }
        .stat-card.orders { border-right-color: #9b59b6; }
        .stat-card.pending-products { border-right-color: #e67e22; }
        .stat-card.pending-sellers { border-right-color: #1abc9c; }
        .stat-card.sales { border-right-color: #27ae60; }

        .stat-icon {
            font-size: 3rem;
            margin-left: 20px;
            opacity: 0.8;
        }

        .stat-card.users .stat-icon { color: #3498db; }
        .stat-card.sellers .stat-icon { color: #2ecc71; }
        .stat-card.products .stat-icon { color: #f39c12; }
        .stat-card.orders .stat-icon { color: #9b59b6; }
        .stat-card.pending-products .stat-icon { color: #e67e22; }
        .stat-card.pending-sellers .stat-icon { color: #1abc9c; }
        .stat-card.sales .stat-icon { color: #27ae60; }

        .stat-info h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .stat-info p {
            color: #7f8c8d;
            font-size: 1rem;
            font-weight: 500;
        }

        /* Content Sections */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .content-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            padding: 20px 30px;
        }

        .section-header h2 {
            font-size: 1.3rem;
            margin: 0;
        }

        .section-content {
            padding: 25px;
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-info h4 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .activity-info p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-unapproved {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: white;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            border-color: #3498db;
            background: #3498db;
            color: white;
            transform: translateY(-2px);
        }

        .action-btn i {
            margin-left: 10px;
            font-size: 1.2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-right: 0;
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-header h1 {
                font-size: 1.8rem;
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
                <li><a href="admin.php" class="active"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="manage-users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                <li><a href="manage-sellers.php"><i class="fas fa-store"></i> إدارة البائعين</a></li>
                <li><a href="manage-products.php"><i class="fas fa-box"></i> إدارة المنتجات</a></li>
                <li><a href="orders-panel.php"><i class="fas fa-shopping-cart"></i> لوحة الطلبات</a></li>
                <li><a href="reports.php"><i class="fas fa-flag"></i> التقارير والشكاوى</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
                <li><a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>مرحباً بك، <?= htmlspecialchars($admin_name) ?>!</h1>
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
                <div class="stat-card pending-products">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($pending_products) ?></h3>
                        <p>منتجات بانتظار الموافقة</p>
                    </div>
                </div>
                <div class="stat-card pending-sellers">
                    <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($pending_sellers) ?></h3>
                        <p>بائعين جدد (30 يوم)</p>
                    </div>
                </div>
                <div class="stat-card sales">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($total_sales, 2) ?> درهم</h3>
                        <p>إجمالي المبيعات</p>
                    </div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="content-grid">
                <!-- Recent Orders -->
                <div class="content-section">
                    <div class="section-header">
                        <h2>أحدث الطلبات</h2>
                    </div>
                    <div class="section-content">
                        <?php if (empty($recent_orders)): ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <p>لا توجد طلبات حديثة</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <h4>طلب #<?= $order['id'] ?></h4>
                                        <p>العميل: <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
                                        <p><?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                                    </div>
                                    <div>
                                        <p style="font-weight: bold; margin-bottom: 5px;"><?= number_format($order['total_amount'], 2) ?> درهم</p>
                                        <span class="status-badge status-<?= $order['status'] ?>">
                                            <?php
                                            $status_text = [
                                                'pending' => 'في الانتظار',
                                                'completed' => 'مكتمل',
                                                'cancelled' => 'ملغي'
                                            ];
                                            echo $status_text[$order['status']] ?? $order['status'];
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="content-section">
                    <div class="section-header">
                        <h2>أحدث المنتجات</h2>
                    </div>
                    <div class="section-content">
                        <?php if (empty($recent_products)): ?>
                            <div class="empty-state">
                                <i class="fas fa-box"></i>
                                <p>لا توجد منتجات حديثة</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_products as $product): ?>
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                                        <p>المتجر: <?= htmlspecialchars($product['store_name']) ?></p>
                                        <p><?= date('M d, Y', strtotime($product['created_at'])) ?></p>
                                    </div>
                                    <div>
                                        <p style="font-weight: bold; margin-bottom: 5px;"><?= number_format($product['price'], 2) ?> درهم</p>
                                        <span class="status-badge status-<?= $product['is_approved'] ? 'approved' : 'unapproved' ?>">
                                            <?= $product['is_approved'] ? 'موافق عليه' : 'في الانتظار' ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });

            // Add click effects to action buttons
            const actionBtns = document.querySelectorAll('.action-btn');
            actionBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-2px)';
                    }, 150);
                });
            });
        });
    </script>
</body>
</html>