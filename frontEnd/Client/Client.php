<?php

require_once __DIR__ . '/../../Includes/session_config.php';
require_once 'client_logic.php';
// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - ZenBladi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="../index.php">
                    <img src="../../Includes/logo.png" alt="ZenBladi Logo" style="max-width: 120px; height: auto; display: block; margin: 0 auto;">
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#dashboard"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
                    <li><a href="#orders"><i class="fas fa-box"></i> طلباتي</a></li>
                    <li><a href="#account"><i class="fas fa-user-cog"></i> حسابي</a></li>
                    <li><a href="#support"><i class="fas fa-headset"></i> الدعم الفني</a></li>
                    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
        
            
            <?php include 'dashboard_home.php'; ?>
            <?php include 'my_orders.php'; ?>
            <?php include 'my_account.php'; ?>

        </main>
    </div>
       <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.sidebar-nav a');
            const sections = document.querySelectorAll('.dashboard-section');
            const navItems = document.querySelectorAll('.sidebar-nav li');

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
                const targetLink = document.querySelector(`.sidebar-nav a[href$="${targetId}"]`);
                if (targetLink) {
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
