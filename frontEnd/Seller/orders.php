<?php
// This is a partial file for the orders section.
// It expects $seller_id and $pdo to be defined from seller_logic.php

// Fetch all orders containing this seller's products
$orders_stmt = $pdo->prepare("
    SELECT DISTINCT
        co.id,
        co.order_date,
        co.status,
        co.shipping_fullname,
        co.shipping_address,
        co.shipping_city,
        co.shipping_phone, 
        (SELECT SUM(ol.total) FROM order_link ol WHERE ol.order_id = co.id) as total_amount
    FROM customer_order co
    JOIN order_link ol ON co.id = ol.order_id
    JOIN product p ON ol.product_id = p.id
    WHERE p.seller_id = ?
    ORDER BY co.order_date DESC
");
$orders_stmt->execute([$seller_id]);
$seller_orders = $orders_stmt->fetchAll();

// For each order, get the associated products
$orders_with_products = [];
foreach ($seller_orders as $order) {
    $products_stmt = $pdo->prepare("
        SELECT p.name, ol.quantity, ol.total
        FROM order_link ol
        JOIN product p ON ol.product_id = p.id
        WHERE ol.order_id = ? AND p.seller_id = ?
    ");
    $products_stmt->execute([$order['id'], $seller_id]);
    $order_products = $products_stmt->fetchAll();
    $order['products'] = $order_products;
    $orders_with_products[] = $order;
}

$status_options = ['قيد المعالجة', 'مؤكد', 'تم الشحن', 'تم التوصيل', 'ملغي'];
?>
<style>
    .dashboard-section {
        background: #f8fdfb;
        border-radius: 16px;
        padding: 32px 24px 24px 24px;
        margin: 32px auto;
        max-width: 1200px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    }
    .section-header h1 {
        font-size: 2.2rem;
        margin-bottom: 24px;
        color: #1a3c40;
        letter-spacing: 1px;
        text-align: right;
    }
    .table-container {
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.04);
        margin-bottom: 0;
    }
    thead th {
        background: #e6f2ef;
        color: #1a3c40;
        font-weight: 700;
        padding: 16px 10px;
        border-bottom: 2px solid #d1e7e0;
        text-align: right;
        font-size: 1.1rem;
    }
    tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f0f0f0;
        text-align: right;
        font-size: 1rem;
        vertical-align: middle;
    }
    tbody tr:last-child td {
        border-bottom: none;
    }
    .status-badge {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.95rem;
        font-weight: 600;
        color: #fff;
        background: #6c757d;
        display: inline-block;
    }
    .status-قيد-المعالجة { background: #ffc107; color: #333; }
    .status-مؤكد { background: #17a2b8; }
    .status-تم-الشحن { background: #007bff; }
    .status-تم-التوصيل { background: #28a745; }
    .status-ملغي { background: #dc3545; }
    .btn {
        padding: 6px 18px;
        border: none;
        border-radius: 6px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
        margin: 0 2px;
    }
    .btn-update {
        background: #17a2b8;
        color: #fff;
    }
    .btn-update:hover {
        background: #138496;
    }
    .btn-view {
        background: #28a745;
        color: #fff;
    }
    .btn-view:hover {
        background: #218838;
    }
    .status-update-form select {
        padding: 4px 10px;
        border-radius: 5px;
        border: 1px solid #d1e7e0;
        margin-left: 6px;
        font-size: 1rem;
    }
    .empty-state {
        text-align: center;
        color: #aaa;
        padding: 40px 0;
    }
    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 10px;
        color: #b2bec3;
    }
    @media (max-width: 900px) {
        .dashboard-section { padding: 12px 2px; }
        thead th, tbody td { font-size: 0.95rem; padding: 10px 4px; }
    }
</style>
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
                    <th>التفاصيل</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders_with_products)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <p>لا توجد طلبات لعرضها.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders_with_products as $order): ?>
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
                            <td data-label="التفاصيل">
                                <button class="btn btn-view" onclick="showOrderDetails(<?= htmlspecialchars(json_encode($order)) ?>)">
                                    <i class="fas fa-eye"></i> عرض
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Order Details Modal -->
<div id="order-details-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>تفاصيل الطلب</h2>
        <div id="modal-order-id"></div>
        <div id="modal-customer-info"></div>
        <table id="modal-products-table">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <tbody>
                <!-- Product rows will be inserted here by JavaScript -->
            </tbody>
        </table>
        <div id="modal-total-amount"></div>
    </div>
</div>

<script>
function showOrderDetails(order) {
    document.getElementById('modal-order-id').innerHTML = `<h3>رقم الطلب: #${order.id}</h3>`;
    
    document.getElementById('modal-customer-info').innerHTML = `
        <p><strong>العميل:</strong> ${order.shipping_fullname}</p>
        <p><strong>رقم الهاتف:</strong> ${order.shipping_phone ? order.shipping_phone : ''}</p>
        <p><strong>العنوان:</strong> ${order.shipping_address}, ${order.shipping_city}</p>
    `;

    const productsTableBody = document.querySelector('#modal-products-table tbody');
    productsTableBody.innerHTML = '';
    order.products.forEach(product => {
        const row = `<tr>
            <td>${product.name}</td>
            <td>${product.quantity}</td>
            <td>${parseFloat(product.total).toFixed(2)} درهم</td>
        </tr>`;
        productsTableBody.innerHTML += row;
    });

    document.getElementById('modal-total-amount').innerHTML = `<h4>المجموع: ${parseFloat(order.total_amount).toFixed(2)} درهم</h4>`;

    document.getElementById('order-details-modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('order-details-modal').style.display = 'none';
}

// Close modal if user clicks outside of it
window.onclick = function(event) {
    const modal = document.getElementById('order-details-modal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
