<?php
// ========================================
// فایل: post.php
// نمایش یک پست کامل با ناوبری قبلی/بعدی
// ========================================

session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// دریافت ID مطلب از URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id == 0) {
    header("Location: index.php");
    exit;
}

try {
    // دریافت اطلاعات مطلب
    $stmt = $pdo->prepare("
        SELECT p.*, u.username as author_name, m.title as menu_title, m.id as menu_id
        FROM posts p
        JOIN users u ON p.author_id = u.id
        LEFT JOIN menus m ON p.menu_id = m.id
        WHERE p.id = ?
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        header("Location: index.php");
        exit;
    }

    // دریافت مطلب قبلی (در همان دسته)
    $prevStmt = $pdo->prepare("
        SELECT id, title FROM posts 
        WHERE menu_id = ? AND id < ?
        ORDER BY id DESC LIMIT 1
    ");
    $prevStmt->execute([$post['menu_id'], $post_id]);
    $prevPost = $prevStmt->fetch();

    // دریافت مطلب بعدی (در همان دسته)
    $nextStmt = $pdo->prepare("
        SELECT id, title FROM posts 
        WHERE menu_id = ? AND id > ?
        ORDER BY id ASC LIMIT 1
    ");
    $nextStmt->execute([$post['menu_id'], $post_id]);
    $nextPost = $nextStmt->fetch();

    // تنظیم متا تگ‌ها
    $page_title = htmlspecialchars($post['title']) . ' | AI Productivity Strategy';
    $meta_description = htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 160));
    $meta_keywords = htmlspecialchars($post['meta_keywords'] ?? '');
    $og_title = htmlspecialchars($post['title']);
    $og_description = htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 160));

} catch (PDOException $e) {
    error_log('Post error: ' . $e->getMessage());
    header("Location: index.php");
    exit;
}

include 'header.php';
?>

<style>
    /* ======================================== */
    /* استایل‌های اختصاصی صفحه پست */
    /* ======================================== */
    .post-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }
    
    /* مسیر راهنما */
    .breadcrumb {
        padding: 0.8rem 0;
        margin-bottom: 1.5rem;
        color: #4a5568;
        font-size: 0.9rem;
    }
    
    .breadcrumb a {
        color: #667eea;
        text-decoration: none;
    }
    
    .breadcrumb a:hover {
        text-decoration: underline;
    }
    
    .breadcrumb span {
        color: #a0aec0;
        margin: 0 5px;
    }
    
    /* کارت اصلی */
    .post-card {
        background: white;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.04);
    }
    
    .post-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 2.5rem 2.5rem 2rem 2.5rem;
    }
    
    .post-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        padding: 0.5rem 1.2rem;
        border-radius: 50px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(5px);
    }
    
    .post-back:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateX(-5px);
    }
    
    .post-header h1 {
        font-size: 2.2rem;
        margin-bottom: 1rem;
        line-height: 1.4;
        font-weight: 800;
    }
    
    .post-meta {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .post-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .post-meta i {
        font-size: 0.8rem;
        opacity: 0.8;
    }
    
    .post-meta .menu-link {
        color: white;
        text-decoration: none;
        background: rgba(255, 255, 255, 0.15);
        padding: 0.2rem 0.8rem;
        border-radius: 50px;
        transition: all 0.3s ease;
    }
    
    .post-meta .menu-link:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    /* محتوای پست */
    .post-content {
        padding: 2.5rem;
        color: #2d3748;
        line-height: 2;
        font-size: 1.05rem;
    }
    
    .post-content p {
        margin-bottom: 1.5rem;
    }
    
    .post-content h2 {
        font-size: 1.5rem;
        color: #2d3748;
        margin: 2rem 0 1rem 0;
        font-weight: 700;
    }
    
    .post-content h3 {
        font-size: 1.2rem;
        color: #2d3748;
        margin: 1.5rem 0 0.8rem 0;
        font-weight: 600;
    }
    
    .post-content ul, .post-content ol {
        margin: 0.5rem 0 1.5rem 0;
        padding-right: 1.5rem;
    }
    
    .post-content li {
        margin-bottom: 0.5rem;
    }
    
    .post-content blockquote {
        border-right: 4px solid #667eea;
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
        background: #f8f9fa;
        border-radius: 0 12px 12px 0;
        color: #4a5568;
    }
    
    .post-content img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 1.5rem 0;
    }
    
    /* فوتر پست */
    .post-footer {
        padding: 1.5rem 2.5rem 2.5rem 2.5rem;
        border-top: 1px solid #e2e8f0;
        background: #fafbfc;
    }
    
    /* ناوبری بین مطالب */
    .post-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    .nav-prev, .nav-next {
        flex: 1;
        min-width: 180px;
    }
    
    .nav-next {
        text-align: left;
        display: flex;
        justify-content: flex-end;
    }
    
    .nav-prev {
        text-align: right;
    }
    
    .nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: white;
        border: 1px solid #e2e8f0;
        padding: 0.7rem 1.2rem;
        border-radius: 50px;
        text-decoration: none;
        color: #667eea;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 0.9rem;
        max-width: 100%;
    }
    
    .nav-btn .nav-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }
    
    .nav-btn i {
        font-size: 0.9rem;
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }
    
    .nav-prev .nav-btn i {
        margin-left: 5px;
    }
    
    .nav-next .nav-btn i {
        margin-right: 5px;
    }
    
    .nav-btn:hover {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .nav-prev .nav-btn:hover i {
        transform: translateX(-5px);
    }
    
    .nav-next .nav-btn:hover i {
        transform: translateX(5px);
    }
    
    .nav-disabled {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #f1f3f5;
        border: 1px solid #e2e8f0;
        padding: 0.7rem 1.2rem;
        border-radius: 50px;
        color: #adb5bd;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: not-allowed;
    }
    
    /* دکمه‌های پایین */
    .post-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }
    
    .post-actions a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .action-category {
        background: #e9ecef;
        color: #667eea;
    }
    
    .action-category:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }
    
    .action-home {
        background: #e9ecef;
        color: #6c757d;
    }
    
    .action-home:hover {
        background: #6c757d;
        color: white;
        transform: translateY(-2px);
    }
    
    /* ======================================== */
    /* استایل‌های ریسپانسیو */
    /* ======================================== */
    @media (max-width: 768px) {
        .post-container {
            padding: 0 1rem;
        }
        
        .post-header {
            padding: 1.5rem;
        }
        
        .post-header h1 {
            font-size: 1.5rem;
        }
        
        .post-content {
            padding: 1.5rem;
            font-size: 1rem;
            line-height: 1.8;
        }
        
        .post-footer {
            padding: 1.5rem;
        }
        
        .post-meta {
            gap: 0.8rem;
            font-size: 0.8rem;
        }
        
        .post-meta .menu-link {
            font-size: 0.75rem;
        }
        
        .post-navigation {
            flex-direction: column;
        }
        
        .nav-prev, .nav-next {
            width: 100%;
            text-align: center !important;
            justify-content: center !important;
        }
        
        .nav-btn, .nav-disabled {
            width: 100%;
            justify-content: center;
        }
        
        .nav-btn .nav-title {
            max-width: 120px;
        }
        
        .post-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .post-actions a {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .post-header h1 {
            font-size: 1.2rem;
        }
        
        .post-content {
            padding: 1rem;
            font-size: 0.95rem;
        }
        
        .post-footer {
            padding: 1rem;
        }
        
        .post-back {
            font-size: 0.8rem;
            padding: 0.4rem 1rem;
        }
        
        .post-meta {
            font-size: 0.7rem;
            gap: 0.5rem;
        }
        
        .nav-btn .nav-title {
            max-width: 80px;
        }
    }
</style>

<!-- ======================================== -->
<!-- محتوای صفحه پست -->
<!-- ======================================== -->

<div class="post-container">
    
    <!-- مسیر راهنما (Breadcrumb) -->
    <nav class="breadcrumb" aria-label="مسیر راهنما">
        <a href="/">خانه</a>
        <span>›</span>
        <a href="index.php?menu=<?= $post['menu_id'] ?>"><?= htmlspecialchars($post['menu_title']) ?></a>
        <span>›</span>
        <span><?= htmlspecialchars(mb_substr($post['title'], 0, 50)) ?>...</span>
    </nav>
    
    <article class="post-card" itemscope itemtype="https://schema.org/Article">
        
        <!-- هدر پست -->
        <header class="post-header">
            <a href="index.php?menu=<?= $post['menu_id'] ?>" class="post-back">
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                بازگشت به <?= htmlspecialchars($post['menu_title']) ?>
            </a>
            
            <h1 itemprop="headline"><?= htmlspecialchars($post['title']) ?></h1>
            
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
                <?php if ($post['updated_at'] && $post['updated_at'] != $post['created_at']): ?>
                    <span>
                        <i class="fas fa-edit" aria-hidden="true"></i>
                        <time datetime="<?= date('Y-m-d', strtotime($post['updated_at'])) ?>" itemprop="dateModified">
                            ویرایش: <?= date('Y/m/d', strtotime($post['updated_at'])) ?>
                        </time>
                    </span>
                <?php endif; ?>
                <span>
                    <i class="fas fa-folder" aria-hidden="true"></i>
                    <a href="index.php?menu=<?= $post['menu_id'] ?>" class="menu-link" itemprop="articleSection">
                        <?= htmlspecialchars($post['menu_title']) ?>
                    </a>
                </span>
            </div>
        </header>
        
        <!-- محتوای پست -->
        <div class="post-content" itemprop="articleBody">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>
        
        <!-- فوتر پست -->
        <footer class="post-footer">
            
            <!-- ناوبری قبلی/بعدی -->
            <div class="post-navigation">
                <div class="nav-prev">
                    <?php if ($prevPost): ?>
                        <a href="post.php?id=<?= $prevPost['id'] ?>" class="nav-btn" title="<?= htmlspecialchars($prevPost['title']) ?>">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            <span class="nav-title"><?= htmlspecialchars($prevPost['title']) ?></span>
                        </a>
                    <?php else: ?>
                        <span class="nav-disabled">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            <span>اولین مطلب</span>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="nav-next">
                    <?php if ($nextPost): ?>
                        <a href="post.php?id=<?= $nextPost['id'] ?>" class="nav-btn" title="<?= htmlspecialchars($nextPost['title']) ?>">
                            <span class="nav-title"><?= htmlspecialchars($nextPost['title']) ?></span>
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <span class="nav-disabled">
                            <span>آخرین مطلب</span>
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- دکمه‌های اقدام -->
            <div class="post-actions">
                <a href="index.php?menu=<?= $post['menu_id'] ?>" class="action-category">
                    <i class="fas fa-folder" aria-hidden="true"></i>
                    همه مطالب <?= htmlspecialchars($post['menu_title']) ?>
                </a>
                <a href="index.php" class="action-home">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    صفحه اصلی
                </a>
            </div>
            
        </footer>
        
    </article>
</div>

<?php include 'footer.php'; ?>