<?php
session_start();
require_once '../../backend/db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header('Location: ../login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Fetch categories for dropdown
$categories = [];
try {
    $cat_query = "SELECT id, name FROM category ORDER BY name";
    $cat_stmt = $pdo->prepare($cat_query);
    $cat_stmt->execute();
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    
    // Validation
    if (empty($name) || empty($description) || empty($price) || empty($category_id)) {
        $message = 'جميع الحقول مطلوبة';
        $messageType = 'error';
    } elseif (!is_numeric($price) || $price <= 0) {
        $message = 'يجب أن يكون السعر رقماً موجباً';
        $messageType = 'error';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $message = 'يرجى اختيار صورة للمنتج';
        $messageType = 'error';
    } else {
        // Handle image upload
        $upload_dir = '../assest/img_Products/';
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_info = $_FILES['image'];
        $file_type = $file_info['type'];
        $file_size = $file_info['size'];
        $file_tmp = $file_info['tmp_name'];
        $file_ext = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_type, $allowed_types)) {
            $message = 'نوع الملف غير مدعوم. يرجى اختيار صورة (JPG, PNG, GIF, WEBP, AVIF)';
            $messageType = 'error';
        } elseif ($file_size > $max_size) {
            $message = 'حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت';
            $messageType = 'error';
        } else {
            // Generate unique filename
            $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Insert product into database
                try {
                    $insert_query = "INSERT INTO product (seller_id, name, description, price, image, category_id, is_approved) VALUES (?, ?, ?, ?, ?, ?, FALSE)";
                    $stmt = $pdo->prepare($insert_query);
                    
                    if ($stmt->execute([$seller_id, $name, $description, $price, $upload_path, $category_id])) {
                        $message = 'تم إضافة المنتج بنجاح! سيتم مراجعته من قبل الإدارة قبل النشر.';
                        $messageType = 'success';
                        // Clear form data
                        $_POST = [];
                    } else {
                        $message = 'حدث خطأ أثناء إضافة المنتج';
                        $messageType = 'error';
                        // Delete uploaded file if database insert failed
                        if (file_exists($upload_path)) {
                            unlink($upload_path);
                        }
                    }
                } catch (PDOException $e) {
                    error_log('Database error: ' . $e->getMessage());
                    $message = 'حدث خطأ في قاعدة البيانات';
                    $messageType = 'error';
                    // Delete uploaded file if database insert failed
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            } else {
                $message = 'فشل في رفع الصورة';
                $messageType = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج جديد - زين بلدي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            direction: rtl;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c5530 0%, #4a7c4a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .back-btn {
            display: inline-block;
            margin: 20px 30px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #5E8C6A 0%, #4a7c4a 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(94, 140, 106, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(94, 140, 106, 0.4);
        }

        .form-container {
            padding: 40px;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c5530;
            font-size: 1rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a7c4a;
            background: white;
            box-shadow: 0 0 0 3px rgba(74, 124, 74, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            border: 2px dashed #4a7c4a;
            border-radius: 8px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .file-input-label:hover {
            background: #e9ecef;
            border-color: #2c5530;
        }

        .file-input-label i {
            font-size: 2rem;
            color: #4a7c4a;
            margin-bottom: 10px;
        }

        .file-input-text {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .preview-container {
            margin-top: 15px;
            text-align: center;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2c5530 0%, #4a7c4a 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(44, 85, 48, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(44, 85, 48, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                margin: 10px;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-plus-circle"></i> إضافة منتج جديد</h1>
            <p>أضف منتجاتك الطبيعية والمحلية لعرضها في المتجر</p>
        </div>
        
        <a href="Seller.php" class="back-btn">
            <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
        </a>
        
        <div class="form-container">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">اسم المنتج *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">السعر (درهم) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">الفئة *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">اختر الفئة</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="description">وصف المنتج *</label>
                        <textarea id="description" name="description" placeholder="اكتب وصفاً مفصلاً عن المنتج..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>صورة المنتج *</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" accept="image/*" required>
                            <label for="image" class="file-input-label">
                                <div>
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <div class="file-input-text">
                                        <strong>اضغط لاختيار صورة</strong><br>
                                        أو اسحب الصورة هنا<br>
                                        <small>JPG, PNG, GIF, WEBP, AVIF - حد أقصى 5MB</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="preview-container" id="preview-container" style="display: none;">
                            <img id="preview-image" class="preview-image" alt="معاينة الصورة">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus"></i> إضافة المنتج
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('preview-container');
            const previewImage = document.getElementById('preview-image');
            const label = document.querySelector('.file-input-label');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                    label.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
                label.style.display = 'flex';
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const price = document.getElementById('price').value;
            const description = document.getElementById('description').value.trim();
            const category = document.getElementById('category_id').value;
            const image = document.getElementById('image').files[0];
            
            if (!name || !price || !description || !category || !image) {
                e.preventDefault();
                alert('يرجى ملء جميع الحقول المطلوبة');
                return;
            }
            
            if (parseFloat(price) <= 0) {
                e.preventDefault();
                alert('يجب أن يكون السعر أكبر من صفر');
                return;
            }
            
            // Check file size (5MB = 5 * 1024 * 1024 bytes)
            if (image && image.size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('حجم الصورة كبير جداً. الحد الأقصى 5 ميجابايت');
                return;
            }
        });
        
        // Auto-resize textarea
        document.getElementById('description').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    </script>
</body>
</html>