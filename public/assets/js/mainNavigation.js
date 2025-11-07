document.addEventListener('DOMContentLoaded', function() {
    const headerLoginBtn = document.getElementById('header-login-btn');
    const headerSignupBtn = document.getElementById('header-signup-btn');

    if (headerLoginBtn) {
        headerLoginBtn.addEventListener('click', function() {
            
            window.location.href = 'login.php'; 
        });
    }

    if (headerSignupBtn) {
        headerSignupBtn.addEventListener('click', function() {
           
            window.location.href = 'SignUpClient.php'; 
        });
    }

    // If you have other buttons on index.php like "هل أنت بائع؟" or "تصفح المنتجات"
    // and want to add navigation to them, you can add them here.
    // For example, if "هل أنت بائع؟" should go to SignUpSeller.php:
    const sellerQuestionBtn = document.querySelector('.btn-question'); // Using class selector as an example
    if (sellerQuestionBtn) {
        // It's good to give this button an ID for more reliable selection
        // e.g., <button class="btn-large btn-question" id="index-seller-signup-btn">هل أنت بائع؟</button>
        sellerQuestionBtn.addEventListener('click', function() {
            window.location.href = 'SignUpSeller.php'; // Adjust path if necessary
        });
    }
});
