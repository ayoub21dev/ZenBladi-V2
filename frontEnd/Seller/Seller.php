<?php
session_start();
require_once '../../backend/db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header('Location: ../login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];

// Fetch seller statistics
try {
    // Total Sales
    $sales_query = "SELECT COALESCE(SUM(total_amount), 0) as total_sales FROM orders WHERE seller_id = ? AND status = 'completed'";
    $sales_stmt = $pdo->prepare($sales_query);
    $sales_stmt->execute([$seller_id]);
    $total_sales = $sales_stmt->fetch()['total_sales'];

    // Orders Pending Approval
    $pending_query = "SELECT COUNT(*) as pending_orders FROM orders WHERE seller_id = ? AND status = 'pending'";
    $pending_stmt = $pdo->prepare($pending_query);
    $pending_stmt->execute([$seller_id]);
    $pending_orders = $pending_stmt->fetch()['pending_orders'];

    // Total Orders
    $orders_query = "SELECT COUNT(*) as total_orders FROM orders WHERE seller_id = ?";
    $orders_stmt = $pdo->prepare($orders_query);
    $orders_stmt->execute([$seller_id]);
    $total_orders = $orders_stmt->fetch()['total_orders'];

    // Total Products
    $products_query = "SELECT COUNT(*) as total_products FROM product WHERE seller_id = ?";
    $products_stmt = $pdo->prepare($products_query);
    $products_stmt->execute([$seller_id]);
    $total_products = $products_stmt->fetch()['total_products'];

    // Latest Orders (last 5)
    $latest_orders_query = "SELECT o.id, o.total_amount, o.status, o.created_at, u.name as customer_name 
                           FROM orders o 
                           JOIN users u ON o.user_id = u.id 
                           WHERE o.seller_id = ? 
                           ORDER BY o.created_at DESC 
                           LIMIT 5";
    $latest_orders_stmt = $pdo->prepare($latest_orders_query);
    $latest_orders_stmt->execute([$seller_id]);
    $latest_orders = $latest_orders_stmt->fetchAll();

    // Latest Products (last 5)
    $latest_products_query = "SELECT id, name, price, image, created_at 
                             FROM product 
                             WHERE seller_id = ? 
                             ORDER BY created_at DESC 
                             LIMIT 5";
    $latest_products_stmt = $pdo->prepare($latest_products_query);
    $latest_products_stmt->execute([$seller_id]);
    $latest_products = $latest_products_stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $total_sales = $pending_orders = $total_orders = $total_products = 0;
    $latest_orders = $latest_products = [];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم البائع - زين بلدي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assest/CSS/Seller.css">
  
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>زين بلدي</h2>
                <p>لوحة تحكم البائع</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="Seller.php" class="active"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> منتجاتي</a></li>
                <li><a href="add-product.php"><i class="fas fa-plus"></i> إضافة منتج جديد</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> الطلبات</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> الملف الشخصي</a></li>
                <li><a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>مرحباً بك في لوحة التحكم</h1>
                <p>إدارة متجرك ومنتجاتك بسهولة</p>
            </div>

            <!-- Add Product Button -->
            <a href="add-product.php" class="add-product-btn">
                <i class="fas fa-plus"></i>
                إضافة منتج جديد
            </a>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card sales">
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                    <h3><?= number_format($total_sales, 2) ?></h3>
                    <p>إجمالي المبيعات</p>
                </div>
                <div class="stat-card pending">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <h3><?= $pending_orders ?></h3>
                    <p>طلبات بانتظار الموافقة</p>
                </div>
                <div class="stat-card orders">
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    <h3><?= $total_orders ?></h3>
                    <p>إجمالي الطلبات</p>
                </div>
                <div class="stat-card products">
                    <div class="icon"><i class="fas fa-box"></i></div>
                    <h3><?= $total_products ?></h3>
                    <p>إجمالي المنتجات</p>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="content-grid">
                <!-- Latest Orders -->
                <div class="content-section">
                    <div class="section-header">
                        <h2>أحدث الطلبات</h2>
                    </div>
                    <div class="section-content">
                        <?php if (empty($latest_orders)): ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <p>لا توجد طلبات حتى الآن</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($latest_orders as $order): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <h4>طلب #<?= $order['id'] ?></h4>
                                        <p>العميل: <?= htmlspecialchars($order['customer_name']) ?></p>
                                        <p><?= date('Y-m-d', strtotime($order['created_at'])) ?></p>
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

                <!-- Latest Products -->
                <div class="content-section">
                    <div class="section-header">
                        <h2>أحدث المنتجات</h2>
                    </div>
                    <div class="section-content">
                        <?php if (empty($latest_products)): ?>
                            <div class="empty-state">
                                <i class="fas fa-box"></i>
                                <p>لا توجد منتجات حتى الآن</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($latest_products as $product): ?>
                                <div class="product-item">
                                    <div class="product-info">
                                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                                        <p><?= number_format($product['price'], 2) ?> درهم</p>
                                        <p><?= date('Y-m-d', strtotime($product['created_at'])) ?></p>
                                    </div>
                                    <?php if ($product['image']): ?>
                                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                                    <?php endif; ?>
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
        });
    </script>
</body>
</html>