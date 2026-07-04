<?php
// فایل: admin.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$page_title = 'پنل مدیریت | AI Productivity Strategy';

if (!isAdmin()) {
    header("HTTP/1.0 403 Forbidden");
    die("دسترسی غیرمجاز");
}

$success = '';
$error = '';

// تابع بازگشتی برای نمایش منوها به صورت درختی در بخش مدیریت منوها
function displayMenus($menus, $parent_id = 0, $level = 0) {
    $html = '';
    foreach ($menus as $menu) {
        if ($menu['parent_id'] == $parent_id) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            $prefix = $level > 0 ? '└─ ' : '';
            
            $html .= '<div class="menu-item" data-menu-id="' . $menu['id'] . '" style="margin-right: ' . ($level * 20) . 'px;">';
            $html .= '<div class="menu-info">';
            $html .= '<i class="fas fa-folder"></i>';
            $html .= '<span>' . $indent . $prefix . htmlspecialchars($menu['title']) . '</span>';
            $html .= '</div>';
            $html .= '<div class="menu-actions">';
            $html .= '<button class="edit-menu-btn" onclick="showEditForm(' . $menu['id'] . ', \'' . addslashes($menu['title']) . '\')">';
            $html .= '<i class="fas fa-pen"></i> ویرایش</button>';
            $html .= '<button class="add-submenu-btn" onclick="showSubmenuForm(' . $menu['id'] . ')">';
            $html .= '<i class="fas fa-plus-circle"></i> افزودن زیرمنو</button>';
            $html .= '<a href="?delete_menu=' . $menu['id'] . '" onclick="return confirm(\'آیا از حذف منو مطمئن هستید؟\')">';
            $html .= '<i class="fas fa-trash"></i> حذف</a>';
            $html .= '</div>';
            $html .= '<div class="edit-menu-form" id="edit-form-' . $menu['id'] . '">';
            $html .= '<form method="post">';
            $html .= '<input type="hidden" name="menu_id" value="' . $menu['id'] . '">';
            $html .= '<input type="text" name="menu_title" value="' . htmlspecialchars($menu['title']) . '" required>';
            $html .= '<button type="submit" name="edit_menu"><i class="fas fa-save"></i> ذخیره</button>';
            $html .= '<button type="button" onclick="hideEditForm(' . $menu['id'] . ')">انصراف</button>';
            $html .= '</form>';
            $html .= '</div>';
            $html .= '<div class="submenu-form" id="submenu-form-' . $menu['id'] . '">';
            $html .= '<form method="post">';
            $html .= '<input type="hidden" name="parent_id" value="' . $menu['id'] . '">';
            $html .= '<input type="text" name="menu_title" placeholder="عنوان زیرمنو" required>';
            $html .= '<button type="submit" name="create_submenu"><i class="fas fa-save"></i> ذخیره</button>';
            $html .= '<button type="button" onclick="hideSubmenuForm(' . $menu['id'] . ')">انصراف</button>';
            $html .= '</form>';
            $html .= '</div>';
            $html .= '</div>';
            
            $html .= displayMenus($menus, $menu['id'], $level + 1);
        }
    }
    return $html;
}

// تابع بازگشتی برای نمایش منوها در select (آبشاری سلسله‌مراتبی)
function displayMenusForSelect($menus, $parent_id = 0, $level = 0, $selected_menu_id = null) {
    $html = '';
    foreach ($menus as $menu) {
        if ($menu['parent_id'] == $parent_id) {
            // ایجاد فاصله (indent) بر اساس سطح منو
            $indent = '';
            for ($i = 0; $i < $level; $i++) {
                $indent .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if ($level > 0) {
                $indent .= '├─ ';
            }
            
            $selected = ($selected_menu_id == $menu['id']) ? 'selected' : '';
            $html .= '<option value="' . $menu['id'] . '" ' . $selected . '>';
            $html .= $indent . htmlspecialchars($menu['title']);
            $html .= '</option>';
            
            // نمایش زیرمنوها با سطح بالاتر
            $html .= displayMenusForSelect($menus, $menu['id'], $level + 1, $selected_menu_id);
        }
    }
    return $html;
}

// ایجاد منوی اصلی
if (isset($_POST['create_menu'])) {
    try {
        $title = trim($_POST['menu_title']);
        if (empty($title)) {
            $error = "عنوان منو نمی‌تواند خالی باشد.";
        } else {
            $slug = str_replace(' ', '-', preg_replace('/[^آ-یa-zA-Z0-9\s\x{0600}-\x{06FF}]/u', '', $title));
            $slug = strtolower(trim($slug, '-'));
            
            $stmt = $pdo->prepare("INSERT INTO menus (title, slug, parent_id, menu_order) VALUES (:title, :slug, 0, 0)");
            $stmt->execute([':title' => $title, ':slug' => $slug]);
            $success = "منو با موفقیت ایجاد شد.";
            header("Location: admin.php");
            exit;
        }
    } catch (PDOException $e) {
        $error = "خطا: " . $e->getMessage();
    }
}

// ایجاد زیرمنو
if (isset($_POST['create_submenu'])) {
    try {
        $title = trim($_POST['menu_title']);
        $parent_id = intval($_POST['parent_id']);
        if (empty($title)) {
            $error = "عنوان زیرمنو نمی‌تواند خالی باشد.";
        } else {
            $slug = str_replace(' ', '-', preg_replace('/[^آ-یa-zA-Z0-9\s\x{0600}-\x{06FF}]/u', '', $title));
            $slug = strtolower(trim($slug, '-'));
            
            $stmt = $pdo->prepare("INSERT INTO menus (title, slug, parent_id, menu_order) VALUES (:title, :slug, :parent_id, 0)");
            $stmt->execute([':title' => $title, ':slug' => $slug, ':parent_id' => $parent_id]);
            $success = "زیرمنو با موفقیت ایجاد شد.";
            header("Location: admin.php");
            exit;
        }
    } catch (PDOException $e) {
        $error = "خطا: " . $e->getMessage();
    }
}

// ویرایش منو
if (isset($_POST['edit_menu'])) {
    try {
        $menu_id = $_POST['menu_id'];
        $title = trim($_POST['menu_title']);
        if (empty($title)) {
            $error = "عنوان منو نمی‌تواند خالی باشد.";
        } else {
            $slug = str_replace(' ', '-', preg_replace('/[^آ-یa-zA-Z0-9\s\x{0600}-\x{06FF}]/u', '', $title));
            $slug = strtolower(trim($slug, '-'));
            
            $stmt = $pdo->prepare("UPDATE menus SET title = ?, slug = ? WHERE id = ?");
            $stmt->execute([$title, $slug, $menu_id]);
            $success = "منو با موفقیت ویرایش شد.";
            header("Location: admin.php");
            exit;
        }
    } catch (PDOException $e) {
        $error = "خطا: " . $e->getMessage();
    }
}

// حذف منو
if (isset($_GET['delete_menu'])) {
    $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
    $stmt->execute([$_GET['delete_menu']]);
    header("Location: admin.php");
    exit;
}

// افزودن مطلب
if (isset($_POST['add_post'])) {
    try {
        $menu_id = $_POST['menu_id'];
        $title = trim($_POST['post_title']);
        $content = trim($_POST['post_content']);
        $author_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("INSERT INTO posts (menu_id, title, content, author_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$menu_id, $title, $content, $author_id]);
        $success = "مطلب با موفقیت افزوده شد.";
        header("Location: admin.php");
        exit;
    } catch (PDOException $e) {
        $error = "خطا: " . $e->getMessage();
    }
}

// ویرایش مطلب
if (isset($_POST['edit_post'])) {
    $post_id = $_POST['post_id'];
    $title = $_POST['post_title'];
    $content = $_POST['post_content'];
    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
    $stmt->execute([$title, $content, $post_id]);
    $success = "مطلب ویرایش شد.";
    header("Location: admin.php");
    exit;
}

// حذف مطلب
if (isset($_GET['delete_post'])) {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$_GET['delete_post']]);
    header("Location: admin.php");
    exit;
}

$menus = $pdo->query("SELECT * FROM menus ORDER BY parent_id ASC, menu_order ASC, id ASC")->fetchAll();
$posts = $pdo->query("SELECT posts.*, menus.title as menu_title, users.username as author_name 
                      FROM posts 
                      JOIN menus ON posts.menu_id = menus.id 
                      JOIN users ON posts.author_id = users.id 
                      ORDER BY posts.created_at DESC")->fetchAll();

include 'header.php';
?>

<style>
    .admin-container {
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
        margin: 0;
    }
    
    /* استایل دکمه‌های با رنگ آبی نفتی (متناسب با دکمه پیام‌ها) */
    .admin-nav-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .nav-btn {
        background: linear-gradient(135deg, #0984e3, #00cec9);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        cursor: pointer;
        font-family: inherit;
        font-size: 0.9rem;
        font-weight: 600;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .nav-btn i {
        font-size: 1rem;
    }
    
    .nav-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(9, 132, 227, 0.4);
        background: linear-gradient(135deg, #0773c7, #00b894);
    }
    
    .nav-btn.active {
        background: linear-gradient(135deg, #0773c7, #00b894);
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);
        transform: translateY(0);
    }
    
    /* دکمه پیام‌ها */
    .btn-messages {
        background: linear-gradient(135deg, #6c5ce7, #a8a4e6);
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 10px;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .btn-messages:hover {
        background: linear-gradient(135deg, #5b4bc4, #9793d4);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
    }
    
    /* بخش‌های قابل نمایش/مخفی */
    .section {
        display: none;
        animation: fadeIn 0.5s ease;
    }
    
    .section.active-section {
        display: block;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .admin-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .admin-card h2 {
        font-size: 1.3rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #0984e3;
        display: inline-block;
    }
    
    .success {
        background: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-right: 3px solid #28a745;
    }
    
    .error {
        background: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-right: 3px solid #dc3545;
    }
    
    input, select, textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
    }
    
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #0984e3;
        box-shadow: 0 0 0 3px rgba(9, 132, 227, 0.1);
    }
    
    button {
        background: linear-gradient(135deg, #0984e3, #00cec9);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-family: inherit;
        font-weight: 500;
        transition: 0.3s;
        margin: 2px;
    }
    
    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(9, 132, 227, 0.4);
    }
    
    .menu-item {
        background: #f8f9fa;
        margin-bottom: 8px;
        border-radius: 8px;
        padding: 10px;
        transition: 0.3s;
    }
    
    .menu-item:hover {
        background: #e9ecef;
    }
    
    .menu-info {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    
    .menu-info i {
        color: #0984e3;
        font-size: 1.1rem;
    }
    
    .menu-actions {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .edit-menu-btn {
        background: #f39c12;
        color: #333;
    }
    
    .edit-menu-btn:hover {
        background: #e67e22;
    }
    
    .add-submenu-btn {
        background: #00cec9;
    }
    
    .add-submenu-btn:hover {
        background: #0984e3;
    }
    
    .edit-menu-form, .submenu-form {
        display: none;
        margin-top: 10px;
        padding: 10px;
        background: #fff;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    
    .edit-menu-form.active, .submenu-form.active {
        display: block;
    }
    
    .edit-menu-form input, .submenu-form input {
        width: calc(100% - 180px);
        display: inline-block;
        margin-bottom: 0;
    }
    
    /* استایل مخصوص منوی انتخاب دسته‌بندی آبشاری */
    .category-select {
        font-family: 'Vazir', 'Tahoma', sans-serif;
        direction: rtl;
        font-size: 14px;
    }
    
    .category-select option {
        padding: 8px 12px;
        direction: rtl;
    }
    
    .form-help-text {
        display: block;
        margin-bottom: 10px;
        color: #666;
        font-size: 0.85rem;
    }
    
    .post-row {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    @media (max-width: 768px) {
        .edit-menu-form input, .submenu-form input {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .menu-actions {
            flex-direction: column;
        }
        
        .menu-actions button, .menu-actions a {
            width: 100%;
            text-align: center;
        }
        
        .admin-header {
            flex-direction: column;
            text-align: center;
        }
        
        .admin-nav-buttons {
            justify-content: center;
        }
        
        .nav-btn {
            width: auto;
            font-size: 0.85rem;
            padding: 8px 15px;
        }
    }
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-crown"></i> پنل مدیریت</h1>
        <div class="admin-nav-buttons">
            <button class="nav-btn active" onclick="showSection('add-post-section', this)">
                <i class="fas fa-edit"></i> افزودن مطلب جدید
            </button>
            <button class="nav-btn" onclick="showSection('menus-section', this)">
                <i class="fas fa-bars"></i> مدیریت منوها
            </button>
            <button class="nav-btn" onclick="showSection('posts-section', this)">
                <i class="fas fa-pen-nib"></i> ویرایش نوشته‌ها
            </button>
            <a href="admin_stats.php" class="nav-btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); text-decoration: none;">
                <i class="fas fa-chart-line"></i> آمار بازدید
            </a>
        </div>
    </div>
    
    <?php if($success): ?>
        <div class="success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- بخش افزودن مطلب جدید (پیش‌فرض نمایش داده می‌شود) -->
    <div id="add-post-section" class="section active-section">
        <div class="admin-card">
            <h2><i class="fas fa-edit"></i> افزودن مطلب جدید</h2>
            <form method="post">
                <label for="menu_id" style="display: block; margin-bottom: 5px; font-weight: 500;">
                    <i class="fas fa-folder-tree"></i> انتخاب دسته‌بندی:
                </label>
                <select name="menu_id" class="category-select" required>
                    <option value="">-- انتخاب کنید --</option>
                    <?php if(count($menus) > 0): ?>
                        <?= displayMenusForSelect($menus, 0, 0, null) ?>
                    <?php else: ?>
                        <option value="" disabled>هیچ دسته‌بندی وجود ندارد</option>
                    <?php endif; ?>
                </select>
                <small class="form-help-text">
                    <i class="fas fa-info-circle"></i> 
                    دسته‌بندی مورد نظر را انتخاب کنید. زیرمنوها با فاصله مشخص شده‌اند.
                </small>
                
                <label for="post_title" style="display: block; margin-bottom: 5px; font-weight: 500;">
                    <i class="fas fa-heading"></i> عنوان مطلب:
                </label>
                <input type="text" name="post_title" id="post_title" placeholder="عنوان مطلب (امکان استفاده از ایموجی)" required>
                
                <label for="post_content" style="display: block; margin-bottom: 5px; font-weight: 500;">
                    <i class="fas fa-align-left"></i> متن مطلب:
                </label>
                <textarea name="post_content" id="post_content" placeholder="متن مطلب... (امکان استفاده از ایموجی و فرمت‌های متنی)" rows="8" required></textarea>
                <small class="form-help-text">
                    <i class="fas fa-smile"></i> نکته: می‌توانید از ایموجی‌ها 😊 و فرمت‌های متنی استفاده کنید.
                </small>
                
                <button type="submit" name="add_post"><i class="fas fa-save"></i> ذخیره مطلب</button>
            </form>
        </div>
    </div>
    
    <!-- بخش مدیریت منوها -->
    <div id="menus-section" class="section">
        <div class="admin-card">
            <h2><i class="fas fa-bars"></i> مدیریت منوها (سلسله‌مراتبی)</h2>
            
            <form method="post" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                <h3 style="margin-bottom: 10px; font-size: 1rem;">ایجاد منوی اصلی جدید:</h3>
                <input type="text" name="menu_title" placeholder="عنوان منوی اصلی (امکان استفاده از ایموجی)" required>
                <button type="submit" name="create_menu"><i class="fas fa-plus"></i> ایجاد منوی اصلی</button>
            </form>
            
            <div>
                <h3 style="margin-bottom: 10px; font-size: 1rem;">منوهای موجود:</h3>
                <?php if(count($menus) > 0): ?>
                    <?= displayMenus($menus) ?>
                <?php else: ?>
                    <p style="color: #999; text-align: center;">هنوز منویی ایجاد نشده است.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- بخش لیست مطالب برای ویرایش -->
    <div id="posts-section" class="section">
        <div class="admin-card">
            <h2><i class="fas fa-pen-nib"></i> ویرایش نوشته‌ها</h2>
            
            <?php if(count($posts) > 0): ?>
                <?php foreach($posts as $post): ?>
                    <div class="post-row">
                        <div style="margin-bottom: 8px;">
                            <strong style="font-size: 1.1rem;"><?= htmlspecialchars($post['title']) ?></strong>
                        </div>
                        
                        <div style="margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 15px; font-size: 0.85rem; color: #666;">
                            <span><i class="fas fa-folder"></i> <?= htmlspecialchars($post['menu_title']) ?></span>
                            <span><i class="fas fa-user"></i> <?= htmlspecialchars($post['author_name']) ?></span>
                            <span><i class="fas fa-calendar"></i> <?= $post['created_at'] ?></span>
                        </div>
                        
                        <div class="post-content-preview" style="margin-bottom: 12px; color: #555; line-height: 1.6;">
                            <?= htmlspecialchars(mb_substr($post['content'], 0, 150)) ?>...
                        </div>
                        
                        <form method="post" style="margin-top: 10px;">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <input type="text" name="post_title" value="<?= htmlspecialchars($post['title']) ?>" style="width: 100%; margin-bottom: 10px;">
                            <textarea name="post_content" rows="5" style="width: 100%; margin-bottom: 10px;"><?= htmlspecialchars($post['content']) ?></textarea>
                            <button type="submit" name="edit_post" style="background: #f39c12; color: #333;"><i class="fas fa-save"></i> ذخیره تغییرات</button>
                            <a href="?delete_post=<?= $post['id'] ?>" onclick="return confirm('آیا از حذف این مطلب مطمئن هستید؟')" style="background: #dc3545; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; display: inline-block;"><i class="fas fa-trash"></i> حذف مطلب</a>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #999; text-align: center;">هنوز مطلبی ثبت نشده است.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showEditForm(menuId, menuTitle) {
    var allForms = document.querySelectorAll('.edit-menu-form');
    allForms.forEach(function(form) {
        form.classList.remove('active');
    });
    
    var form = document.getElementById('edit-form-' + menuId);
    form.classList.add('active');
}

function hideEditForm(menuId) {
    var form = document.getElementById('edit-form-' + menuId);
    form.classList.remove('active');
}

function showSubmenuForm(menuId) {
    var allForms = document.querySelectorAll('.submenu-form');
    allForms.forEach(function(form) {
        form.classList.remove('active');
    });
    
    var form = document.getElementById('submenu-form-' + menuId);
    form.classList.add('active');
}

function hideSubmenuForm(menuId) {
    var form = document.getElementById('submenu-form-' + menuId);
    form.classList.remove('active');
}

// تابع نمایش/مخفی کردن بخش‌ها با مدیریت دکمه فعال
function showSection(sectionId, buttonElement) {
    // مخفی کردن همه بخش‌ها
    var sections = document.querySelectorAll('.section');
    sections.forEach(function(section) {
        section.classList.remove('active-section');
    });
    
    // نمایش بخش انتخاب شده
    var activeSection = document.getElementById(sectionId);
    if (activeSection) {
        activeSection.classList.add('active-section');
    }
    
    // تغییر وضعیت دکمه‌های ناوبری
    var buttons = document.querySelectorAll('.nav-btn');
    buttons.forEach(function(button) {
        button.classList.remove('active');
    });
    
    // فعال کردن دکمه کلیک شده
    if (buttonElement) {
        buttonElement.classList.add('active');
    }
}

// بررسی اگر در URL پارامتری وجود دارد که مشخص کند کدام بخش نمایش داده شود
var urlParams = new URLSearchParams(window.location.search);
var section = urlParams.get('section');
if (section === 'menus') {
    showSection('menus-section', document.querySelector('.nav-btn[onclick*="menus-section"]'));
} else if (section === 'posts') {
    showSection('posts-section', document.querySelector('.nav-btn[onclick*="posts-section"]'));
} else {
    // پیش‌فرض: نمایش بخش افزودن مطلب جدید
    showSection('add-post-section', document.querySelector('.nav-btn[onclick*="add-post-section"]'));
}
</script>

<?php include 'footer.php'; ?>