<?php
// فایل: reset_password.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/db.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    $error = "❌ لینک نامعتبر است. توکن یافت نشد.";
} else {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        $error = "❌ لینک نامعتبر است. توکن در دیتابیس وجود ندارد.";
    } elseif ($reset['expires_at'] < date('Y-m-d H:i:s')) {
        $error = "❌ لینک منقضی شده است. لطفاً دوباره درخواست بازیابی کنید.";
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            if (strlen($password) < 8) {
                $error = "❌ رمز عبور باید حداقل 8 کاراکتر باشد.";
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $error = "❌ رمز عبور باید حداقل یک حرف بزرگ داشته باشد.";
            } elseif (!preg_match('/[0-9]/', $password)) {
                $error = "❌ رمز عبور باید حداقل یک عدد داشته باشد.";
            } elseif ($password != $confirm_password) {
                $error = "❌ رمز عبور با تکرار آن مطابقت ندارد.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $reset['email']]);
                
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);
                
                $_SESSION['reset_success'] = "✅ رمز عبور با موفقیت تغییر کرد. اکنون می‌توانید وارد شوید.";
                header("Location: login.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بازنشانی رمز عبور | AI Productivity Strategy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tahoma', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            direction: rtl;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 450px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header i {
            font-size: 60px;
            color: #667eea;
        }
        
        .header h2 {
            margin-top: 10px;
            color: #333;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group input {
            width: 100%;
            padding: 14px;
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
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-right: 3px solid #28a745;
        }
        
        .password-strength {
            font-size: 12px;
            margin-top: 8px;
        }
        
        .strength-weak {
            color: #dc3545;
        }
        
        .strength-medium {
            color: #ffc107;
        }
        
        .strength-strong {
            color: #28a745;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="header">
            <i class="fas fa-lock-open"></i>
            <h2>بازنشانی رمز عبور</h2>
        </div>
        
        <?php if($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if(empty($error) || (strpos($error, 'نامعتبر') === false && strpos($error, 'منقضی') === false)): ?>
            <?php if(empty($error)): ?>
            <form method="post">
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder="رمز عبور جدید (حداقل 8 کاراکتر)" required>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="تکرار رمز عبور" required>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> تغییر رمز عبور
                </button>
            </form>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login.php">
                <i class="fas fa-arrow-right"></i> بازگشت به صفحه ورود
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('password')?.addEventListener('input', function() {
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