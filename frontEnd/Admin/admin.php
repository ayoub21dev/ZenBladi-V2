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
    <link rel="stylesheet" href="../assest/CSS/Admin/admin.css">
    
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