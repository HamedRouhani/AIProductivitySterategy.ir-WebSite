<?php
// فایل: admin_messages.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/mailer.php';

$page_title = 'مدیریت پیام‌ها | AI Productivity Strategy';

if (!isAdmin()) {
    header("HTTP/1.0 403 Forbidden");
    die("دسترسی غیرمجاز");
}

$success = '';
$error = '';

// پاسخ به پیام
if (isset($_POST['reply_message'])) {
    $contact_id = intval($_POST['contact_id']);
    $reply_text = trim($_POST['reply_text']);
    $user_email = trim($_POST['user_email']);
    $user_name = trim($_POST['user_name']);
    
    if (empty($reply_text)) {
        $error = "متن پاسخ نمی‌تواند خالی باشد.";
    } else {
        try {
            // ذخیره پاسخ در دیتابیس
            $stmt = $pdo->prepare("INSERT INTO contact_replies (contact_id, reply, replied_by) VALUES (?, ?, ?)");
            $stmt->execute([$contact_id, $reply_text, $_SESSION['user_id']]);
            
            // به‌روزرسانی وضعیت پیام اصلی
            $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1, reply = ?, replied_at = NOW(), replied_by = ? WHERE id = ?");
            $stmt->execute([$reply_text, $_SESSION['user_id'], $contact_id]);
            
            // ارسال ایمیل پاسخ به کاربر
            $subject = "پاسخ به پیام شما | AI Productivity Strategy";
            $message = "
            <!DOCTYPE html>
            <html dir='rtl'>
            <head><meta charset='UTF-8'></head>
            <body style='font-family:Tahoma; padding:20px;'>
                <div style='max-width:600px; margin:auto; background:#f8f9fa; border-radius:15px; overflow:hidden;'>
                    <div style='background:linear-gradient(135deg, #667eea, #764ba2); color:white; padding:20px; text-align:center;'>
                        <h2>پاسخ به پیام شما</h2>
                    </div>
                    <div style='padding:25px;'>
                        <p>سلام <strong>" . htmlspecialchars($user_name) . "</strong>،</p>
                        <p>پاسخ تیم پشتیبانی به پیام شما:</p>
                        <div style='background:#e9ecef; padding:15px; border-radius:10px; margin:15px 0;'>
                            " . nl2br(htmlspecialchars($reply_text)) . "
                        </div>
                        <p>با تشکر از ارتباط شما با ما.</p>
                        <hr>
                        <small>AI Productivity Strategy</small>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            sendContactReplyEmail($user_email, $subject, $message);
            
            $success = "پاسخ با موفقیت ارسال شد.";
            header("Location: admin_messages.php");
            exit;
            
        } catch (PDOException $e) {
            $error = "خطا: " . $e->getMessage();
        }
    }
}

// حذف پیام
if (isset($_GET['delete_msg'])) {
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([$_GET['delete_msg']]);
    header("Location: admin_messages.php");
    exit;
}

// دریافت پیام‌ها
$messages = $pdo->query("SELECT c.*, u.username as admin_name 
                         FROM contacts c 
                         LEFT JOIN users u ON c.replied_by = u.id 
                         ORDER BY c.created_at DESC")->fetchAll();

include 'header.php';
?>

<style>
    .messages-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .admin-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 1.5rem;
        border-radius: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .admin-header h1 {
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .admin-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 8px;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 8px;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .stat-box {
        flex: 1;
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 12px;
    }
    
    .stat-box i {
        font-size: 2rem;
        color: #667eea;
    }
    
    .stat-box .count {
        font-size: 1.8rem;
        font-weight: bold;
        color: #2d3748;
    }
    
    .stat-box .label {
        color: #718096;
    }
    
    .message-card {
        background: white;
        border-radius: 15px;
        margin-bottom: 1.5rem;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .message-header {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .message-status {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .badge-unread {
        background: #dc3545;
        color: white;
    }
    
    .badge-read {
        background: #28a745;
        color: white;
    }
    
    .badge-replied {
        background: #17a2b8;
        color: white;
    }
    
    .message-body {
        padding: 1.5rem;
    }
    
    .message-info {
        margin-bottom: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 0.85rem;
        color: #718096;
    }
    
    .message-info i {
        width: 20px;
        color: #667eea;
    }
    
    .message-content {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        line-height: 1.7;
    }
    
    .message-reply {
        background: #e8f0fe;
        padding: 1rem;
        border-radius: 10px;
        margin-top: 1rem;
        border-right: 3px solid #667eea;
    }
    
    .reply-form {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
        display: none;
    }
    
    .reply-form.active {
        display: block;
    }
    
    .reply-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: inherit;
        resize: vertical;
        min-height: 100px;
    }
    
    .btn-send {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 10px;
    }
    
    .btn-send:hover {
        background: #218838;
    }
    
    .btn-reply {
        background: #17a2b8;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
    }
    
    .btn-reply:hover {
        background: #138496;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-delete:hover {
        background: #c82333;
    }
    
    @media (max-width: 768px) {
        .stats-card {
            flex-direction: column;
        }
        
        .message-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="messages-container">
    <div class="admin-header">
        <h1><i class="fas fa-envelope"></i> مدیریت پیام‌ها</h1>
    </div>
    
    <?php if($success): ?>
        <div class="success" style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 1rem;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="error" style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 1rem;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <!-- آمار -->
    <?php
    $total = count($messages);
    $unread = count(array_filter($messages, function($m) { return $m['is_read'] == 0; }));
    $replied = count(array_filter($messages, function($m) { return !empty($m['reply']); }));
    ?>
    <div class="stats-card">
        <div class="stat-box">
            <i class="fas fa-envelope"></i>
            <div class="count"><?= $total ?></div>
            <div class="label">کل پیام‌ها</div>
        </div>
        <div class="stat-box">
            <i class="fas fa-clock"></i>
            <div class="count"><?= $unread ?></div>
            <div class="label">خوانده نشده</div>
        </div>
        <div class="stat-box">
            <i class="fas fa-reply-all"></i>
            <div class="count"><?= $replied ?></div>
            <div class="label">پاسخ داده شده</div>
        </div>
    </div>
    
    <!-- لیست پیام‌ها -->
    <?php if(count($messages) > 0): ?>
        <?php foreach($messages as $msg): ?>
            <div class="message-card" id="msg-<?= $msg['id'] ?>">
                <div class="message-header">
                    <div class="message-status">
                        <strong><?= htmlspecialchars($msg['name']) ?></strong>
                        <?php if($msg['is_read'] == 0): ?>
                            <span class="badge badge-unread">جدید</span>
                        <?php elseif(!empty($msg['reply'])): ?>
                            <span class="badge badge-replied">پاسخ داده شده</span>
                        <?php else: ?>
                            <span class="badge badge-read">خوانده شده</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="?delete_msg=<?= $msg['id'] ?>" class="btn-delete" onclick="return confirm('آیا از حذف این پیام مطمئن هستید؟')">
                            <i class="fas fa-trash"></i> حذف
                        </a>
                    </div>
                </div>
                
                <div class="message-body">
                    <div class="message-info">
                        <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($msg['email']) ?></span>
                        <span><i class="fas fa-calendar"></i> <?= date('Y/m/d H:i', strtotime($msg['created_at'])) ?></span>
                        <?php if($msg['subject']): ?>
                            <span><i class="fas fa-tag"></i> <?= htmlspecialchars($msg['subject']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    </div>
                    
                    <?php if(!empty($msg['reply'])): ?>
                        <div class="message-reply">
                            <strong><i class="fas fa-reply"></i> پاسخ ارسال شده:</strong>
                            <div style="margin-top: 8px;"><?= nl2br(htmlspecialchars($msg['reply'])) ?></div>
                            <small style="display: block; margin-top: 8px; color: #888;">
                                تاریخ پاسخ: <?= date('Y/m/d H:i', strtotime($msg['replied_at'])) ?>
                            </small>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <button class="btn-reply" onclick="toggleReplyForm(<?= $msg['id'] ?>)">
                            <i class="fas fa-reply"></i> پاسخ به پیام
                        </button>
                    </div>
                    
                    <div class="reply-form" id="reply-form-<?= $msg['id'] ?>">
                        <form method="post">
                            <input type="hidden" name="contact_id" value="<?= $msg['id'] ?>">
                            <input type="hidden" name="user_email" value="<?= htmlspecialchars($msg['email']) ?>">
                            <input type="hidden" name="user_name" value="<?= htmlspecialchars($msg['name']) ?>">
                            <textarea name="reply_text" rows="4" placeholder="متن پاسخ خود را وارد کنید..." required></textarea>
                            <div>
                                <button type="submit" name="reply_message" class="btn-send">
                                    <i class="fas fa-paper-plane"></i> ارسال پاسخ
                                </button>
                                <button type="button" class="btn-secondary" onclick="toggleReplyForm(<?= $msg['id'] ?>)">
                                    انصراف
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="admin-card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <p style="margin-top: 1rem; color: #999;">هیچ پیامی یافت نشد.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleReplyForm(msgId) {
    var form = document.getElementById('reply-form-' + msgId);
    if (form.classList.contains('active')) {
        form.classList.remove('active');
    } else {
        // بستن سایر فرم‌ها
        var allForms = document.querySelectorAll('.reply-form');
        allForms.forEach(function(f) {
            f.classList.remove('active');
        });
        form.classList.add('active');
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>

<?php include 'footer.php'; ?>