<?php
// Helper function to normalize image paths
function get_correct_image_path($raw_path) {
    // The target directory is 'assest/img_Products/'
    $target_dir = 'assest/img_Products/';

    // Find the last occurrence of the target directory in the path
    $pos = strrpos($raw_path, $target_dir);

    if ($pos !== false) {
        // If found, take the substring from that point onwards
        return substr($raw_path, $pos);
    } else {
        // If the target directory is not in the path, it might be an old path
        // that only contains the filename. Prepend the target directory.
        // This handles cases where the path is just 'image.jpg'
        return $target_dir . basename($raw_path);
    }
}

// This is a partial file for the products section
// It expects $seller_id and $pdo to be defined from seller_logic.php


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
                            <td><img src="../<?= htmlspecialchars(get_correct_image_path($product['image'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumbnail"></td>
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
                                    <form action="#products" method="post" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="button" class="btn btn-delete" onclick="openDeleteModal(<?= $product['id'] ?>)"><i class="fas fa-trash"></i> حذف</button>
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

<!-- Deletion Confirmation Modal -->
<div id="delete-confirm-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>تأكيد الحذف</h2>
        <p>هل أنت متأكد من أنك تريد حذف هذا المنتج؟ لا يمكن التراجع عن هذا الإجراء.</p>
        <div class="modal-actions">
            <button id="cancel-delete" class="btn btn-secondary">إلغاء</button>
            <form id="delete-form" action="#products" method="post" style="display: inline;">
                <input type="hidden" name="product_id" id="product-id-to-delete">
                <button type="submit" name="delete_product" class="btn btn-delete">حذف المنتج</button>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background-color: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
    width: 90%;
    max-width: 450px;
    text-align: center;
    animation: fadeIn 0.3s;
}
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.modal-content h2 {
    font-size: 1.8rem;
    color: #2c5530;
    margin-bottom: 15px;
}
.modal-content p {
    font-size: 1.1rem;
    color: #555;
    margin-bottom: 25px;
}
.modal-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
}
.close-btn {
    color: #aaa;
    float: left;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.close-btn:hover {
    color: #333;
}
</style>

<script>
function openDeleteModal(productId) {
    document.getElementById('product-id-to-delete').value = productId;
    document.getElementById('delete-confirm-modal').style.display = 'flex';
}

document.querySelector('.close-btn').onclick = function() {
    document.getElementById('delete-confirm-modal').style.display = 'none';
}

document.getElementById('cancel-delete').onclick = function() {
    document.getElementById('delete-confirm-modal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('delete-confirm-modal')) {
        document.getElementById('delete-confirm-modal').style.display = 'none';
    }
}
</script>
