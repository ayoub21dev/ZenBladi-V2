<section id="dashboard" class="dashboard-section active-section">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1>مرحباً بك، <?= htmlspecialchars($seller_name) ?>!</h1>
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
            <h3><?= number_format($total_sales, 2) ?> درهم</h3>
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
                                <p>العميل: <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
                                <p><?= date('Y-m-d', strtotime($order['order_date'])) ?></p>
                            </div>
                            <div>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
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
                                <img src="../assest/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section> 