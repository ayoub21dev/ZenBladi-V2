<?php require_once 'seller_logic.php'; ?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم البائع - زين بلدي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assest/CSS/Seller/Seller.css">
    <link rel="stylesheet" href="../assest/CSS/Seller/profile.css">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 700px;
            border-radius: 10px;
        }
        .close-btn {
            color: #aaa;
            float: left;
            font-size: 28px;
            font-weight: bold;
        }
        .close-btn:hover,
        .close-btn:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>زين بلدي</h2>
                <p>لوحة تحكم البائع</p>
            </div>
            <ul class="sidebar-menu">
                <li class="active"><a href="#dashboard"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="#products"><i class="fas fa-box"></i> منتجاتي</a></li>
                <li><a href="add-product.php"><i class="fas fa-plus"></i> إضافة منتج جديد</a></li>
                <li><a href="#orders"><i class="fas fa-shopping-cart"></i> الطلبات</a></li>
                <li><a href="#profile"><i class="fas fa-user"></i> الملف الشخصي</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <?php include '_dashboard_home.php'; ?>
            <?php include 'products.php'; ?>
            <?php include 'orders.php'; ?>
            <?php include 'profile.php'; ?>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.sidebar-menu a');
            const sections = document.querySelectorAll('.dashboard-section');
            const navItems = document.querySelectorAll('.sidebar-menu li');

            function switchSection(targetId) {
                // Hide all sections
                sections.forEach(section => {
                    section.style.display = 'none';
                });
                // Show the target section
                const targetSection = document.querySelector(targetId);
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
            }

            function updateActiveLink(targetId) {
                navItems.forEach(item => item.classList.remove('active'));
                const targetLink = document.querySelector(`.sidebar-menu a[href$="${targetId}"]`);
                if (targetLink && !targetLink.href.includes('add-product.php')) {
                    targetLink.parentElement.classList.add('active');
                }
            }

            // Initial load
            const currentHash = window.location.hash || '#dashboard';
            switchSection(currentHash);
            updateActiveLink(currentHash);

            // Handle sidebar link clicks
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');
                    if (targetId.startsWith('#')) {
                        e.preventDefault();
                        window.location.hash = targetId; // This will trigger the hashchange event
                    }
                });
            });

             // Listen for hash changes to handle back/forward navigation
            window.addEventListener('hashchange', () => {
                const hash = window.location.hash || '#dashboard';
                switchSection(hash);
                updateActiveLink(hash);
            });
        });
    </script>
</body>
</html>