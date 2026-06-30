<?php
// file: index.php
// ========================================
// تنظیمات صفحه اصلی
// ========================================
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$page_title = 'AI Productivity Strategy | بهره‌وری و استراتژی با هوش مصنوعی';
$meta_description = 'آموزش و استراتژی‌های بهره‌وری با هوش مصنوعی - افزایش کارایی فردی و سازمانی با استفاده از ابزارهای پیشرفته هوش مصنوعی';
$meta_keywords = 'هوش مصنوعی, بهره‌وری, استراتژی, AI, productivity, artificial intelligence, بهینه‌سازی, یادگیری ماشین';
$og_title = 'AI Productivity Strategy - بهره‌وری با هوش مصنوعی';
$og_description = 'استراتژی‌های بهره‌وری و تحول دیجیتال با استفاده از هوش مصنوعی';

$menu_id = isset($_GET['menu']) ? (int)$_GET['menu'] : null;
$posts = [];
$menuTitle = '';
$debugInfo = '';

// ========================================
// دریافت پست‌های هر دسته (از اولین به آخرین)
// ========================================
if ($menu_id) {
    try {
        // ۱. بررسی وجود ستون status در جدول posts
        $checkColumn = $pdo->query("SHOW COLUMNS FROM posts LIKE 'status'");
        $hasStatusColumn = $checkColumn->rowCount() > 0;
        
        // ۲. دریافت پست‌ها (ترتیب صعودی - از قدیم به جدید)
        if (!$hasStatusColumn) {
            $stmt = $pdo->prepare("
                SELECT p.*, u.username as author_name, m.title as menu_title
                FROM posts p
                JOIN users u ON p.author_id = u.id
                LEFT JOIN menus m ON p.menu_id = m.id
                WHERE p.menu_id = ?
                ORDER BY p.created_at ASC
            ");
            $stmt->execute([$menu_id]);
            $posts = $stmt->fetchAll();
            
            if (count($posts) > 0) {
                $debugInfo = "ℹ️ ستون 'status' در جدول posts وجود ندارد. لطفاً برای مدیریت بهتر، آن را اضافه کنید.";
            }
        } else {
            $stmt = $pdo->prepare("
                SELECT p.*, u.username as author_name, m.title as menu_title
                FROM posts p
                JOIN users u ON p.author_id = u.id
                LEFT JOIN menus m ON p.menu_id = m.id
                WHERE p.menu_id = ? AND (p.status = 'published' OR p.status IS NULL OR p.status = '')
                ORDER BY p.created_at ASC
            ");
            $stmt->execute([$menu_id]);
            $posts = $stmt->fetchAll();
            
            if (count($posts) == 0) {
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE menu_id = ?");
                $checkStmt->execute([$menu_id]);
                $totalPosts = $checkStmt->fetchColumn();
                
                if ($totalPosts > 0) {
                    $debugInfo = "⚠️ تعداد $totalPosts پست در این دسته وجود دارد اما وضعیت آنها 'published' نیست. 
                                 لطفاً وضعیت پست‌ها را در دیتابیس به 'published' تغییر دهید.";
                    
                    $stmt = $pdo->prepare("
                        SELECT p.*, u.username as author_name, m.title as menu_title
                        FROM posts p
                        JOIN users u ON p.author_id = u.id
                        LEFT JOIN menus m ON p.menu_id = m.id
                        WHERE p.menu_id = ?
                        ORDER BY p.created_at ASC
                    ");
                    $stmt->execute([$menu_id]);
                    $posts = $stmt->fetchAll();
                } else {
                    $debugInfo = "ℹ️ هیچ پستی در این دسته یافت نشد.";
                }
            }
        }
        
        // ۳. دریافت عنوان دسته‌بندی
        $menuStmt = $pdo->prepare("SELECT title FROM menus WHERE id = ?");
        $menuStmt->execute([$menu_id]);
        $menuTitle = $menuStmt->fetchColumn();
        
        if (!$menuTitle) {
            $menuTitle = 'دسته‌بندی نامشخص';
        }
        
    } catch (PDOException $e) {
        error_log('Index error: ' . $e->getMessage());
        $debugInfo = '❌ خطا در ارتباط با پایگاه داده: ' . $e->getMessage();
    }
}

// ========================================
// دریافت منوها با ساختار درختی
// ========================================
try {
    $menus = $pdo->query("
        SELECT m1.*, 
               (SELECT COUNT(*) FROM menus m2 WHERE m2.parent_id = m1.id) as child_count
        FROM menus m1 
        ORDER BY m1.parent_id ASC, m1.menu_order ASC, m1.id ASC
    ")->fetchAll();
} catch (PDOException $e) {
    error_log('Menus error: ' . $e->getMessage());
    $menus = [];
}

// ========================================
// تابع بازگشتی برای نمایش منوها با تغییر رنگ و فونت (بدون ایندنت)
// ========================================
function displayMenusTree($menus, $parent_id = 0, $current_menu_id = null, $level = 0, $active_parents = []) {
    $html = '';
    
    // ========================================
    // تنظیمات بر اساس سطح - رنگ و سایز فونت
    // ========================================
    $configs = [
        0 => [
            'font_size' => '16px',
            'color' => '#1a202c',
            'bg' => '#e8f0fe',
            'border_color' => '#667eea',
            'font_weight' => '700',
            'icon' => 'fa-folder-open',
            'class' => 'level-root'
        ],
        1 => [
            'font_size' => '15px',
            'color' => '#2d3748',
            'bg' => '#f0f4f8',
            'border_color' => '#a0c4e8',
            'font_weight' => '600',
            'icon' => 'fa-folder',
            'class' => 'level-1'
        ],
        2 => [
            'font_size' => '14px',
            'color' => '#4a5568',
            'bg' => '#f5f7fa',
            'border_color' => '#c8d8e8',
            'font_weight' => '500',
            'icon' => 'fa-folder',
            'class' => 'level-2'
        ],
        3 => [
            'font_size' => '13.5px',
            'color' => '#5a7a9a',
            'bg' => '#fafbfc',
            'border_color' => '#d8e8f0',
            'font_weight' => '400',
            'icon' => 'fa-folder',
            'class' => 'level-3'
        ],
        4 => [
            'font_size' => '13px',
            'color' => '#6a8aaa',
            'bg' => '#fefefe',
            'border_color' => '#e0e8f0',
            'font_weight' => '400',
            'icon' => 'fa-folder',
            'class' => 'level-4'
        ],
    ];
    
    // اگر سطح بیشتر از 4 بود، از تنظیمات سطح 4 استفاده کن
    $levelKey = $level > 4 ? 4 : $level;
    $config = $configs[$levelKey];
    
    foreach ($menus as $menu) {
        if ($menu['parent_id'] == $parent_id) {
            $hasChildren = $menu['child_count'] > 0;
            $activeClass = ($current_menu_id == $menu['id']) ? 'active' : '';
            
            $isOpen = ($current_menu_id !== null && ($current_menu_id == $menu['id'] || in_array($menu['id'], $active_parents)));
            $openClass = $isOpen ? 'open' : '';
            
            // ========================================
            // نمایش منو با رنگ و سایز مناسب - بدون ایندنت
            // ========================================
            $html .= '<li class="menu-item ' . $config['class'] . ' ' . $openClass . '" data-menu-id="' . $menu['id'] . '">';
            
            if ($hasChildren) {
                $html .= '<div class="menu-parent">';
                $html .= '<a href="index.php?menu=' . $menu['id'] . '" class="menu-link ' . $activeClass . '" 
                            style="font-size: ' . $config['font_size'] . '; 
                                   color: ' . $config['color'] . '; 
                                   background: ' . $config['bg'] . '; 
                                   border-right-color: ' . $config['border_color'] . '; 
                                   font-weight: ' . $config['font_weight'] . ';">';
                $html .= '<i class="fas ' . $config['icon'] . '" aria-hidden="true"></i> ' . htmlspecialchars($menu['title']);
                $html .= '</a>';
                $html .= '<button class="toggle-submenu" data-menu-id="' . $menu['id'] . '" aria-label="باز و بسته کردن زیرمنو">';
                $html .= '<i class="fas fa-chevron-down" aria-hidden="true"></i>';
                $html .= '</button>';
                $html .= '</div>';
                
                $new_active_parents = $active_parents;
                if ($current_menu_id == $menu['id'] || $isOpen) {
                    $new_active_parents[] = $menu['id'];
                }
                
                $html .= '<ul class="submenu-list" style="display: ' . ($isOpen ? 'block' : 'none') . ';">';
                $html .= displayMenusTree($menus, $menu['id'], $current_menu_id, $level + 1, $new_active_parents);
                $html .= '</ul>';
            } else {
                $html .= '<a href="index.php?menu=' . $menu['id'] . '" class="menu-link ' . $activeClass . '" 
                            style="font-size: ' . $config['font_size'] . '; 
                                   color: ' . $config['color'] . '; 
                                   background: ' . $config['bg'] . '; 
                                   border-right-color: ' . $config['border_color'] . '; 
                                   font-weight: ' . $config['font_weight'] . ';">';
                $html .= '<i class="fas ' . $config['icon'] . '" aria-hidden="true"></i> ' . htmlspecialchars($menu['title']);
                $html .= '</a>';
            }
            
            $html .= '</li>';
        }
    }
    return $html;
}

include 'header.php';
?>

<style>
    /* ======================================== */
    /* استایل‌های اختصاصی صفحه اصلی */
    /* ======================================== */
    
    /* Hero Section */
    .hero-new {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3.5rem 2rem;
        margin-bottom: 2rem;
        border-radius: 25px;
        max-width: 1200px;
        margin: 0 auto 2rem auto;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .hero-new::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 70%;
        height: 200%;
        background: rgba(255, 255, 255, 0.05);
        transform: rotate(15deg);
        pointer-events: none;
    }
    
    .hero-content {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    
    .hero-content h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        font-weight: 900;
        line-height: 1.3;
    }
    
    .hero-content h1 .highlight {
        color: #ffd700;
    }
    
    .hero-content p {
        font-size: 1.1rem;
        opacity: 0.95;
        line-height: 1.8;
        max-width: 650px;
        margin: 0 auto;
    }
    
    .hero-content .hero-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.4rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        margin-bottom: 1rem;
        backdrop-filter: blur(5px);
    }
    
    /* بخش اصلی */
    .main-layout {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        gap: 2rem;
        padding: 0 1.5rem;
    }
    
    .right-column {
        flex: 1;
        min-width: 250px;
    }
    
    .left-column {
        flex: 2;
        min-width: 300px;
    }
    
    /* کارت دسته‌بندی */
    .categories-card {
        background: white;
        border-radius: 20px;
        padding: 1.8rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        position: sticky;
        top: 20px;
        border: 1px solid rgba(0, 0, 0, 0.04);
    }
    
    .categories-card h3 {
        color: #2d3748;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #667eea;
        display: inline-block;
        font-weight: 700;
        font-size: 1.2rem;
    }
    
    .categories-card h3 i {
        color: #667eea;
        margin-left: 8px;
    }
    
    /* ======================================== */
    /* استایل منوی درختی - بدون ایندنت */
    /* ======================================== */
    .menu-list {
        list-style: none;
        margin-top: 0.5rem;
        padding-right: 0;
    }

    .menu-list li {
        margin: 0.15rem 0;
        list-style: none;
        transition: all 0.2s ease;
    }

    .menu-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0.6rem 1rem;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s ease;
        flex: 1;
        font-family: 'Vazirmatn', sans-serif;
        border-right: 3px solid transparent;
    }

    .menu-link i {
        width: 22px;
        flex-shrink: 0;
        font-size: 1em;
        color: inherit;
        opacity: 0.7;
    }
    
    /* ======================================== */
    /* سطوح مختلف منو - فقط با رنگ و فونت */
    /* ======================================== */
    
    /* سطح ریشه (level 0) */
    .level-root .menu-link {
        padding: 0.7rem 1rem;
        border-radius: 12px;
    }
    
    .level-root .menu-link i {
        opacity: 1;
    }
    
    /* سطح 1 */
    .level-1 .menu-link {
        padding: 0.55rem 1rem;
        border-radius: 10px;
    }
    
    /* سطح 2 */
    .level-2 .menu-link {
        padding: 0.5rem 1rem;
        border-radius: 8px;
    }
    
    /* سطح 3 */
    .level-3 .menu-link {
        padding: 0.45rem 1rem;
        border-radius: 8px;
    }
    
    /* سطح 4+ */
    .level-4 .menu-link {
        padding: 0.4rem 1rem;
        border-radius: 8px;
    }
    
    /* ======================================== */
    /* دکمه باز/بستن زیرمنو */
    /* ======================================== */
    .menu-parent {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }

    .toggle-submenu {
        background: rgba(102, 126, 234, 0.1);
        border: none;
        cursor: pointer;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        color: #667eea;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .toggle-submenu:hover {
        background: rgba(102, 126, 234, 0.2);
        transform: scale(1.05);
    }

    .toggle-submenu i {
        transition: transform 0.3s ease;
        font-size: 12px;
    }

    .menu-item.open .toggle-submenu i {
        transform: rotate(180deg);
    }

    .submenu-list {
        list-style: none;
        transition: all 0.3s ease;
        padding-right: 0;
        margin: 0;
    }
    
    /* ======================================== */
    /* هاور و اکتیو */
    /* ======================================== */
    .menu-link:hover {
        transform: translateX(-3px);
        background: linear-gradient(135deg, #667eea, #764ba2) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        border-right-color: transparent !important;
    }

    .menu-link:hover i {
        color: white !important;
        opacity: 1 !important;
    }

    .menu-link.active {
        background: linear-gradient(135deg, #667eea, #764ba2) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        border-right-color: transparent !important;
    }

    .menu-link.active i {
        color: white !important;
        opacity: 1 !important;
    }
    
    /* کارت‌های محتوا */
    .info-card, .welcome-card, .final-card {
        background: white;
        border-radius: 20px;
        padding: 1.8rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }
    
    .info-card:hover, .welcome-card:hover, .final-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }
    
    .info-card i, .welcome-card i {
        color: #667eea;
        font-size: 2rem;
        margin-bottom: 0.8rem;
        display: inline-block;
    }
    
    .info-card h3, .welcome-card h4 {
        color: #2d3748;
        margin-bottom: 0.8rem;
        font-weight: 700;
    }
    
    .info-card p, .welcome-card p {
        color: #4a5568;
        line-height: 1.8;
    }
    
    .final-card p {
        color: #4a5568;
        line-height: 1.8;
    }
    
    .final-card strong {
        color: #2d3748;
    }
    
    /* پست‌ها */
    .posts-section {
        margin-top: 0.5rem;
    }
    
    .posts-title {
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e2e8f0;
        color: #2d3748;
        font-weight: 700;
    }
    
    .posts-title i {
        color: #667eea;
        margin-left: 8px;
    }
    
    .post-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.04);
    }
    
    .post-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    }
    
    .post-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 1.2rem 1.8rem;
    }
    
    .post-header h2 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }
    
    .post-header h2 a {
        color: white;
        text-decoration: none;
        transition: opacity 0.3s ease;
    }
    
    .post-header h2 a:hover {
        opacity: 0.8;
    }
    
    .post-meta {
        font-size: 0.8rem;
        opacity: 0.9;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .post-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .post-body {
        padding: 1.5rem 1.8rem;
        line-height: 1.8;
        color: #4a5568;
    }
    
    .post-body p {
        margin-bottom: 0.5rem;
    }
    
    .read-more {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #667eea;
        text-decoration: none;
        font-weight: 700;
        margin-top: 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        background: rgba(102, 126, 234, 0.08);
        border: 1px solid rgba(102, 126, 234, 0.15);
    }

    .read-more:hover {
        gap: 15px;
        color: white;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-color: transparent;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        transform: translateY(-2px);
    }

    .read-more i {
        transition: transform 0.3s ease;
    }

    .read-more:hover i {
        transform: translateX(-5px);
    }
    
    /* پیام دیباگ */
    .debug-info {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        color: #856404;
        font-size: 0.95rem;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.8;
    }
    
    .debug-info i {
        font-size: 1.5rem;
        margin-top: 0.2rem;
        flex-shrink: 0;
    }
    
    .debug-info strong {
        color: #6c5200;
    }
    
    .debug-info a {
        color: #667eea;
        text-decoration: underline;
    }
    
    /* حالت خالی */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
    }
    
    .empty-state i {
        font-size: 3.5rem;
        color: #cbd5e0;
        margin-bottom: 1rem;
        display: inline-block;
    }
    
    .empty-state p {
        color: #4a5568;
        font-size: 1.05rem;
    }
    
    /* ======================================== */
    /* استایل‌های ریسپانسیو */
    /* ======================================== */
    @media (max-width: 992px) {
        .hero-content h1 {
            font-size: 2rem;
        }
        
        .main-layout {
            gap: 1.5rem;
            padding: 0 1rem;
        }
    }
    
    @media (max-width: 768px) {
        .main-layout {
            flex-direction: column;
            padding: 0 1rem;
        }
        
        .right-column {
            min-width: auto;
        }
        
        .left-column {
            min-width: auto;
        }
        
        .hero-new {
            padding: 2rem 1.5rem;
            border-radius: 20px;
            margin: 0 1rem 1.5rem 1rem;
        }
        
        .hero-content h1 {
            font-size: 1.6rem;
        }
        
        .hero-content p {
            font-size: 0.95rem;
        }
        
        .post-header {
            padding: 1rem 1.2rem;
        }
        
        .post-body {
            padding: 1rem 1.2rem;
        }
        
        .post-header h2 {
            font-size: 1rem;
        }
        
        .categories-card {
            padding: 1.2rem;
        }
        
        .menu-link {
            padding: 0.5rem 0.8rem !important;
        }
        
        .menu-link i {
            width: 18px;
        }
        
        .toggle-submenu {
            width: 24px;
            height: 24px;
        }
        
        .toggle-submenu i {
            font-size: 10px;
        }
    }
    
    @media (max-width: 480px) {
        .hero-new {
            padding: 1.5rem 1rem;
            margin: 0 0.5rem 1rem 0.5rem;
        }
        
        .hero-content h1 {
            font-size: 1.3rem;
        }
        
        .hero-content p {
            font-size: 0.85rem;
        }
        
        .hero-content .hero-badge {
            font-size: 0.7rem;
            padding: 0.3rem 1rem;
        }
        
        .categories-card {
            padding: 1rem;
        }
        
        .info-card, .welcome-card, .final-card {
            padding: 1.2rem;
        }
        
        .post-meta {
            font-size: 0.7rem;
            gap: 10px;
        }
        
        .post-meta span {
            flex-wrap: wrap;
        }
        
        .debug-info {
            padding: 0.8rem 1rem;
            font-size: 0.85rem;
        }
        
        .menu-link {
            padding: 0.4rem 0.6rem !important;
            font-size: 0.8rem !important;
            gap: 6px;
        }
        
        .menu-link i {
            width: 16px;
            font-size: 0.8em;
        }
        
        .toggle-submenu {
            width: 20px;
            height: 20px;
        }
        
        .toggle-submenu i {
            font-size: 9px;
        }
    }
</style>

<!-- ======================================== -->
<!-- محتوای صفحه اصلی -->
<!-- ======================================== -->

<?php if (!$menu_id): ?>
<!-- بخش هدر اصلی -->
<section class="hero-new">
    <div class="hero-content">
        <span class="hero-badge">
            <i class="fas fa-rocket" aria-hidden="true"></i>
            آینده بهره‌وری با هوش مصنوعی
        </span>
        <h1>
            <span class="highlight">AI Productivity &amp; Strategy</span><br>
            در عصر هوش مصنوعی
        </h1>
        <p>
            هوش مصنوعی نه تنها نحوه کار، بلکه نحوه تفکر و استراتژی ما را متحول می‌کند.
            این پلتفرم به متخصصان، محققان و سازمان‌ها کمک می‌کند تا رویکردهای ساختاریافته 
            برای <strong>بهره‌وری</strong> و عملکرد ایجاد کنند.
        </p>
    </div>
</section>
<?php endif; ?>

<!-- بخش اصلی -->
<div class="main-layout">
    
    <!-- ستون سمت راست: دسته‌بندی‌ها -->
    <aside class="right-column">
        <div class="categories-card">
            <h3><i class="fas fa-bars" aria-hidden="true"></i> دسته‌بندی‌ها</h3>
            <ul class="menu-list">
                <?php if (count($menus) > 0): ?>
                    <?= displayMenusTree($menus, 0, $menu_id) ?>
                <?php else: ?>
                    <li><span style="color: #999; font-size: 0.9rem;">هیچ دسته‌بندی وجود ندارد</span></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <?php if (!$menu_id): ?>
        <div class="welcome-card">
            <i class="fas fa-hand-peace" aria-hidden="true"></i>
            <h4>به AI Productivity Strategy خوش آمدید</h4>
            <p>از دسته‌بندی‌ها، موضوع مورد نظر خود را انتخاب کنید.</p>
        </div>
        <?php endif; ?>
    </aside>
    
    <!-- ستون سمت چپ: محتوای اصلی -->
    <main class="left-column">
        <?php if ($menu_id): ?>
            <!-- نمایش پست‌های یک دسته -->
            <section class="posts-section">
                <h2 class="posts-title">
                    <i class="fas fa-edit" aria-hidden="true"></i>
                    مطالب دسته: <?= htmlspecialchars($menuTitle ?? '') ?>
                </h2>
                
                <!-- نمایش پیام دیباگ در صورت وجود -->
                <?php if ($debugInfo): ?>
                    <div class="debug-info">
                        <i class="fas fa-info-circle" aria-hidden="true"></i>
                        <div>
                            <?= nl2br(htmlspecialchars($debugInfo)) ?>
                            <?php if (strpos($debugInfo, 'ستون') !== false): ?>
                                <br><br>
                                <strong>راه حل:</strong> 
                                <a href="fix_posts.php" target="_blank">✅ کلیک کنید تا ابزار اصلاح دیتابیس اجرا شود</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card" itemscope itemtype="https://schema.org/Article">
                            <div class="post-header">
                                <h2 itemprop="headline">
                                    <a href="post.php?id=<?= $post['id'] ?>">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h2>
                                <div class="post-meta">
                                    <span>
                                        <i class="fas fa-user" aria-hidden="true"></i>
                                        <span itemprop="author"><?= htmlspecialchars($post['author_name']) ?></span>
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar" aria-hidden="true"></i>
                                        <time datetime="<?= date('Y-m-d', strtotime($post['created_at'])) ?>" itemprop="datePublished">
                                            <?= date('Y/m/d', strtotime($post['created_at'])) ?>
                                        </time>
                                    </span>
                                    <?php if ($post['menu_title']): ?>
                                    <span>
                                        <i class="fas fa-folder" aria-hidden="true"></i>
                                        <?= htmlspecialchars($post['menu_title']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="post-body" itemprop="description">
                                <!-- نمایش خلاصه 300 کاراکتر اول -->
                                <p><?= nl2br(htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 300))) ?>...</p>
                                
                                <!-- دکمه ادامه نوشته -->
                                <a href="post.php?id=<?= $post['id'] ?>" class="read-more">
                                    ادامه نوشته ... <i class="fas fa-arrow-left" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox" aria-hidden="true"></i>
                        <p>هنوز مطلبی در این دسته منتشر نشده است.</p>
                        <?php if ($debugInfo && strpos($debugInfo, 'وضعیت') !== false): ?>
                            <p style="font-size: 0.85rem; color: #856404; margin-top: 0.5rem;">
                                💡 برای رفع این مشکل، <a href="fix_posts.php" style="color: #667eea;">ابزار اصلاح دیتابیس</a> را اجرا کنید.
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <!-- صفحه اصلی -->
            <div class="info-card">
                <i class="fas fa-chart-line" aria-hidden="true"></i>
                <h3>🔹 مجموعه بهره‌وری هوش مصنوعی</h3>
                <p>مجموعه بهره‌وری هوش مصنوعی بررسی می‌کند که چگونه هوش مصنوعی کار روزانه را دگرگون می‌کند.</p>
                <p style="margin-top: 0.8rem;">
                    از بازتعریف عادت‌های بهره‌وری تا طراحی گردش‌کارهای هوشمند و درک کار مبتنی بر هوش مصنوعی، 
                    این مجموعه چارچوب‌ها و ابزارهای عملی را برای حرفه‌ای‌هایی که می‌خواهند 
                    با هوش مصنوعی هوشمندانه‌تر کار کنند، ارائه می‌دهد.
                </p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-chess-board" aria-hidden="true"></i>
                <h3>🔹 مجموعه استراتژی هوش مصنوعی</h3>
                <p>مجموعه استراتژی هوش مصنوعی بر تصویر بزرگ‌تر متمرکز است: چگونه هوش مصنوعی سازمان‌ها و رقابت را متحول می‌کند.</p>
                <p style="margin-top: 0.8rem;">
                    این مجموعه بررسی می‌کند که چگونه متخصصان، رهبران و سازمان‌ها می‌توانند 
                    از استفاده از هوش مصنوعی به عنوان یک ابزار به بهره‌برداری از آن به عنوان یک مزیت استراتژیک حرکت کنند.
                </p>
            </div>
            
            <div class="final-card">
                <p>✨ محتوای این وبسایت فقط درباره ابزارها نیست.</p>
                <p>بلکه درباره ساختن <strong>قابلیت، استراتژی و مزیت بلندمدت</strong> در عصر هوش مصنوعی است.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- ======================================== -->
<!-- اسکریپت‌های مربوط به منو -->
<!-- ======================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========================================
    // باز و بستن زیرمنوها
    // ========================================
    const toggles = document.querySelectorAll('.toggle-submenu');
    toggles.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const menuItem = this.closest('.menu-item');
            const submenu = menuItem.querySelector('.submenu-list');
            if (submenu) {
                const isOpen = submenu.style.display !== 'none' && getComputedStyle(submenu).display !== 'none';
                if (isOpen) {
                    submenu.style.display = 'none';
                    menuItem.classList.remove('open');
                } else {
                    submenu.style.display = 'block';
                    menuItem.classList.add('open');
                }
            }
        });
    });
    
    // ========================================
    // باز نگه داشتن منوی فعال
    // ========================================
    const activeLink = document.querySelector('.menu-link.active');
    if (activeLink) {
        let parent = activeLink.closest('.menu-item');
        while (parent) {
            const sub = parent.querySelector('.submenu-list');
            if (sub) {
                sub.style.display = 'block';
                parent.classList.add('open');
            }
            parent = parent.parentElement.closest('.menu-item');
        }
    }
    
    // ========================================
    // بستن منو در موبایل با کلیک خارج از منو
    // ========================================
    document.addEventListener('click', function(e) {
        const navMenu = document.getElementById('navMenu');
        const menuToggle = document.getElementById('menuToggle');
        if (navMenu && navMenu.classList.contains('active')) {
            const isClickInside = navMenu.contains(e.target) || menuToggle.contains(e.target);
            if (!isClickInside && window.innerWidth <= 768) {
                navMenu.classList.remove('active');
                if (menuToggle) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    const icon = menuToggle.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-bars';
                    }
                }
            }
        }
    });
});
</script>

<?php include 'footer.php'; ?>