<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../BackEnd/db.php'; 
?>
 <?php
// استخراج 6 منتجات فقط مع معلومات التصنيف
$sql = "SELECT p.id, p.name, p.description, p.price, p.image, c.name as category_name 
FROM product p 
JOIN category c ON p.category_id = c.id 
WHERE p.is_approved = 1 
LIMIT 6";

// Add error handling for the query
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assest/CSS/index.css">
    <link rel="stylesheet" href="assest/CSS/searchBar.css">
    <link rel="stylesheet" href="../Includes/header.css">
    <style>
        /* تنسيق قسم المنتجات */
        .products-section {
            padding: 80px 20px;
            background-color: #f9f9f9;
            min-height: 400px; /* Add minimum height */
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #2c5530;
            margin-bottom: 50px;
            font-weight: bold;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Increased minimum width */
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px; /* Added padding */
        }
        
        .product-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s ease; /* Reduced transition time */
            text-align: center;
            display: flex;
            flex-direction: column;
            height: 100%; /* Fixed height */
            min-height: 450px; /* Minimum height */
            position: relative; /* Added for stability */
            overflow: hidden; /* Prevent content overflow */
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 250px; /* Fixed height */
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 15px;
            background-color: #f5f5f5; /* Fallback background */
        }
        
        .product-name {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2c5530;
            margin: 10px 0;
            height: 40px; /* Fixed height */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .product-description {
            font-size: 0.9rem;
            color: #666;
            margin: 10px 0;
            height: 60px; /* Fixed height */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        .product-price {
            font-size: 1.4rem;
            font-weight: bold;
            color: #e74c3c;
            margin: 10px 0;
        }
        
        .product-category {
            font-size: 0.8rem;
            color: #888;
            margin: 10px 0;
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 15px;
            display: inline-block;
        }
        
        .order-btn {
            background: linear-gradient(135deg, #2c5530, #4a7c4a);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: auto; /* Push button to bottom */
        }
        
        .order-btn:hover {
            background: linear-gradient(135deg, #1e3a21, #2c5530);
            transform: scale(1.02);
        }
        
        .no-products {
            text-align: center;
            color: #666;
            font-size: 1.2rem;
            grid-column: 1 / -1;
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
    <title>كنوز المغرب الطبيعية</title>
</head>
<body>
    
    <?php include __DIR__ . '/../Includes/header.php'; ?>

    <main>
 
        <!-- ===========Hero Section========= -->
        <section class="Hero-content">

            <div class="image-section">
                <img src="assest/images/HeaderPhoto.png" alt="منتجات مغربية تقليدية" class="main-image">
            </div>
            
            <div class="content-text">
                <h1 class="main-title">
                    اكتشف <span class="highlight-green">كنوز المغرب</span> الطبيعية
                </h1>
                <p class="description">
                    أجود المنتجات التقليدية والحرفيات اليدوية المغربية الأصيلة، من<br>
                    خيرات بلادنا مباشرة إليك جودة صنع، وثراء
                </p>
                <div class="action-buttons">
                    <button class="btn-large btn-question">هل أنت بائع؟</button>
                    <button class="btn-large btn-browse">تصفح المنتجات</button>
                </div>
            </div>

        </section>
        <!-- ===========Hero Section========= -->

        <!-- =========show 6 products=========  -->
        <section class="products-section">
            <h2 class="section-title">منتجاتنا المميزة</h2>
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div class="no-products">
                        <p>لا توجد منتجات متاحة حالياً</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php 
                                $imagePath = $product['image'];
                             
                                $imageUrl = htmlspecialchars($product['image']);
                                if (!str_starts_with($imageUrl, 'http') && !str_starts_with($imageUrl, '/')) {
                                    
                                    $baseAppPath = '/ZenBladi-V2/'; // Adjust if your setup is different
                                    $imageUrl = $baseAppPath . ltrim($product['image'], '/');
                                }
                            ?>
                            <img src="<?= $imageUrl ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image"
                                 onerror="this.src='<?= $baseAppPath ?? '' ?>frontEnd/assest/images/default-product.jpg'; this.onerror=null;">
                            
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            
                            <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                            
                            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                            
                            <div class="product-price"><?= number_format($product['price'], 2) ?> درهم</div>
                            
                            <button class="order-btn" onclick="orderProduct(<?= $product['id'] ?>)">
                                اطلب الآن
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <a href="alll_Product.php" class="view-all-btn" style="
                    background: linear-gradient(135deg, #2c5530, #4a7c4a);
                    color: white;
                    border: none;
                    padding: 15px 30px;
                    border-radius: 25px;
                    cursor: pointer;
                    font-size: 1.1rem;
                    font-weight: bold;
                    text-decoration: none;
                    display: inline-block;
                    transition: all 0.3s ease;
                ">
                    عرض جميع المنتوجات...
                </a>
            </div>
        </section>
        <!-- =========show 6 products=========  -->

    </main>
    
    <script src="assest/JS/mainNavigation.js"></script>
    <script>
        function orderProduct(productId) {
            // يمكنك إضافة منطق الطلب هنا
            alert('سيتم توجيهك لصفحة الطلب للمنتج رقم: ' + productId);
            // مثال: window.location.href = 'order.php?product_id=' + productId;
        }
    </script>
<!-- =====================================================why ZenBladi?============================= -->
 <section class="whyZenbladi">
<div class="containerzenbladi">
        <h1 class="main-titleZenbladi">لماذا زين بلدي؟</h1>
        
        <div class="cards-containerZenbladi">
            <div class="cardZenbladi">
                <div class="icon-containerZenbladi">
                    🚚
                </div>
                <h3 class="card-titleZenbladi">توصيل آمن وسريع</h3>
                <p class="card-descriptionZenbladi">
                    نضمن وصول منتجاتك بحالة ممتازة من خلال شبكة توصيل متوفرة في جميع أنحاء المغرب
                </p>
            </div>

            <div class="cardZenbladi">
                <div class="icon-containerZenbladi">
                    💰
                </div>
                <h3 class="card-titleZenbladi">دعم الحرفيين المحليين</h3>
                <p class="card-descriptionZenbladi">
                    نساعد الحرفيين والمنتجين المحليين على الوصول إلى عملاء جدد وتوسيع أعمالهم
                </p>
            </div>

            <div class="cardZenbladi">
                <div class="icon-containerZenbladi">
                    🌿
                </div>
                <h3 class="card-titleZenbladi">منتجات طبيعية 100%</h3>
                <p class="card-descriptionZenbladi">
                    نقدم فقط منتجات مغربية طبيعية وتقليدية عالية الجودة من مصادر موثوقة
                </p>
            </div>
        </div>
    </div>
    </section>


    <script src="assest/JS/mainNavigation.js"></script>
</body>
</html>

