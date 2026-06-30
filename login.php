<?php
// فایل: login.php

session_start();
require_once 'includes/db.php';

$page_title = 'ورود | AI Productivity Strategy';

$error = '';
$success = '';
$username_value = '';

// نمایش پیام موفقیت ثبت نام
if (isset($_SESSION['register_success'])) {
    $success = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}

// نمایش پیام بازیابی رمز
if (isset($_SESSION['reset_success'])) {
    $success = $_SESSION['reset_success'];
    unset($_SESSION['reset_success']);
}

// تولید کد کپچا فقط در صورتی که وجود نداشته باشد
if (!isset($_SESSION['login_captcha'])) {
    $captcha_code = rand(10000, 99999);
    $_SESSION['login_captcha'] = $captcha_code;
} else {
    $captcha_code = $_SESSION['login_captcha'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_value = trim($_POST['username']);
    $password = $_POST['password'];
    $captcha_input = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
    
    // بررسی کپچا
    if (empty($captcha_input) || $captcha_input != $_SESSION['login_captcha']) {
        $error = "کد امنیتی اشتباه است.";
        unset($_SESSION['login_captcha']);
    }
    // بررسی وجود نام کاربری/ایمیل
    elseif (empty($username_value) || empty($password)) {
        $error = "نام کاربری و رمز عبور را وارد کنید.";
        unset($_SESSION['login_captcha']);
    }
    else {
        // جستجو با نام کاربری یا ایمیل
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username_value, $username_value]);
        $user = $stmt->fetch();
        
        // بررسی وجود کاربر
        if (!$user) {
            $error = "کاربری با این مشخصات یافت نشد. لطفاً ثبت نام کنید.";
            unset($_SESSION['login_captcha']);
        }
        // بررسی صحت رمز عبور
        elseif (!password_verify($password, $user['password'])) {
            $error = "رمز عبور اشتباه است.";
            unset($_SESSION['login_captcha']);
        }
        // بررسی فعال بودن حساب
        elseif ($user['is_active'] == 0) {
            $error = "حساب کاربری شما غیرفعال است. با پشتیبانی تماس بگیرید.";
            unset($_SESSION['login_captcha']);
        }
        // ورود موفق
        else {
            // ذخیره اطلاعات در سشن
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // پاک کردن کپچا
            unset($_SESSION['login_captcha']);
            
            // هدایت به صفحه اصلی یا پنل ادمین
            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    }
    
    // اگر خطایی رخ داده، صفحه را دوباره بارگذاری کن
    if (!empty($error)) {
        header("Location: login.php?error=" . urlencode($error) . "&username=" . urlencode($username_value));
        exit;
    }
}

// دریافت خطا از URL
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

// دریافت نام کاربری از URL
if (isset($_GET['username'])) {
    $username_value = htmlspecialchars($_GET['username']);
}

include 'header.php';
?>

<style>
    /* استایل مخصوص صفحه لاگین */
    .login-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 140px);
        padding: 2rem 1rem;
    }
    
    .login-container {
        width: 100%;
        max-width: 450px;
    }
    
    .login-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .login-header i {
        font-size: 60px;
        color: #667eea;
    }
    
    .login-header h2 {
        margin-top: 10px;
        color: #333;
        font-size: 28px;
    }
    
    .login-header p {
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .input-group {
        position: relative;
        margin-bottom: 20px;
    }
    
    .input-group i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
        font-size: 16px;
    }
    
    .input-group input {
        width: 100%;
        padding: 14px 45px 14px 15px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
        font-family: inherit;
    }
    
    .input-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* استایل کپچا */
    .captcha-box {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .captcha-code {
        background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
        padding: 14px;
        text-align: center;
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 8px;
        border-radius: 10px;
        flex: 1;
        font-family: monospace;
        color: #333;
        text-shadow: 1px 1px 0 rgba(255,255,255,0.5);
    }
    
    .captcha-refresh {
        background: #667eea;
        color: white;
        border: none;
        padding: 14px 18px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 16px;
    }
    
    .captcha-refresh:hover {
        background: #5a67d8;
        transform: rotate(15deg);
    }
    
    .captcha-input {
        width: 100%;
        padding: 14px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 14px;
        text-align: center;
        letter-spacing: 2px;
    }
    
    .captcha-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-primary {
        width: 100%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 14px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .error {
        background: #fee;
        color: #c33;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        border-right: 3px solid #c33;
    }
    
    .error i {
        margin-left: 8px;
    }
    
    .success {
        background: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        border-right: 3px solid #28a745;
    }
    
    .success i {
        margin-left: 8px;
    }
    
    .register-link,
    .forgot-link {
        text-align: center;
        margin-top: 15px;
    }
    
    .register-link a,
    .forgot-link a {
        color: #667eea;
        text-decoration: none;
        font-size: 14px;
        transition: 0.3s;
    }
    
    .register-link a:hover,
    .forgot-link a:hover {
        text-decoration: underline;
    }
    
    hr {
        margin: 25px 0 20px;
        border: none;
        border-top: 1px solid #eee;
    }
    
    @media (max-width: 480px) {
        .login-card {
            padding: 30px 20px;
        }
        
        .login-header h2 {
            font-size: 24px;
        }
        
        .captcha-code {
            font-size: 20px;
            letter-spacing: 4px;
        }
    }
</style>

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-sign-in-alt"></i>
                <h2>خوش آمدید</h2>
                <p>برای ورود، نام کاربری یا ایمیل خود را وارد کنید</p>
            </div>
            
            <?php if($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="نام کاربری یا ایمیل" required autofocus value="<?= $username_value ?>">
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="رمز عبور" required>
                </div>
                
                <!-- بخش کپچا -->
                <div class="captcha-box">
                    <div class="captcha-code"><?= $captcha_code ?></div>
                    <button type="button" class="captcha-refresh" onclick="refreshCaptcha()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-shield-alt"></i>
                    <input type="text" name="captcha" class="captcha-input" placeholder="کد امنیتی را وارد کنید" required>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-arrow-left"></i> ورود
                </button>
            </form>
            
            <div class="forgot-link">
                <a href="forgot_password.php">
                    <i class="fas fa-key"></i> رمز عبور خود را فراموش کرده‌اید؟
                </a>
            </div>
            
            <hr>
            
            <div class="register-link">
                <a href="register.php">
                    <i class="fas fa-user-plus"></i> حساب کاربری ندارید؟ ثبت نام کنید
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function refreshCaptcha() {
    fetch('refresh_login_captcha.php')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.captcha-code').innerHTML = data.captcha;
        })
        .catch(error => {
            console.log('Error:', error);
        });
}
</script>

<?php include 'footer.php'; ?>