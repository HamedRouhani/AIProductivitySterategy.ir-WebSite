<?php
// ========================================
// فایل: header.php
// تنظیمات سراسری سایت
// ========================================

// تنظیمات پیش‌فرض متا تگ‌ها
if (!isset($page_title)) {
    $page_title = 'AI Productivity Strategy | بهره‌وری با هوش مصنوعی';
}

if (!isset($meta_description)) {
    $meta_description = 'آموزش و استراتژی‌های بهره‌وری با هوش مصنوعی - افزایش کارایی فردی و سازمانی با استفاده از ابزارهای پیشرفته هوش مصنوعی';
}

if (!isset($meta_keywords)) {
    $meta_keywords = 'هوش مصنوعی, بهره‌وری, AI, استراتژی, productivity, artificial intelligence, بهینه‌سازی, یادگیری ماشین';
}

if (!isset($og_title)) {
    $og_title = 'AI Productivity Strategy - بهره‌وری با هوش مصنوعی';
}

if (!isset($og_description)) {
    $og_description = 'استراتژی‌های بهره‌وری و تحول دیجیتال با استفاده از هوش مصنوعی';
}

if (!isset($canonical_url)) {
    $canonical_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

if (isset($pdo) && $pdo instanceof PDO) {
    // اگر کاربر ادمین نیست، بازدید او ثبت شود
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        if (file_exists(__DIR__ . '/visit_tracker.php')) {
            require_once __DIR__ . '/visit_tracker.php';
            // تابع logVisit به صورت خودکار در فایل tracker اجرا می‌شود
        }
    }
}

$og_image = 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/og-image.jpg';
$site_name = 'AI Productivity Strategy';
$theme_color = '#667eea';
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <!-- ======================================== -->
    <!-- ۱. متا تگ‌های اصلی و پایه -->
    <!-- ======================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <meta name="googlebot" content="index, follow">
    <meta name="theme-color" content="<?= $theme_color ?>">
    
    <!-- ======================================== -->
    <!-- ۲. لینک‌های کنونیکال و زبان -->
    <!-- ======================================== -->
    <link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>">
    <link rel="alternate" hreflang="fa" href="<?= htmlspecialchars($canonical_url) ?>">
    
    <!-- ======================================== -->
    <!-- ۳. Open Graph (فیسبوک، لینکدین و ...) -->
    <!-- ======================================== -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($og_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($og_description) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical_url) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $site_name ?>">
    <meta property="og:locale" content="fa_IR">
    
    <!-- ======================================== -->
    <!-- ۴. Twitter Card -->
    <!-- ======================================== -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($og_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($og_description) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta name="twitter:site" content="@AIPStrategy">
    <meta name="twitter:creator" content="@AIPStrategy">
    
    <!-- ======================================== -->
    <!-- ۵. Structured Data (JSON-LD) -->
    <!-- ======================================== -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?= $site_name ?>",
        "url": "https://<?= $_SERVER['HTTP_HOST'] ?>",
        "description": "<?= htmlspecialchars($meta_description) ?>",
        "inLanguage": "fa-IR",
        "publisher": {
            "@type": "Organization",
            "name": "<?= $site_name ?>",
            "logo": {
                "@type": "ImageObject",
                "url": "https://<?= $_SERVER['HTTP_HOST'] ?>/assets/images/logo.png"
            }
        }
    }
    </script>

    <?php if (isset($post) && is_array($post)): ?>
    <!-- Structured Data برای مقالات -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "<?= htmlspecialchars($post['title']) ?>",
        "description": "<?= htmlspecialchars(substr(strip_tags($post['content']), 0, 160)) ?>",
        "image": "<?= htmlspecialchars($post['image'] ?? $og_image) ?>",
        "datePublished": "<?= date('Y-m-d', strtotime($post['created_at'])) ?>",
        "dateModified": "<?= date('Y-m-d', strtotime($post['updated_at'] ?? $post['created_at'])) ?>",
        "author": {
            "@type": "Person",
            "name": "<?= htmlspecialchars($post['author_name'] ?? 'AI Productivity Strategy') ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "<?= $site_name ?>"
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?= htmlspecialchars($canonical_url) ?>"
        }
    }
    </script>
    <?php endif; ?>
    
    <!-- ======================================== -->
    <!-- ۶. فونت Vazirmatn (اصلاح‌شده) -->
    <!-- ======================================== -->
    <!-- Preconnect برای بهبود سرعت -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- بارگذاری فونت Vazirmatn -->
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- ======================================== -->
    <!-- ۷. استایل‌های اصلی -->
    <!-- ======================================== -->
    <style>
        /* ======================================== */
        /* استایل‌های پایه و سراسری */
        /* ======================================== */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* ======================================== */
        /* تنظیم فونت Vazirmatn برای کل سایت */
        /* ======================================== */
        body {
            font-family: 'Vazirmatn', 'Tahoma', 'Segoe UI', system-ui, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
            direction: rtl;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* اعمال فونت به همه عناصر */
        h1, h2, h3, h4, h5, h6,
        p, span, div, a, li,
        button, input, textarea,
        label, table, td, th,
        .navbar, .footer, .post-content,
        .menu-link, .post-title {
            font-family: 'Vazirmatn', 'Tahoma', 'Segoe UI', system-ui, sans-serif !important;
        }
        
        /* ======================================== */
        /* استایل نوار ناوبری */
        /* ======================================== */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-weight: 700;
        }
        
        .logo i {
            font-size: 28px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .logo span {
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 28px;
            color: #667eea;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 1.5rem;
            align-items: center;
        }
        
        .nav-menu li a {
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
        }
        
        .nav-menu li a:hover {
            color: #667eea;
            transform: translateY(-1px);
        }
        
        .nav-menu .login-btn,
        .nav-menu .logout-btn,
        .nav-menu .admin-btn,
        .nav-menu .messages-btn {
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .nav-menu .login-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .nav-menu .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .nav-menu .logout-btn {
            background: #e53e3e;
        }
        
        .nav-menu .logout-btn:hover {
            background: #c53030;
            transform: translateY(-2px);
            color: white;
        }
        
        .nav-menu .admin-btn {
            background: #38a169;
        }
        
        .nav-menu .admin-btn:hover {
            background: #2f855a;
            transform: translateY(-2px);
            color: white;
        }
        
        .nav-menu .messages-btn {
            background: #17a2b8;
        }
        
        .nav-menu .messages-btn:hover {
            background: #138496;
            transform: translateY(-2px);
            color: white;
        }
        
        /* ======================================== */
        /* استایل فوتر */
        /* ======================================== */
        .footer {
            background: #1a1a2e;
            color: white;
            text-align: center;
            padding: 2.5rem 2rem;
            margin-top: 3rem;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer p {
            margin: 10px 0;
            font-size: 14px;
            opacity: 0.8;
            font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
        }
        
        .social-links {
            margin-top: 15px;
        }
        
        .social-links a {
            color: white;
            margin: 0 10px;
            font-size: 1.4rem;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
        }
        
        .social-links a:hover {
            transform: translateY(-3px);
            color: #667eea;
        }
        
        .main-wrapper {
            min-height: calc(100vh - 140px);
        }
        
        /* ======================================== */
        /* استایل‌های ریسپانسیو */
        /* ======================================== */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0.8rem 1rem;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .nav-menu {
                display: none;
                width: 100%;
                flex-direction: column;
                gap: 0;
                margin-top: 1rem;
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
                padding: 0.5rem 0;
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .nav-menu li {
                width: 100%;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .nav-menu li:last-child {
                border-bottom: none;
            }
            
            .nav-menu li a {
                padding: 1rem 1.5rem;
                width: 100%;
                justify-content: flex-start;
                border-radius: 0;
            }
            
            .nav-menu .login-btn,
            .nav-menu .logout-btn,
            .nav-menu .admin-btn,
            .nav-menu .messages-btn {
                border-radius: 0;
                justify-content: center;
            }
            
            .logo span {
                font-size: 0.9rem;
            }
            
            .logo i {
                font-size: 22px;
            }
        }
        
        @media (max-width: 480px) {
            .nav-container {
                padding: 0.6rem 0.8rem;
            }
            
            .logo span {
                font-size: 0.8rem;
            }
            
            .logo i {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- ======================================== -->
    <!-- نوار ناوبری -->
    <!-- ======================================== -->
    <nav class="navbar" role="navigation" aria-label="منوی اصلی">
        <div class="nav-container">
            <a href="/" class="logo" rel="home" title="صفحه اصلی AI Productivity Strategy">
                <i class="fas fa-brain" aria-hidden="true"></i>
                <span>AI Productivity Strategy</span>
            </a>
            
            <button class="menu-toggle" id="menuToggle" aria-label="تغییر وضعیت منو" aria-expanded="false">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            
            <ul class="nav-menu" id="navMenu" role="menubar">
                <li role="none">
                    <a href="/" role="menuitem">
                        <i class="fas fa-home" aria-hidden="true"></i>
                        خانه
                    </a>
                </li>
                <li role="none">
                    <a href="/about" role="menuitem">
                        <i class="fas fa-users" aria-hidden="true"></i>
                        درباره ما
                    </a>
                </li>
                <li role="none">
                    <a href="/contact" role="menuitem">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        تماس با ما
                    </a>
                </li>
                <li role="none">
                    <a href="/software" role="menuitem">
                        <i class="fas fa-toolbox" aria-hidden="true"></i>
                        نرم‌افزارها
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li role="none">
                            <a href="/admin" class="admin-btn" role="menuitem">
                                <i class="fas fa-cog" aria-hidden="true"></i>
                                پنل مدیریت
                            </a>
                        </li>
                        <li role="none">
                            <a href="/admin_messages" class="messages-btn" role="menuitem">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                مدیریت پیام‌ها
                            </a>
                        </li>
                    <?php else: ?>
                        <li role="none">
                            <a href="/my_messages" class="messages-btn" role="menuitem">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                پیام‌های من
                            </a>
                        </li>
                    <?php endif; ?>
                    <li role="none">
                        <a href="/logout" class="logout-btn" role="menuitem">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                            خروج
                        </a>
                    </li>
                <?php else: ?>
                    <li role="none">
                        <a href="/login" class="login-btn" role="menuitem">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            ورود
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <!-- ======================================== -->
    <!-- محتوای اصلی -->
    <!-- ======================================== -->
    <main class="main-wrapper">