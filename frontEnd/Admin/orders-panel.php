<?php
require_once __DIR__ . '/../../Includes/session_config.php';
require_once '../../backend/db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Handle delete order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
    $order_id = $_POST['delete_order_id'];
    try {
        $pdo->prepare('DELETE FROM order_link WHERE order_id = ?')->execute([$order_id]);
        $pdo->prepare('DELETE FROM customer_order WHERE id = ?')->execute([$order_id]);
        $message = 'تم حذف الطلب بنجاح.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'خطأ في حذف الطلب: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Filters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$where = [];
$params = [];
if ($status_filter) {
    $where[] = 'co.status = ?';
    $params[] = $status_filter;
}
if ($search) {
    $where[] = '(c.first_name LIKE ? OR c.last_name LIKE ? OR co.id LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch orders
$sql = "SELECT co.*, c.first_name, c.last_name, c.email, 
        (SELECT SUM(ol.total) FROM order_link ol WHERE ol.order_id = co.id) as total_amount
        FROM customer_order co
        JOIN customer c ON co.customer_id = c.id
        $where_clause
        ORDER BY co.created_at DESC";
$orders = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    // Fetch products for each order
    foreach ($orders as $key => $order) {
        $products_sql = "SELECT p.name, ol.quantity, ol.total FROM order_link ol JOIN product p ON ol.product_id = p.id WHERE ol.order_id = ?";
        $products_stmt = $pdo->prepare($products_sql);
        $products_stmt->execute([$order['id']]);
        $orders[$key]['products'] = $products_stmt->fetchAll();
    }
} catch (PDOException $e) {
    $message = 'خطأ في جلب الطلبات: ' . $e->getMessage();
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الطلبات - زين بلدي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; direction: rtl; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: linear-gradient(135deg, #2c3e50, #34495e); color: white; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; }
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { font-size: 24px; margin-bottom: 5px; color: #3498db; }
        .sidebar-menu { list-style: none; padding: 20px 0; }
        .sidebar-menu li { margin: 5px 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 15px 25px; color: #ecf0f1; text-decoration: none; transition: all 0.3s ease; border-right: 3px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: rgba(52, 152, 219, 0.2); border-right-color: #3498db; color: white; }
        .sidebar-menu a i { margin-left: 15px; width: 20px; text-align: center; }
        .logout-btn { color: #e74c3c !important; }
        .logout-btn:hover { background-color: rgba(231, 76, 60, 0.2) !important; border-right-color: #e74c3c !important; }
        .main-content { margin-right: 280px; padding: 30px; width: calc(100% - 280px); }
        .page-header { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .page-header h1 { color: #2c3e50; font-size: 28px; margin-bottom: 10px; }
        .filters-section { background: white; padding: 22px 20px 10px 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(44,62,80,0.07); margin-bottom: 22px; }
        .filters-grid { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 10px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: bold; color: #2c3e50; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; background: #f8f9fa; }
        .btn { padding: 8px 18px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; transition: all 0.3s ease; }
        .btn-primary { background: linear-gradient(90deg, #3498db 80%, #2980b9 100%); color: white; font-weight: bold; }
        .btn-primary:hover { background: linear-gradient(90deg, #2980b9 80%, #3498db 100%); }
        .btn-danger { background: linear-gradient(90deg, #e74c3c 80%, #c0392b 100%); color: #fff; font-weight: bold; border: none; padding: 7px 18px; border-radius: 6px; font-size: 1rem; transition: background 0.2s; }
        .btn-danger:hover { background: linear-gradient(90deg, #c0392b 80%, #e74c3c 100%); }
        .orders-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(44,62,80,0.07);
            overflow: hidden;
            width: 100%;
            margin-top: 20px;
        }
        .orders-table th, .orders-table td {
            padding: 16px 12px;
            text-align: right;
            border-bottom: 1px solid #f0f0f0;
            font-size: 1.08rem;
            vertical-align: middle;
        }
        .orders-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e0e0e0;
        }
        .orders-table tbody tr:hover {
            background: #f3f8fd;
            transition: background 0.2s;
        }
        .orders-table td {
            background: #fff;
        }
        .orders-table td:last-child {
            text-align: center;
        }
        .status-badge {
            padding: 7px 18px;
            border-radius: 22px;
            font-size: 1rem;
            font-weight: bold;
            display: inline-block;
            min-width: 90px;
            text-align: center;
            line-height: 1.7;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            margin: 0 auto;
            border: 1px solid #e0e0e0;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffe8a1;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #b7dfc7;
        }
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #b7dfc7;
        }
        .btn.btn-danger {
            background: linear-gradient(90deg, #e74c3c 80%, #c0392b 100%);
            color: #fff;
            font-weight: bold;
            border: none;
            padding: 7px 18px;
            border-radius: 6px;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn.btn-danger:hover {
            background: linear-gradient(90deg, #c0392b 80%, #e74c3c 100%);
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 60%; border-radius: 8px; }
        .close { color: #aaa; float: left; font-size: 28px; font-weight: bold; }
        .close:hover, .close:focus { color: black; text-decoration: none; cursor: pointer; }
        @media (max-width: 900px) {
            .main-content { margin-right: 0; padding: 10px; }
            .orders-table th, .orders-table td { font-size: 13px; padding: 10px 4px; }
        }
        @media (max-width: 600px) {
            .orders-table, .orders-table thead, .orders-table tbody, .orders-table th, .orders-table td, .orders-table tr { display: block; }
            .orders-table tr { margin-bottom: 15px; background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
            .orders-table td { border: none; position: relative; padding-right: 50%; min-height: 40px; }
            .orders-table td:before { position: absolute; right: 10px; top: 12px; white-space: nowrap; font-weight: bold; color: #888; }
            .orders-table td:nth-child(1):before { content: 'رقم الطلب'; }
            .orders-table td:nth-child(2):before { content: 'العميل'; }
            .orders-table td:nth-child(3):before { content: 'البريد الإلكتروني'; }
            .orders-table td:nth-child(4):before { content: 'التاريخ'; }
            .orders-table td:nth-child(5):before { content: 'الحالة'; }
            .orders-table td:nth-child(6):before { content: 'الإجمالي'; }
            .orders-table td:nth-child(7):before { content: 'الإجراءات'; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>زين بلدي</h2>
                <p>لوحة تحكم الإدارة</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="manage-users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                <li><a href="manage-sellers.php"><i class="fas fa-store"></i> إدارة البائعين</a></li>
                <li><a href="manage-products.php"><i class="fas fa-box"></i> إدارة المنتجات</a></li>
                <li><a href="orders-panel.php" class="active"><i class="fas fa-shopping-cart"></i> لوحة الطلبات</a></li>
                <li><a href="reports.php"><i class="fas fa-flag"></i> التقارير والشكاوى</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> إعدادات المنصة</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> التحليلات</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="page-header">
                <h1>لوحة الطلبات</h1>
                <p>عرض وإدارة جميع الطلبات في المنصة</p>
            </div>
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <div class="filters-section">
                <form method="GET">
                    <div class="filters-grid">
                        <div class="form-group">
                            <label>بحث</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="اسم العميل أو رقم الطلب">
                        </div>
                        <div class="form-group">
                            <label>الحالة</label>
                            <select name="status">
                                <option value="">جميع الحالات</option>
                                <option value="بانتظار الموافقة" <?= $status_filter === 'بانتظار الموافقة' ? 'selected' : '' ?>>بانتظار الموافقة</option>
                                <option value="قيد التجهيز" <?= $status_filter === 'قيد التجهيز' ? 'selected' : '' ?>>قيد التجهيز</option>
                                <option value="تم التوصيل" <?= $status_filter === 'تم التوصيل' ? 'selected' : '' ?>>تم التوصيل</option>
                                <option value="ملغي" <?= $status_filter === 'ملغي' ? 'selected' : '' ?>>ملغي</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">تطبيق الفلاتر</button>
                    <a href="orders-panel.php" class="btn btn-danger">إعادة تعيين</a>
                </form>
            </div>
            <div class="orders-table">
                <table style="width:100%">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>البريد الإلكتروني</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>الإجمالي</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="7" style="text-align:center; padding:40px;">لا توجد طلبات</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                    <td><?= htmlspecialchars($order['email']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                                    <td><span class="status-badge status-pending"><?= htmlspecialchars($order['status']) ?></span></td>
                                    <td><?= number_format($order['total_amount'], 2) ?> د.م.</td>
                                    <td>
                                        <button class="btn btn-primary" onclick="viewOrder(<?= htmlspecialchars(json_encode($order)) ?>)">عرض التفاصيل</button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟');">
                                            <input type="hidden" name="delete_order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" class="btn btn-danger">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>تفاصيل الطلب</h2>
            <div id="modal-order-details"></div>
        </div>
    </div>

    <script>
    function viewOrder(order) {
        const modal = document.getElementById('orderDetailsModal');
        const detailsContainer = document.getElementById('modal-order-details');
        let productsHtml = '<ul>';
        order.products.forEach(p => {
            productsHtml += `<li>${p.name} (الكمية: ${p.quantity}) - ${p.total} د.م.</li>`;
        });
        productsHtml += '</ul>';
        detailsContainer.innerHTML = `
            <p><strong>رقم الطلب:</strong> ${order.id}</p>
            <p><strong>العميل:</strong> ${order.first_name} ${order.last_name}</p>
            <p><strong>البريد الإلكتروني:</strong> ${order.email}</p>
            <p><strong>عنوان الشحن:</strong> ${order.shipping_address}, ${order.shipping_city}</p>
            <p><strong>الحالة:</strong> ${order.status}</p>
            <p><strong>إجمالي المبلغ:</strong> ${order.total_amount} د.م.</p>
            <p><strong>المنتجات:</strong></p>
            ${productsHtml}
        `;
        modal.style.display = 'block';
    }

    function closeModal() {
        document.getElementById('orderDetailsModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('orderDetailsModal');
        if (event.target === modal) {
            closeModal();
        }
    }
    </script>
</body>
</html>

