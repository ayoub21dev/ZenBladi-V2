<?php require_once 'client_logic.php'; ?>
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
                <h2>ZenBladi</h2>
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
            <header class="main-header">
                <div class="header-left">
                    <button class="menu-toggle"><i class="fas fa-bars"></i></button>
                </div>
                <div class="header-user">
                    <span>مرحباً، <?php echo htmlspecialchars($customerName); ?>!</span>
                </div>
            </header>
            
            <?php include 'dashboard_home.php'; ?>
            <?php include 'my_orders.php'; ?>
            <?php include 'my_account.php'; ?>

        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const navLinks = document.querySelectorAll('.sidebar-nav a');
            const sections = document.querySelectorAll('.dashboard-section');
            const navItems = document.querySelectorAll('.sidebar-nav li');

            // Function to switch section
            function switchSection(targetId) {
                sections.forEach(section => {
                    section.classList.remove('active-section');
                });
                const targetSection = document.querySelector(targetId);
                if (targetSection) {
                    targetSection.classList.add('active-section');
                }
            }

            // Handle hash on page load
            if (window.location.hash) {
                const targetId = window.location.hash;
                const targetLink = document.querySelector(`.sidebar-nav a[href="${targetId}"]`);
                if (targetLink) {
                    navItems.forEach(item => item.classList.remove('active'));
                    targetLink.parentElement.classList.add('active');
                    switchSection(targetId);
                }
            } else {
                // Default to dashboard if no hash
                switchSection('#dashboard');
            }

            // Menu toggle for mobile
            if (menuToggle) {
                menuToggle.addEventListener('click', () => sidebar.classList.toggle('active'));
            }

            // Handle sidebar link clicks
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');
                    if (targetId.startsWith('#')) {
                        e.preventDefault();
                        history.pushState(null, null, targetId); // Update URL hash
                        
                        navItems.forEach(item => item.classList.remove('active'));
                        this.parentElement.classList.add('active');
                        
                        switchSection(targetId);

                        if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
                            sidebar.classList.remove('active');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
