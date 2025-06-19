<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$orderId = isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : 'N/A';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تأكيد الطلب - كنوز المغرب الطبيعية</title>
    <link rel="stylesheet" href="assest/CSS/product_detail.css">
    <link rel="stylesheet" href="../Includes/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .confirmation-icon {
            font-size: 5em;
            color: #2c5530;
        }
        .confirmation-title {
            font-size: 2.5em;
            color: #2c5530;
            margin-top: 20px;
        }
        .confirmation-message {
            font-size: 1.2em;
            color: #333;
            margin-top: 15px;
        }
        .order-id {
            font-size: 1.1em;
            color: #555;
            background-color: #f0f0f0;
            padding: 10px 20px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 25px;
        }
        .home-link {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background-color: #2c5530;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .home-link:hover {
            background-color: #1e3a21;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../Includes/header.php'; ?>

    <main>
        <div class="confirmation-container">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="confirmation-title">شكراً لك!</h1>
            <p class="confirmation-message">لقد تم استلام طلبك بنجاح. سنتصل بك قريباً لتأكيد التفاصيل.</p>
            <div class="order-id">
                <strong>رقم طلبك هو:</strong> <?= $orderId ?>
            </div>
            <br>
            <a href="index.php" class="home-link">العودة إلى الصفحة الرئيسية</a>
        </div>
    </main>
</body>
</html> 