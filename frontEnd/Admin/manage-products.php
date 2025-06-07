<?php
session_start();
require_once '../../backend/db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Handle product approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_product_id'])) {
        $product_id_to_approve = $_POST['approve_product_id'];
        try {
            $stmt = $pdo->prepare("UPDATE product SET is_approved = TRUE WHERE id = ?");
            if ($stmt->execute([$product_id_to_approve])) {
                $message = 'تمت الموافقة على المنتج بنجاح.';
                $messageType = 'success';
            } else {
                $message = 'حدث خطأ أثناء الموافقة على المنتج.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            error_log('Approve product error: ' . $e->getMessage());
            $message = 'خطأ في قاعدة البيانات.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['reject_product_id'])) {
        $product_id_to_reject = $_POST['reject_product_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM product WHERE id = ?");
            if ($stmt->execute([$product_id_to_reject])) {
                $message = 'تم رفض المنتج وحذفه بنجاح.';
                $messageType = 'success';
            } else {
                $message = 'حدث خطأ أثناء رفض المنتج.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            error_log('Reject product error: ' . $e->getMessage());
            $message = 'خطأ في قاعدة البيانات.';
            $messageType = 'error';
        }
    }
}

// Fetch products based on filter
$filter = $_GET['filter'] ?? 'pending'; // Default to pending

$sql = "SELECT p.id, p.name, p.description, p.price, p.image, p.created_at, s.store_name, c.name as category_name, p.is_approved 
        FROM product p 
        JOIN seller s ON p.seller_id = s.id 
        JOIN category c ON p.category_id = c.id";

if ($filter === 'pending') {
    $sql .= " WHERE p.is_approved = FALSE";
} elseif ($filter === 'approved') {
    $sql .= " WHERE p.is_approved = TRUE";
}
// For 'all', no additional WHERE clause for is_approved status

$sql .= " ORDER BY p.created_at DESC";

$products_list = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products_list = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Fetch products error: ' . $e->getMessage());
    $message = 'حدث خطأ أثناء جلب المنتجات.';
    $messageType = 'error';
}

// Fetch admin name for sidebar display (optional, but good for consistency)
$admin_name = '';
if (isset($_SESSION['user_id'])) {
    $name_query = "SELECT first_name, last_name FROM admin WHERE id = ?";
    $name_stmt = $pdo->prepare($name_query);
    $name_stmt->execute([$_SESSION['user_id']]);
    if ($row = $name_stmt->fetch()) {
        $admin_name = $row['first_name'] . ' ' . $row['last_name'];
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات - زين بلدي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assest/CSS/Admin/admin.css"> 
    <style>
        /* Styles from previous response, ensure they are complete */
        .main-content {
            padding: 20px;
            margin-right: 260px; /* Adjust if sidebar width is different */
        }
        .page-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .page-header h1 {
            font-size: 1.8rem;
            color: #333;
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
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        .products-table th, .products-table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .products-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c5530;
        }
        .products-table tr:last-child td {
            border-bottom: none;
        }
        .products-table tr:hover {
            background-color: #f1f1f1;
        }
        .product-image-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .action-buttons form {
            display: inline-block;
            margin-left: 5px;
        }
        .action-buttons .btn {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            color: white;
            transition: background-color 0.2s ease;
        }
        .btn-approve {
            background-color: #28a745; 
        }
        .btn-approve:hover {
            background-color: #218838;
        }
        .btn-reject {
            background-color: #dc3545; 
        }
        .btn-reject:hover {
            background-color: #c82333;
        }
        .no-products {
            text-align: center;
            padding: 30px;
            color: #777;
            font-size: 1.1rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .filter-tabs {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .filter-tabs a {
            padding: 10px 15px;
            text-decoration: none;
            background-color: #e9ecef;
            color: #495057;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .filter-tabs a:hover {
            background-color: #d3d9df;
        }
        .filter-tabs a.active {
            background-color: #2c5530;
            color: white;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
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
                <li><a href="manage-products.php" class="active"><i class="fas fa-box"></i> إدارة المنتجات</a></li>
                <li><a href="orders-panel.php"><i class="fas fa-shopping-cart"></i> لوحة الطلبات</a></li>
                <li><a href="reports.php"><i class="fas fa-flag"></i> التقارير والشكاوى</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
                <li><a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1>إدارة المنتجات</h1>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="filter-tabs">
                <a href="?filter=pending" class="<?php echo ($filter === 'pending') ? 'active' : ''; ?>">منتجات بانتظار الموافقة</a>
                <a href="?filter=approved" class="<?php echo ($filter === 'approved') ? 'active' : ''; ?>">منتجات موافق عليها</a>
                <a href="?filter=all" class="<?php echo ($filter === 'all') ? 'active' : ''; ?>">جميع المنتجات</a>
            </div>

            <?php if (empty($products_list)): ?>
                <div class="no-products">
                    <p><i class="fas fa-info-circle"></i> لا توجد منتجات لعرضها حالياً حسب الفلتر المختار.</p>
                </div>
            <?php else: ?>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>اسم المنتج</th>
                            <th>المتجر</th>
                            <th>السعر</th>
                            <th>الفئة</th>
                            <th>تاريخ الإضافة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products_list as $product): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $image_path = $product['image'];
                                    // The path stored by add-product.php is '../assest/img_Products/filename.jpg'
                                    // This is relative to the frontEnd/Seller/ directory.
                                    // From frontEnd/Admin/, this path is correct.
                                    ?>
                                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image-thumbnail">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['store_name']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($product['price'], 2)); ?> درهم</td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($product['created_at']))); ?></td>
                                <td>
                                    <?php if ($product['is_approved']): ?>
                                        <span class="status-badge status-approved">موافق عليه</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">بانتظار الموافقة</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <?php if (!$product['is_approved']): ?>
                                        <form method="POST" action="manage-products.php?filter=<?php echo htmlspecialchars($filter); ?>">
                                            <input type="hidden" name="approve_product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn btn-approve" title="موافقة"><i class="fas fa-check"></i> موافقة</button>
                                        </form>
                                        <form method="POST" action="manage-products.php?filter=<?php echo htmlspecialchars($filter); ?>" onsubmit="return confirm('هل أنت متأكد أنك تريد رفض هذا المنتج؟ سيتم حذفه نهائياً.');">
                                            <input type="hidden" name="reject_product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn btn-reject" title="رفض وحذف"><i class="fas fa-times"></i> رفض</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>