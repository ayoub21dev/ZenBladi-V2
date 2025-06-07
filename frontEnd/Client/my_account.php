<!-- My Account Section -->
<section id="account" class="dashboard-section">
    <h1>إعدادات حسابي</h1>
    <div class="account-form">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form action="Client.php#account" method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">الاسم الأول</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">الاسم الأخير</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
            </div>
            <hr>
            <p>تغيير كلمة المرور (اتركه فارغاً لعدم التغيير)</p>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">كلمة المرور الجديدة</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>
            <button type="submit" name="update_account" class="btn">حفظ التغييرات</button>
        </form>
    </div>
</section> 