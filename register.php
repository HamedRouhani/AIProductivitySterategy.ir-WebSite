<?php
// فایل: register.php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // بررسی کپچا
    if (!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION['register_captcha']) {
        $error = "❌ کد امنیتی اشتباه است.";
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // اعتبارسنجی
        if (empty($username) || empty($email) || empty($password)) {
            $error = "❌ تمام فیلدها را پر کنید.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $error = "❌ نام کاربری باید 3 تا 20 کاراکتر (حروف، اعداد، زیرخط) باشد.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "❌ ایمیل معتبر نیست.";
        } elseif (strlen($password) < 8) {
            $error = "❌ رمز عبور باید حداقل 8 کاراکتر باشد.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = "❌ رمز عبور باید حداقل یک حرف بزرگ داشته باشد.";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = "❌ رمز عبور باید حداقل یک عدد داشته باشد.";
        } elseif ($password != $confirm_password) {
            $error = "❌ رمز عبور با تکرار آن مطابقت ندارد.";
        } else {
            // بررسی تکراری نبودن
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->execute([$username, $email]);
            if ($check->fetch()) {
                $error = "❌ نام کاربری یا ایمیل قبلاً ثبت شده است.";
            } else {
                // ثبت کاربر جدید
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'guest')");
                
                if ($stmt->execute([$username, $email, $hashed_password])) {
                    // پاک کردن سشن کپچا
                    unset($_SESSION['captcha_code']);
                    
                    // هدایت به صفحه ورود با پیام موفقیت
                    $_SESSION['register_success'] = "✅ ثبت نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.";
                    header("Location: login.php");
                    exit;
                } else {
                    $error = "❌ خطا در ثبت نام. مجدداً تلاش کنید.";
                }
            }
        }
    }
}

// تولید کد کپچا
$captcha_code = rand(10000, 99999);
$_SESSION['register_captcha'] = $captcha_code;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت نام | AI Productivity Strategy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tahoma', sans-serif; background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; display: flex; justify-content: center; align-items: center; direction: rtl; padding: 20px; }
        .register-container { width: 100%; max-width: 500px; }
        .register-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .register-header { text-align: center; margin-bottom: 30px; }
        .register-header i { font-size: 60px; color: #667eea; }
        .register-header h2 { margin-top: 10px; color: #333; }
        .register-header p { color: #666; font-size: 14px; }
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }
        .input-group input { width: 100%; padding: 14px 45px 14px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; transition: 0.3s; }
        .input-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .captcha-box { display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
        .captcha-code { background: #f0f0f0; padding: 14px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; border-radius: 10px; flex: 1; font-family: monospace; }
        .captcha-refresh { background: #667eea; color: white; border: none; padding: 14px 15px; border-radius: 10px; cursor: pointer; transition: 0.3s; }
        .captcha-refresh:hover { background: #5a67d8; }
        .btn-primary { width: 100%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 14px; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        .error { background: #fee; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border-right: 3px solid #c33; }
        .login-link { text-align: center; margin-top: 20px; }
        .login-link a { color: #667eea; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
        .password-strength { font-size: 12px; margin-top: 5px; }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        hr { margin: 20px 0; border: none; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h2>ثبت نام</h2>
            <p>لطفاً اطلاعات زیر را وارد کنید</p>
        </div>
        
        <?php if($error): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="نام کاربری (3-20 کاراکتر)" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="ایمیل" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="رمز عبور (حداقل 8 کاراکتر)" required>
                <div class="password-strength" id="passwordStrength"></div>
            </div>
            
            <div class="input-group">
                <i class="fas fa-check-circle"></i>
                <input type="password" name="confirm_password" placeholder="تکرار رمز عبور" required>
            </div>
            
            <div class="captcha-box">
                <div class="captcha-code"><?= $captcha_code ?></div>
                <button type="button" class="captcha-refresh" onclick="refreshCaptcha()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            
            <div class="input-group">
                <i class="fas fa-shield-alt"></i>
                <input type="text" name="captcha" placeholder="کد امنیتی را وارد کنید" required>
            </div>
            
            <button type="submit" class="btn-primary">ثبت نام</button>
        </form>
        
        <hr>
        
        <div class="login-link">
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> قبلاً ثبت نام کرده‌اید؟ وارد شوید</a>
        </div>
    </div>
</div>

<script>
function refreshCaptcha() {
    fetch('refresh_register_captcha.php')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.captcha-code').innerHTML = data.captcha;
        });
}

document.getElementById('password').addEventListener('input', function() {
    var password = this.value;
    var strengthDiv = document.getElementById('passwordStrength');
    
    if (password.length == 0) {
        strengthDiv.innerHTML = '';
        return;
    }
    
    var strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    if (strength <= 1) {
        strengthDiv.innerHTML = '● ضعیف';
        strengthDiv.className = 'password-strength strength-weak';
    } else if (strength == 2) {
        strengthDiv.innerHTML = '●● متوسط';
        strengthDiv.className = 'password-strength strength-medium';
    } else {
        strengthDiv.innerHTML = '●●● قوی';
        strengthDiv.className = 'password-strength strength-strong';
    }
});
</script>
</body>
</html>