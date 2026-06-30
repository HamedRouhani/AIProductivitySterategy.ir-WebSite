<?php
// فایل: forget_password.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/db.php';
require_once 'includes/mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = "❌ لطفاً ایمیل خود را وارد کنید.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ فرمت ایمیل معتبر نیست.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$email, $token, $expires_at])) {
                $reset_link = "http://www.aiproductivitystrategy.ir/reset_password.php?token=" . $token;
                
                if (sendResetPasswordEmail($email, $reset_link)) {
                    $success = "✅ لینک بازیابی به ایمیل شما ارسال شد.";
                } else {
                    $error = "❌ خطا در ارسال ایمیل. لطفاً دوباره تلاش کنید.";
                }
            } else {
                $error = "❌ خطا در ذخیره اطلاعات. لطفاً دوباره تلاش کنید.";
            }
        } else {
            $error = "❌ ایمیلی با این آدرس در سایت ثبت نشده است.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بازیابی رمز عبور | AI Productivity Strategy</title>
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
        
        .header p {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
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
        
        hr {
            margin: 25px 0 20px;
            border: none;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="header">
            <i class="fas fa-key"></i>
            <h2>بازیابی رمز عبور</h2>
            <p>ایمیل خود را وارد کنید تا لینک بازیابی برای شما ارسال شود</p>
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
        
        <?php if(!$success): ?>
        <form method="post">
            <div class="input-group">
                <input type="email" name="email" placeholder="ایمیل خود را وارد کنید" required autofocus>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-paper-plane"></i> ارسال لینک بازیابی
            </button>
        </form>
        <?php endif; ?>
        
        <hr>
        
        <div class="back-link">
            <a href="login.php">
                <i class="fas fa-arrow-right"></i> بازگشت به صفحه ورود
            </a>
        </div>
    </div>
</div>
</body>
</html>