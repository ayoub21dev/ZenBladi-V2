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
 
    <title>كنوز المغرب الطبيعية</title>
    <style>
        .product-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
    </style>
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
                    <a href="alll_Product.php" class="btn-large btn-browse">تصفح المنتجات</a>
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
                        <a href="product_detail.php?id=<?= $product['id'] ?>" class="product-card-link">
                            <div class="product-card">
                                <?php 
                                    $imagePath = $product['image'];
                                 
                                    $imageUrl = htmlspecialchars($imagePath);
                                    if (!str_starts_with($imageUrl, 'http') && !str_starts_with($imageUrl, '/')) {
                                        
                                        $baseAppPath = '/ZenBladi-V2/'; // Adjust if your setup is different
                                        $imageUrl = $baseAppPath . ltrim($imagePath, '/');
                                    }
                                ?>
                                <img src="<?= $imageUrl ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="product-image"
                                     onerror="this.src='<?= $baseAppPath ?? '' ?>frontEnd/assest/images/default-product.jpg'; this.onerror=null;">
                                
                                <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                
                                <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                                
                                <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) . '...' ?></p>
                                
                                <div class="product-price"><?= number_format($product['price'], 2) ?> درهم</div>
                                
                                <div class="order-btn">
                                    عرض التفاصيل
                                </div>
                            </div>
                        </a>
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
        <!-- ========= end of show 6 products=========  -->

    </main>
    
    <script src="assest/JS/mainNavigation.js"></script>
    
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
    <!-- ========== Seller Join Section ========== -->
    <section>
    <div class="signup-container">
        
        <div class="form-section">
            <div class="form-header">
                <h1>انضم إلينا كبائع</h1>
                <p>هل تنتج منتجات مغربية تقليدية أو طبيعية؟ انضم إلى منصتنا وابدأ في بيع منتجاتك عبر الإنترنت لعملاء من جميع أنحاء المغرب</p>
            </div>
            <ul class="features-list">
                <li>
                    <div class="check-icon"></div>
                    إنشاء متجر خاص بك مجاناً
                </li>
                <li>
                    <div class="check-icon"></div>
                    إدارة المنتجات بسهولة
                </li>
                <li>
                    <div class="check-icon"></div>
                    الوصول إلى عملاء جدد في جميع أنحاء المغرب
                </li>
                <li>
                    <div class="check-icon"></div>
                    متابعة الطلبات وإدارة المبيعات
                </li>
            </ul>
            <button class="cta-button" onclick="startRegistration()">
                سجل كبائع الآن
            </button>
        </div>
        <div class="image-section">
            <div class="image-container">
                <div class="main-illustration">
                    <div class="store-icon">🏬</div>
                </div>
                <div class="image-text">
                    <h2>ابدأ متجرك الآن</h2>
                    <p>انضم إلى منصتنا وحقق أرباحاً من منتجاتك المحلية</p>
                </div>
            </div>
        </div>              
    </div>
    </section>
    <script>
        function startRegistration() {
            window.location.href = 'SignUpSeller.php';
        }
    </script>
</body>
</html>

