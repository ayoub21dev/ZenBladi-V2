<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="header" dir="rtl">
  <a href="../frontEnd/index.php"><img src="../Includes/logo.png" alt="ZenBladi Logo" class="logo" style="height:60px; margin-right:50px;"/></a>

  <div class="nav-buttons">
    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])): ?>
      <?php
        $userType = $_SESSION['user_type'];
        $userName = $_SESSION['user_name'] ?? 'حسابي';
        if ($userType === 'client') {
            $dashboardLink = '../frontEnd/Client/Client.php';
        } elseif ($userType === 'seller') {
            $dashboardLink = '../frontEnd/Seller/Seller.php';
        } elseif ($userType === 'admin') {
            $dashboardLink = '../frontEnd/Admin/admin.php';
        } else {
            $dashboardLink = '#';
        }
      ?>
      <a href="<?= $dashboardLink ?>" class="btn btn-outline" style="margin-left:10px;"><i class="fas fa-user"></i> <?= htmlspecialchars($userName) ?></a>
      <a href="../frontEnd/Admin/logout.php" class="btn btn-green">تسجيل الخروج</a>
    <?php else: ?>
      <a href="../frontEnd/login.php" class="btn btn-outline" id="header-login-btn">تسجيل دخول</a>
      <a href="../frontEnd/SignUpClient.php" class="btn btn-green" id="header-signup-btn">فتح حساب</a>
    <?php endif; ?>
  </div>
</header>
