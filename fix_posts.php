<?php
// ========================================
// فایل: fix_posts.php
// ابزار اصلاح وضعیت پست‌ها و اضافه کردن ستون status
// ========================================

session_start();

// فقط ادمین می‌تواند از این ابزار استفاده کند
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

$page_title = 'ابزار اصلاح دیتابیس | مدیریت';
include 'header.php';
?>

<style>
    .fix-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }
    
    .fix-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }
    
    .fix-card h2 {
        color: #2d3748;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #667eea;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-published {
        background: #d4edda;
        color: #155724;
    }
    
    .status-draft {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-null {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-fix {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-fix:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        color: white;
    }
    
    .btn-fix-danger {
        background: #e53e3e;
    }
    
    .btn-fix-danger:hover {
        background: #c53030;
        box-shadow: 0 8px 25px rgba(229, 62, 62, 0.3);
    }
    
    .table-wrap {
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    
    table th {
        background: #667eea;
        color: white;
        padding: 0.8rem 1rem;
        text-align: right;
    }
    
    table td {
        padding: 0.8rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    
    table tr:hover {
        background: #f8f9fa;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        border-right: 4px solid #28a745;
        margin-bottom: 1.5rem;
    }
    
    .alert-warning {
        background: #fff3cd;
        color: #856404;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        border-right: 4px solid #ffc107;
        margin-bottom: 1.5rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-item {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 12px;
        text-align: center;
    }
    
    .stat-item .number {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
    }
    
    .stat-item .label {
        font-size: 0.85rem;
        color: #718096;
    }
</style>

<div class="fix-container">
    <h1 style="color: #2d3748; margin-bottom: 1.5rem;">🔧 ابزار اصلاح پایگاه داده</h1>
    
    <div class="fix-card">
        <h2>📊 وضعیت فعلی پست‌ها</h2>
        
        <?php
        try {
            // ۱. بررسی وجود ستون status
            $checkColumn = $pdo->query("SHOW COLUMNS FROM posts LIKE 'status'");
            $hasStatusColumn = $checkColumn->rowCount() > 0;
            
            if (!$hasStatusColumn) {
                echo '<div class="alert-warning">';
                echo '⚠️ ستون <strong>status</strong> در جدول posts وجود ندارد. ';
                echo '<a href="?add_column=1" class="btn-fix" style="margin-right: 10px;">➕ افزودن ستون status</a>';
                echo '</div>';
            } else {
                echo '<div class="alert-success">✅ ستون <strong>status</strong> در جدول posts وجود دارد.</div>';
            }
            
            // ۲. آمار پست‌ها
            $stats = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN status != 'published' OR status IS NULL THEN 1 ELSE 0 END) as not_published
                FROM posts
            ")->fetch();
            
            ?>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="number"><?= $stats['total'] ?></div>
                    <div class="label">کل پست‌ها</div>
                </div>
                <div class="stat-item" style="background: #d4edda;">
                    <div class="number" style="color: #155724;"><?= $stats['published'] ?></div>
                    <div class="label">✅ منتشر شده</div>
                </div>
                <div class="stat-item" style="background: #f8d7da;">
                    <div class="number" style="color: #721c24;"><?= $stats['not_published'] ?></div>
                    <div class="label">⚠️ منتشر نشده</div>
                </div>
            </div>
            
            <?php if ($stats['not_published'] > 0): ?>
                <div style="margin: 1rem 0;">
                    <a href="?fix_all=1" class="btn-fix">✅ انتشار همه پست‌ها</a>
                </div>
            <?php endif; ?>
            
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>عنوان</th>
                            <th>دسته‌بندی</th>
                            <th>وضعیت</th>
                            <th>نویسنده</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $posts = $pdo->query("
                            SELECT p.*, u.username as author_name, m.title as menu_title
                            FROM posts p
                            LEFT JOIN users u ON p.author_id = u.id
                            LEFT JOIN menus m ON p.menu_id = m.id
                            ORDER BY p.id DESC
                        ")->fetchAll();
                        
                        if (count($posts) == 0):
                        ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #718096; padding: 2rem;">
                                <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                                هیچ پستی در دیتابیس وجود ندارد.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?= $post['id'] ?></td>
                                    <td><?= htmlspecialchars(mb_substr($post['title'], 0, 50)) ?></td>
                                    <td><?= htmlspecialchars($post['menu_title'] ?? 'بدون دسته') ?></td>
                                    <td>
                                        <?php if ($post['status'] == 'published'): ?>
                                            <span class="status-badge status-published">✅ منتشر شده</span>
                                        <?php elseif ($post['status'] == '' || $post['status'] === null): ?>
                                            <span class="status-badge status-null">❌ بدون وضعیت</span>
                                        <?php else: ?>
                                            <span class="status-badge status-draft">📝 <?= htmlspecialchars($post['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($post['author_name'] ?? 'نامشخص') ?></td>
                                    <td>
                                        <?php if ($post['status'] != 'published'): ?>
                                            <a href="?fix_id=<?= $post['id'] ?>" class="btn-fix" style="font-size: 0.75rem;">✅ انتشار</a>
                                        <?php else: ?>
                                            <span style="color: #28a745;">✔️ منتشر شده</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        <?php
        } catch (PDOException $e) {
            echo '<div class="alert-warning">❌ خطا: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
    
    <div class="fix-card" style="background: #f8f9fa;">
        <h2>💡 راهنما</h2>
        <ul style="line-height: 2; color: #4a5568; padding-right: 1.5rem;">
            <li>✅ <strong>منتشر شده</strong> = پست در سایت نمایش داده می‌شود</li>
            <li>📝 <strong>پیش‌نویس</strong> = پست در سایت نمایش داده نمی‌شود</li>
            <li>❌ <strong>بدون وضعیت</strong> = نیاز به تنظیم وضعیت دارد</li>
            <li>برای انتشار یک پست، روی دکمه <strong>✅ انتشار</strong> کلیک کنید</li>
            <li>برای انتشار همه پست‌ها، روی دکمه <strong>✅ انتشار همه پست‌ها</strong> کلیک کنید</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="index.php" class="btn-fix" style="padding: 0.8rem 2.5rem; font-size: 1rem;">
            🔙 بازگشت به صفحه اصلی
        </a>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
            <a href="admin.php" class="btn-fix" style="padding: 0.8rem 2.5rem; font-size: 1rem; background: #38a169;">
                ⚙️ پنل مدیریت
            </a>
        <?php endif; ?>
    </div>
</div>

<?php
// ========================================
// پردازش درخواست‌ها
// ========================================

if (isset($_GET['add_column'])) {
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN status VARCHAR(20) DEFAULT 'published' AFTER focus_keyword");
        echo '<script>alert("✅ ستون status با موفقیت اضافه شد."); window.location.href = "fix_posts.php";</script>';
    } catch (PDOException $e) {
        echo '<script>alert("❌ خطا: ' . addslashes($e->getMessage()) . '");</script>';
    }
}

if (isset($_GET['fix_id']) && is_numeric($_GET['fix_id'])) {
    $id = (int)$_GET['fix_id'];
    try {
        $stmt = $pdo->prepare("UPDATE posts SET status = 'published' WHERE id = ?");
        $stmt->execute([$id]);
        echo '<script>alert("✅ پست با ID ' . $id . ' به وضعیت منتشر شده تغییر یافت."); window.location.href = "fix_posts.php";</script>';
    } catch (PDOException $e) {
        echo '<script>alert("❌ خطا: ' . addslashes($e->getMessage()) . '");</script>';
    }
}

if (isset($_GET['fix_all'])) {
    try {
        $count = $pdo->exec("UPDATE posts SET status = 'published' WHERE status != 'published' OR status IS NULL");
        echo '<script>alert("✅ ' . $count . ' پست به وضعیت منتشر شده تغییر یافت."); window.location.href = "fix_posts.php";</script>';
    } catch (PDOException $e) {
        echo '<script>alert("❌ خطا: ' . addslashes($e->getMessage()) . '");</script>';
    }
}

include 'footer.php';
?>