<!-- My Orders Section -->
<section id="orders" class="dashboard-section">
    <h1>طلباتي</h1>
    <div class="orders-history">
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
                <?php if (empty($allOrders)): ?>
                    <tr><td colspan="5">لا يوجد لديك أي طلبات.</td></tr>
                <?php else: ?>
                    <?php foreach ($allOrders as $order): ?>
                        <tr>
                            <td data-label="رقم الطلب">#ZB<?php echo $order['id']; ?></td>
                            <td data-label="التاريخ"><?php echo $order['order_date']; ?></td>
                            <td data-label="الإجمالي">$<?php echo htmlspecialchars(number_format(getOrderTotal($order['id'], $pdo), 2)); ?></td>
                            <td data-label="الحالة"><span class="status <?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                            <td data-label="إجراء">
                                <button class="btn">عرض التفاصيل</button>
                                <button class="btn btn-secondary">إعادة الطلب</button>
                                <?php if ($order['status'] === 'تم الشحن'): ?>
                                    <button class="btn btn-confirm">تأكيد الاستلام</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section> 