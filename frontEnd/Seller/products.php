<?php
// This is a partial file for the products section
// It expects $seller_id and $pdo to be defined from seller_logic.php

// Handle Product Deletion
if (isset($_POST['delete_product'])) {
    $product_id_to_delete = $_POST['product_id'];
    
    // To maintain data integrity, we should first delete order items linked to the product
    $delete_order_links_stmt = $pdo->prepare("DELETE FROM order_link WHERE product_id = ?");
    $delete_order_links_stmt->execute([$product_id_to_delete]);

    // Now, delete the product
    $delete_product_stmt = $pdo->prepare("DELETE FROM product WHERE id = ? AND seller_id = ?");
    $delete_product_stmt->execute([$product_id_to_delete, $seller_id]);
    
    // Redirect to the same page to see the changes
    header('Location: Seller.php#products');
    exit();
}


// Fetch all products for this seller
$products_stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.image, p.is_approved, c.name as category_name 
    FROM product p
    JOIN category c ON p.category_id = c.id
    WHERE p.seller_id = ? 
    ORDER BY p.created_at DESC
");
$products_stmt->execute([$seller_id]);
$all_products = $products_stmt->fetchAll();

?>
<section id="products" class="dashboard-section">
    <div class="section-header">
        <h1>منتجاتي</h1>
        <a href="add-product.php" class="btn">إضافة منتج جديد</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>صورة المنتج</th>
                    <th>اسم المنتج</th>
                    <th>السعر</th>
                    <th>الصنف</th>
                    <th>حالة الموافقة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($all_products)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-box"></i>
                                <p>لم تقم بإضافة أي منتجات بعد. <a href="add-product.php">أضف منتجك الأول!</a></p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($all_products as $product): ?>
                        <tr>
                            <td><img src="../assest/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumbnail"></td>
                            <td data-label="اسم المنتج"><?= htmlspecialchars($product['name']) ?></td>
                            <td data-label="السعر"><?= number_format($product['price'], 2) ?> درهم</td>
                            <td data-label="الصنف"><?= htmlspecialchars($product['category_name']) ?></td>
                            <td data-label="حالة الموافقة">
                                <span class="status-badge <?= $product['is_approved'] ? 'status-approved' : 'status-pending-approval' ?>">
                                    <?= $product['is_approved'] ? 'تمت الموافقة' : 'في انتظار الموافقة' ?>
                                </span>
                            </td>
                            <td data-label="إجراءات">
                                <div class="action-buttons">
                                    <form action="add-product.php" method="get" style="display: inline;">
                                        <input type="hidden" name="edit_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="btn btn-edit"><i class="fas fa-edit"></i> تعديل</button>
                                    </form>
                                    <form action="#products" method="post" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من أنك تريد حذف هذا المنتج؟');">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" name="delete_product" class="btn btn-delete"><i class="fas fa-trash"></i> حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
