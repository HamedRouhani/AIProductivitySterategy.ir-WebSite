<?php
// فایل: my_messages.php

session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$page_title = 'پیام‌های من | AI Productivity Strategy';

// اگر کاربر وارد نشده، به صفحه ورود هدایت شود
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// دریافت پیام‌های کاربر
$stmt = $pdo->prepare("SELECT * FROM contacts WHERE email = ? ORDER BY created_at DESC");
$stmt->execute([$user_email]);
$messages = $stmt->fetchAll();

include 'header.php';
?>

<style>
    .messages-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .page-header h1 {
        color: #2d3748;
        font-size: 1.8rem;
    }
    
    .page-header p {
        color: #718096;
    }
    
    .message-card {
        background: white;
        border-radius: 20px;
        margin-bottom: 1.5rem;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }
    
    .message-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .message-subject {
        font-weight: bold;
    }
    
    .message-date {
        font-size: 0.8rem;
        opacity: 0.9;
    }
    
    .message-body {
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .message-text {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 12px;
        line-height: 1.7;
        color: #4a5568;
    }
    
    .reply-section {
        padding: 1.5rem;
        background: #f0fdf4;
        border-top: 2px solid #28a745;
    }
    
    .reply-title {
        color: #28a745;
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .reply-text {
        background: white;
        padding: 1rem;
        border-radius: 12px;
        line-height: 1.7;
        color: #2d3748;
    }
    
    .no-messages {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 20px;
    }
    
    .no-messages i {
        font-size: 3rem;
        color: #ccc;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .message-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="messages-container">
    <div class="page-header">
        <h1><i class="fas fa-envelope"></i> پیام‌های من</h1>
        <p>پیام‌های ارسال شده و پاسخ‌های دریافتی</p>
    </div>
    
    <?php if(count($messages) > 0): ?>
        <?php foreach($messages as $msg): ?>
            <div class="message-card">
                <div class="message-header">
                    <span class="message-subject">
                        <i class="fas fa-tag"></i> 
                        <?= !empty($msg['subject']) ? htmlspecialchars($msg['subject']) : 'بدون موضوع' ?>
                    </span>
                    <span class="message-date">
                        <i class="fas fa-calendar"></i> <?= date('Y/m/d H:i', strtotime($msg['created_at'])) ?>
                    </span>
                </div>
                
                <div class="message-body">
                    <div class="message-text">
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    </div>
                </div>
                
                <?php if(!empty($msg['reply'])): ?>
                    <div class="reply-section">
                        <div class="reply-title">
                            <i class="fas fa-reply-all"></i> پاسخ تیم پشتیبانی:
                        </div>
                        <div class="reply-text">
                            <?= nl2br(htmlspecialchars($msg['reply'])) ?>
                        </div>
                        <?php if($msg['replied_at']): ?>
                            <small style="display: block; margin-top: 10px; color: #888;">
                                تاریخ پاسخ: <?= date('Y/m/d H:i', strtotime($msg['replied_at'])) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="padding: 1rem 1.5rem; background: #fff3cd; border-top: 1px solid #ffeeba;">
                        <span style="color: #856404;">
                            <i class="fas fa-clock"></i> در انتظار پاسخ تیم پشتیبانی...
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-messages">
            <i class="fas fa-inbox"></i>
            <p>شما هنوز پیامی ارسال نکرده‌اید.</p>
            <a href="contact.php" class="btn-primary" style="display: inline-block; margin-top: 1rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 10px 20px; border-radius: 50px; text-decoration: none;">
                <i class="fas fa-paper-plane"></i> ارسال پیام جدید
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>