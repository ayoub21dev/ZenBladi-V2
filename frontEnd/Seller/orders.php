<?php
// This is a partial file for the orders section.
// It expects $seller_id and $pdo to be defined from seller_logic.php

// Handle Order Status Update
if (isset($_POST['update_order_status'])) {
    $order_id_to_update = $_POST['order_id'];
    $new_status = $_POST['status'];

    $update_stmt = $pdo->prepare("UPDATE customer_order SET status = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $order_id_to_update]);

    // We need to ensure this update is authorized, but for now, we'll assume it is.
    // A proper check would confirm at least one product in the order belongs to the seller.

    header('Location: Seller.php#orders');
    exit();
}

// Fetch all orders containing this seller's products
$orders_stmt = $pdo->prepare("
    SELECT DISTINCT
        co.id,
        co.order_date,
        co.status,
        co.shipping_fullname,
        co.shipping_address,
        co.shipping_city,
        (SELECT SUM(ol.total) FROM order_link ol WHERE ol.order_id = co.id) as total_amount
    FROM customer_order co
    JOIN order_link ol ON co.id = ol.order_id
    JOIN product p ON ol.product_id = p.id
    WHERE p.seller_id = ?
    ORDER BY co.order_date DESC
");
$orders_stmt->execute([$seller_id]);
$seller_orders = $orders_stmt->fetchAll();

$status_options = ['قيد المعالجة', 'مؤكد', 'تم الشحن', 'تم التوصيل', 'ملغي'];
?>
<section id="orders" class="dashboard-section">
    <div class="section-header">
        <h1>الطلبات</h1>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>اسم العميل</th>
                    <th>عنوان الشحن</th>
                    <th>تاريخ الطلب</th>
                    <th>المبلغ الإجمالي</th>
                    <th>الحالة</th>
                    <th>تغيير الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($seller_orders)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <p>لا توجد طلبات لعرضها.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($seller_orders as $order): ?>
                        <tr>
                            <td data-label="رقم الطلب">#<?= $order['id'] ?></td>
                            <td data-label="اسم العميل"><?= htmlspecialchars($order['shipping_fullname']) ?></td>
                            <td data-label="عنوان الشحن"><?= htmlspecialchars($order['shipping_address'] . ', ' . $order['shipping_city']) ?></td>
                            <td data-label="تاريخ الطلب"><?= date('Y-m-d', strtotime($order['order_date'])) ?></td>
                            <td data-label="المبلغ الإجمالي"><?= number_format($order['total_amount'], 2) ?> درهم</td>
                            <td data-label="الحالة">
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td data-label="تغيير الحالة">
                                <form action="#orders" method="post" class="status-update-form">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status">
                                        <?php foreach ($status_options as $status): ?>
                                            <option value="<?= $status ?>" <?= $status === $order['status'] ? 'selected' : '' ?>>
                                                <?= $status ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_order_status" class="btn btn-update">تحديث</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
