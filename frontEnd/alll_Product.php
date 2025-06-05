<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../BackEnd/db.php'; 

// Define the base path for your application
// Adjust this if your project is in a subdirectory of htdocs
$baseAppPath = '/ZenBladi-V2/'; // Assuming ZenBladi-V2 is directly in htdocs

// Get all approved products with category information
$sql = "SELECT p.id, p.name, p.description, p.price, p.image, c.name as category_name 
FROM product p 
JOIN category c ON p.category_id = c.id 
WHERE p.is_approved = 1";

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
    <title>جميع المنتجات - كنوز المغرب الطبيعية</title>
    <style>
        .products-section {
            padding: 80px 20px;
            background-color: #f9f9f9;
            min-height: 400px;
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 450px;
            position: relative;
            overflow: hidden;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 15px;
            background-color: #f5f5f5;
        }
        
        .product-name {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2c5530;
            margin: 10px 0;
            height: 40px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .product-description {
            font-size: 0.9rem;
            color: #666;
            margin: 10px 0;
            height: 60px;
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
            margin-top: auto;
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
    </style>
</head>
<body>
    <?php include __DIR__ . '/../Includes/header.php'; ?>

    <main>
        <section class="products-section">
            <h2 class="section-title">جميع المنتجات</h2>
            
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div class="no-products">لا توجد منتجات متاحة حاليا</div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        // Construct the correct image path
                        $imagePath = $baseAppPath . $product['image'];
                        // Define a fallback image path, also using baseAppPath for consistency if needed
                        $fallbackImagePath = $baseAppPath . 'frontEnd/assest/images/placeholder.jpg';
                        ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image" 
                                 onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($fallbackImagePath); ?>';">
                            <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-price"><?php echo htmlspecialchars($product['price']); ?> درهم</div>
                            <button class="order-btn">اطلب الآن</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>