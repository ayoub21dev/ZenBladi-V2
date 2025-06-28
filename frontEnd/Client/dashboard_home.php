<!-- Dashboard Home Section -->
<section id="dashboard" class="dashboard-section active-section">
    <h1 class="welcome-message">مرحباً <?php echo htmlspecialchars($customerName); ?>، في لوحة تحكمك!</h1>
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-box-open"></i></div>
            <div class="stat-info">
                <p>إجمالي الطلبات</p>
                <span><?php echo $totalOrders; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shipping-fast"></i></div>
            <div class="stat-info">
                <p>طلبات قيد التنفيذ</p>
                <span><?php echo $inProgressOrders; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <p>طلبات تم توصيلها</p>
                <span><?php echo $deliveredOrders; ?></span>
            </div>
        </div>
    </div>

    <div class="orders-history">
        <h2>أحدث الطلبات</h2>
        <table>
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5">لا توجد طلبات حديثة.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td data-label="رقم الطلب">#<?php echo $order['id']; ?></td>
                            <td data-label="التاريخ"><?php echo $order['order_date']; ?></td>
                            <td data-label="الإجمالي"><?php echo htmlspecialchars(number_format(getOrderTotal($order['id'], $pdo), 2)); ?>DH</td>
                            <td data-label="الحالة"><span class="status <?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                            <td data-label="إجراء">
                                <button class="btn">عرض التفاصيل</button>
                                <button class="btn btn-secondary">إعادة الطلب</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section> 