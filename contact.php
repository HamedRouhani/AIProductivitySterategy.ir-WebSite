<?php
// فایل: contact.php

// ========================================
// تنظیمات صفحه تماس با ما
// ========================================
session_start();
require_once 'includes/db.php';

$page_title = 'تماس با ما | ارتباط با تیم AI Productivity Strategy';
$meta_description = 'ارتباط با تیم AI Productivity Strategy - مشاوره و همکاری در زمینه بهره‌وری و هوش مصنوعی. فرم تماس، آدرس، تلفن و شبکه‌های اجتماعی';
$meta_keywords = 'تماس با ما, ارتباط با تیم, مشاوره هوش مصنوعی, بهره‌وری, فرم تماس';
$og_title = 'تماس با AI Productivity Strategy';
$og_description = 'ارتباط با تیم متخصصان بهره‌وری و هوش مصنوعی - مشاوره، همکاری و پشتیبانی';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_required'] = 'برای ارسال پیام باید ابتدا وارد شوید.';
    header('Location: /login');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    // اعتبارسنجی
    $errors = [];
    if (empty($name)) {
        $errors[] = 'نام و نام خانوادگی الزامی است.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ایمیل معتبر الزامی است.';
    }
    if (empty($message)) {
        $errors[] = 'متن پیام الزامی است.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($stmt->execute([$name, $email, $subject, $message, $_SESSION['user_id']])) {
                $success = '✅ پیام شما با موفقیت ارسال شد. به زودی با شما تماس می‌گیریم.';
                
                // ارسال ایمیل (در صورت نیاز)
                // mail('info@aiproductivitystrategy.ir', 'پیام جدید از سایت', "نام: $name\nایمیل: $email\nموضوع: $subject\nپیام: $message");
            } else {
                $errors[] = 'خطا در ارسال پیام. لطفاً دوباره تلاش کنید.';
            }
        } catch (PDOException $e) {
            $errors[] = 'خطا در ارتباط با پایگاه داده. لطفاً بعداً تلاش کنید.';
            error_log('Contact error: ' . $e->getMessage());
        }
    }
    
    if (!empty($errors)) {
        $error = '❌ ' . implode(' ', $errors);
    }
}

include 'header.php';
?>

<style>
    /* ======================================== */
    /* استایل‌های اختصاصی صفحه تماس */
    /* ======================================== */
    .contact-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }
    
    /* Breadcrumb */
    .breadcrumb {
        padding: 0.8rem 0;
        margin-bottom: 2rem;
        color: #4a5568;
        font-size: 0.9rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .breadcrumb a {
        color: #667eea;
        text-decoration: none;
    }
    
    .breadcrumb a:hover {
        text-decoration: underline;
    }
    
    .contact-wrapper {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 2.5rem;
    }
    
    /* اطلاعات تماس */
    .contact-info {
        background: white;
        border-radius: 25px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }
    
    .contact-info h2 {
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
        border-right: 4px solid #667eea;
        padding-right: 1rem;
        font-weight: 700;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 15px;
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }
    
    .info-item:hover {
        transform: translateX(-5px);
        background: #f0f0f0;
    }
    
    .info-item i {
        font-size: 1.5rem;
        color: #667eea;
        width: 40px;
        text-align: center;
        flex-shrink: 0;
    }
    
    .info-item .info-text {
        display: flex;
        flex-direction: column;
    }
    
    .info-item .info-label {
        font-size: 0.8rem;
        color: #a0aec0;
        font-weight: 500;
    }
    
    .info-item .info-value {
        color: #2d3748;
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    .contact-social {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .contact-social a {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 1.2rem;
    }
    
    .contact-social a:hover {
        transform: translateY(-5px) rotate(5deg);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .contact-social a:focus {
        outline: 2px solid #667eea;
        outline-offset: 2px;
    }
    
    /* فرم تماس */
    .contact-form-box {
        background: white;
        border-radius: 25px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }
    
    .contact-form-box h2 {
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
        border-right: 4px solid #667eea;
        padding-right: 1rem;
        font-weight: 700;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #2d3748;
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    .form-group label .required {
        color: #e53e3e;
        margin-right: 3px;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        font-family: inherit;
        transition: all 0.3s ease;
        background: #f8fafc;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        background: white;
    }
    
    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #a0aec0;
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 14px 30px;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    .btn-submit:active {
        transform: translateY(0);
    }
    
    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    /* اعلان‌ها */
    .alert {
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        text-align: center;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .alert-error {
        background: #fee;
        color: #c33;
        border-right: 4px solid #c33;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border-right: 4px solid #28a745;
    }
    
    .alert i {
        font-size: 1.2rem;
    }
    
    /* نقشه */
    .map-container {
        margin-top: 2.5rem;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }
    
    .map-container iframe {
        width: 100%;
        height: 350px;
        border: none;
        display: block;
    }
    
    /* ======================================== */
    /* استایل‌های ریسپانسیو */
    /* ======================================== */
    @media (max-width: 992px) {
        .contact-wrapper {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .contact-info {
            order: 2;
        }
        
        .contact-form-box {
            order: 1;
        }
    }
    
    @media (max-width: 768px) {
        .contact-container {
            padding: 0 1rem;
        }
        
        .contact-info,
        .contact-form-box {
            padding: 1.5rem;
        }
        
        .info-item {
            padding: 0.8rem;
        }
        
        .map-container iframe {
            height: 250px;
        }
    }
    
    @media (max-width: 480px) {
        .info-item {
            flex-direction: column;
            text-align: center;
            padding: 1rem;
        }
        
        .info-item i {
            width: auto;
            font-size: 1.8rem;
        }
        
        .contact-social a {
            width: 45px;
            height: 45px;
            font-size: 1rem;
        }
        
        .btn-submit {
            font-size: 14px;
            padding: 12px 20px;
        }
        
        .form-group input,
        .form-group textarea {
            padding: 12px 14px;
            font-size: 13px;
        }
    }
</style>

<!-- ======================================== -->
<!-- محتوای صفحه تماس -->
<!-- ======================================== -->
<div class="contact-container">
    
    <!-- مسیر راهنما (Breadcrumb) -->
    <nav class="breadcrumb" aria-label="مسیر راهنما">
        <a href="/">خانه</a>
        <span> / </span>
        <span>تماس با ما</span>
    </nav>
    
    <div class="contact-wrapper">
        <!-- اطلاعات تماس -->
        <aside class="contact-info">
            <h2><i class="fas fa-address-card" aria-hidden="true"></i> اطلاعات تماس</h2>
            
            <a href="https://www.google.com/maps/search/تهران" target="_blank" rel="noopener noreferrer" class="info-item">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                <div class="info-text">
                    <span class="info-label">آدرس</span>
                    <span class="info-value">تهران، ایران</span>
                </div>
            </a>
            
            <a href="tel:+982112345678" class="info-item">
                <i class="fas fa-phone" aria-hidden="true"></i>
                <div class="info-text">
                    <span class="info-label">تلفن</span>
                    <span class="info-value">+98 21 1234 5678</span>
                </div>
            </a>
            
            <a href="mailto:info@aiproductivitystrategy.ir" class="info-item">
                <i class="fas fa-envelope" aria-hidden="true"></i>
                <div class="info-text">
                    <span class="info-label">ایمیل</span>
                    <span class="info-value">info@aiproductivitystrategy.ir</span>
                </div>
            </a>
            
            <div class="info-item">
                <i class="fas fa-clock" aria-hidden="true"></i>
                <div class="info-text">
                    <span class="info-label">ساعات کاری</span>
                    <span class="info-value">شنبه تا چهارشنبه: ۹ تا ۱۷</span>
                </div>
            </div>
            
            <div class="contact-social">
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="لینکدین">
                    <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="تلگرام">
                    <i class="fab fa-telegram-plane" aria-hidden="true"></i>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="اینستاگرام">
                    <i class="fab fa-instagram" aria-hidden="true"></i>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="توییتر">
                    <i class="fab fa-twitter" aria-hidden="true"></i>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="یوتیوب">
                    <i class="fab fa-youtube" aria-hidden="true"></i>
                </a>
            </div>
        </aside>
        
        <!-- فرم تماس -->
        <section class="contact-form-box">
            <h2><i class="fas fa-paper-plane" aria-hidden="true"></i> ارسال پیام</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error" role="alert">
                    <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="post" novalidate>
                <div class="form-group">
                    <label for="name">
                        نام و نام خانوادگی
                        <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           placeholder="نام و نام خانوادگی خود را وارد کنید"
                           required
                           value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>"
                           minlength="3"
                           maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="email">
                        ایمیل
                        <span class="required">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="example@email.com"
                           required
                           value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>"
                           maxlength="255">
                </div>
                
                <div class="form-group">
                    <label for="subject">موضوع</label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           placeholder="موضوع پیام خود را وارد کنید"
                           maxlength="200"
                           value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="message">
                        متن پیام
                        <span class="required">*</span>
                    </label>
                    <textarea id="message" 
                              name="message" 
                              placeholder="متن پیام شما..."
                              rows="6"
                              required
                              minlength="10"
                              maxlength="5000"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-send" aria-hidden="true"></i>
                    ارسال پیام
                </button>
            </form>
        </section>
    </div>
    
    <!-- نقشه -->
    <div class="map-container">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2593.542175442983!2d51.389022!3d35.689197!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3f8e0143d3c6b4b9%3A0x1e5c3c5e5b5e5b5e!2sTehran!5e0!3m2!1sen!2sir!4v1700000000000!5m2!1sen!2sir" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade"
            title="نقشه موقعیت مکانی AI Productivity Strategy">
        </iframe>
    </div>
</div>

<?php include 'footer.php'; ?>