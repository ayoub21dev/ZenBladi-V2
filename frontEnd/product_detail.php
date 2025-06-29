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

require_once __DIR__ . '/../Includes/session_config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../BackEnd/db.php';

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$productId = $_GET['id'];

// Fetch product details
$sql = "SELECT p.id, p.name, p.description, p.price, p.image, c.name as category_name 
        FROM product p 
        JOIN category c ON p.category_id = c.id 
        WHERE p.id = :id AND p.is_approved = 1";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $product = null;
}

// If product not found
if (!$product) {
    http_response_code(404);
    echo "Product not found.";
    exit;
}

$baseAppPath = '/ZenBladi-V2/'; // Adjust if your setup is different
$imagePath = $product['image'];
$correctRelativePath = $imagePath;
if (strpos($imagePath, '../') === 0) {
    $correctRelativePath = 'frontEnd/' . substr($imagePath, 3);
} else if (strpos($imagePath, 'frontEnd/') !== 0) {
    $correctRelativePath = 'frontEnd/' . ltrim($imagePath, '/');
}
$imageUrl = $baseAppPath . $correctRelativePath;
$fallbackImagePath = $baseAppPath . 'frontEnd/assest/images/default-product.jpg';

$userId = $_SESSION['user_id'] ?? null;

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - كنوز المغرب الطبيعية</title>
    <link rel="stylesheet" href="assest/CSS/product_detail.css">
    <link rel="stylesheet" href="../Includes/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Checkout Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            -webkit-animation-name: fadeIn;
            -webkit-animation-duration: 0.4s;
            animation-name: fadeIn;
            animation-duration: 0.4s
        }
        .modal-content {
            position: fixed;
            bottom: 0;
            background-color: #fefefe;
            width: 100%;
            -webkit-animation-name: slideIn;
            -webkit-animation-duration: 0.4s;
            animation-name: slideIn;
            animation-duration: 0.4s;
            border-radius: 20px 20px 0 0;
            padding: 20px;
            max-width: 500px;
            margin: auto;
            left: 0;
            right: 0;
        }
        .close-btn {
            color: #aaa;
            float: left;
            font-size: 28px;
            font-weight: bold;
        }
        .close-btn:hover, .close-btn:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        #checkout-form h2 {
            text-align: center;
            color: #2c5530;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .order-summary {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .order-summary h3 {
            color: #2c5530;
            border-bottom: 2px solid #2c5530;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        #confirm-order-btn {
            width: 100%;
            padding: 15px;
            background-color: #2c5530;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        #confirm-order-btn:hover {
            background-color: #1e3a21;
        }
        @-webkit-keyframes slideIn {
            from {bottom: -300px; opacity: 0} 
            to {bottom: 0; opacity: 1}
        }
        @keyframes slideIn {
            from {bottom: -300px; opacity: 0}
            to {bottom: 0; opacity: 1}
        }
        @-webkit-keyframes fadeIn {
            from {opacity: 0} 
            to {opacity: 1}
        }
        @keyframes fadeIn {
            from {opacity: 0} 
            to {opacity: 1}
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../Includes/header.php'; ?>

    <main class="product-detail-container">
        <div class="product-image-section">
            <img src="<?= htmlspecialchars(get_correct_image_path($product['image'])) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 class="product-detail-image"
                 onerror="this.onerror=null; this.src='<?= $fallbackImagePath ?>';">
        </div>

        <div class="product-info-section">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="product-category-chip"><?= htmlspecialchars($product['category_name']) ?></div>
            
            <p class="product-description-full"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            
            <div class="product-price-detail" data-price="<?= $product['price'] ?>"><?= number_format($product['price'], 2) ?> درهم</div>

            <form action="javascript:void(0);" onsubmit="buyNow(event, <?= $product['id'] ?>)" class="buy-form">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="quantity-selector">
                    <label for="quantity">الكمية:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="10">
                </div>
                <button type="submit" class="buy-now-btn">
                    <i class="fas fa-shopping-cart"></i> اشتر الآن
                </button>
            </form>
            <div id="login-required-message" style="display:none; color: #333; background: #fffbe7; border: 1px solid #ffe082; border-radius: 8px; padding: 15px; margin-top: 20px; text-align: center; font-size: 1.05em;">
                <span style="font-size:1.5em; color:#e6b800; vertical-align:middle;">&#9888;</span> 
                المرجو <a href="login.php" style="color: #2c5530; text-decoration: underline; font-weight: bold;">تسجيل الدخول</a> لكي تتمكن من شراء هذا المنتج.<br>
                إذا لم يكن لديك حساب، <a href="SignUpClient.php" style="color: #2c5530; text-decoration: underline; font-weight: bold;">أنشئ حساب جديد من هنا</a>.
            </div>
        </div>
    </main>

    <!-- Checkout Modal -->
    <div id="checkout-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <form id="checkout-form" action="../BackEnd/create_order.php" method="post">
                <h2>إتمام عملية الشراء</h2>
                
                <input type="hidden" name="product_id" id="modal-product-id">
                <input type="hidden" name="quantity" id="modal-quantity">
                <input type="hidden" name="total_price" id="modal-total-price">

                <div class="form-group">
                    <label for="fullname">الاسم الكامل</label>
                    <input type="text" id="fullname" name="shipping_fullname" required>
                </div>
                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="tel" id="phone" name="shipping_phone" rrequired pattern="[0-9]{8,15}" inputmode="numeric" maxlength="15" title="يرجى إدخال رقم هاتف صحيح (8 إلى 15 رقم)">
                </div>
                <div class="form-group">
                    <label for="address">العنوان</label>
                    <input type="text" id="address" name="shipping_address" required>
                </div>
                <div class="form-group">
                    <label for="city">المدينة</label>
                    <input type="text" id="city" name="shipping_city" required>
                </div>

                <div class="order-summary">
                    <h3>ملخص الطلب</h3>
                    <div class="summary-item">
                        <span>المنتج:</span>
                        <span id="summary-product-name"><?= htmlspecialchars($product['name']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>الكمية:</span>
                        <span id="summary-quantity">1</span>
                    </div>
                    <div class="summary-item">
                        <strong>المجموع:</strong>
                        <strong id="summary-total-price">0.00 درهم</strong>
                    </div>
                </div>

                <button type="submit" id="confirm-order-btn">تأكيد الطلب</button>
            </form>
        </div>
    </div>

    <script>
    const userId = <?= json_encode($userId) ?>;
    const modal = document.getElementById('checkout-modal');
    const closeBtn = document.querySelector('.close-btn');

    function buyNow(event, productId) {
        if (!userId) {
            event.preventDefault();
            var msgDiv = document.getElementById('login-required-message');
            msgDiv.style.display = 'block';
            msgDiv.scrollIntoView({behavior: 'smooth', block: 'center'});
            return false;
        }
        const quantity = document.getElementById('quantity').value;
        const price = document.querySelector('.product-price-detail').dataset.price;
        const totalPrice = (quantity * price).toFixed(2);
        document.getElementById('modal-product-id').value = productId;
        document.getElementById('modal-quantity').value = quantity;
        document.getElementById('modal-total-price').value = totalPrice;
        document.getElementById('summary-quantity').textContent = quantity;
        document.getElementById('summary-total-price').textContent = `${totalPrice} درهم`;
        modal.style.display = 'block';
    }

    closeBtn.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
    
</body>
</html> 