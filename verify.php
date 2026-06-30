<?php
// فایل: verify.php

session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['login_email'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_code = $_POST['code'];
    if ($user_code == $_SESSION['login_code'] && time() < $_SESSION['code_expiry']) {
        $email = $_SESSION['login_email'];
        $stmt = $pdo->prepare("SELECT id, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $user['role'];
        
        unset($_SESSION['login_code'], $_SESSION['login_email'], $_SESSION['code_expiry']);
        header("Location: index.php");
        exit;
    } else {
        $error = "کد نامعتبر یا منقضی شده است.";
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تایید کد | AI Productivity Strategy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-key"></i>
                <h2>تایید کد</h2>
                <p>کد ۶ رقمی ارسال شده را وارد کنید</p>
            </div>
            <form method="post" class="auth-form">
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="text" name="code" placeholder="_ _ _ _ _ _" maxlength="6" autocomplete="off" required>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check-circle"></i> تایید و ورود
                </button>
            </form>
            <?php if(isset($error)): ?>
                <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>