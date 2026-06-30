<?php
// فایل: about.php

// ========================================
// تنظیمات صفحه درباره ما
// ========================================
session_start();

$page_title = 'درباره ما | تیم متخصصان بهره‌وری و هوش مصنوعی';
$meta_description = 'آشنایی با تیم AI Productivity Strategy - سید حسین سیادت و حامد روحانی، متخصصان بهره‌وری و هوش مصنوعی با بیش از ۱۵ سال تجربه';
$meta_keywords = 'درباره ما, تیم بهره‌وری, هوش مصنوعی, سید حسین سیادت, حامد روحانی, استراتژیست, مشاور AI';
$og_title = 'درباره تیم AI Productivity Strategy';
$og_description = 'آشنایی با بنیان‌گذاران و متخصصان مجموعه AI Productivity Strategy در حوزه بهره‌وری و هوش مصنوعی';

include 'header.php';

// ========================================
// تنظیمات نویسندگان
// ========================================
$authors = [
    [
        'id' => 'hossein-siadat',
        'name' => 'سید حسین سیادت',
        'english_name' => 'S. H. Siadat',
        'image' => '/assets/images/hossein-siadat.jpg',
        'title' => 'استراتژیست سیستم‌ها و گردش‌کاری',
        'bio' => [
            'سید حسین سیادت، استراتژیست سیستم‌ها و گردش‌کاری است که در زمینه بهره‌وری مبتنی بر هوش مصنوعی و تحول دیجیتال تخصص دارد.',
            'با بیش از ۱۵ سال تجربه در بهینه‌سازی فرآیندهای کسب‌وکار و بهبود عملکرد مبتنی بر فناوری، او به حرفه‌ای‌ها کمک می‌کند تا نحوه کار خود را در محیط‌های پیچیده و سریع در حال تغییر بازطراحی کنند.',
            'کار او بر روی سیستم‌های عملی متمرکز است که سردرگمی را کاهش می‌دهد، وضوح را بهبود می‌بخشد و هوش مصنوعی را به یک مزیت ساختاری روزانه تبدیل می‌کند.'
        ],
        'linkedin' => 'https://www.linkedin.com/in/hossein-siadat-25147318/',
        'icon' => 'fa-user-tie'
    ],
    [
        'id' => 'hamed-rouhani',
        'name' => 'حامد روحانی',
        'english_name' => 'H. Rouhani',
        'image' => '/assets/images/hamed-rouhani.jpg',
        'title' => 'طراح سیستم‌های اطلاعاتی',
        'bio' => [
            'حامد روحانی، طراح سیستم‌های اطلاعاتی با نزدیک به دو دهه تجربه در توسعه نرم‌افزارهای صنعتی در بخش نفت و گاز است.',
            'او با پیشینه‌ای در مهندسی صنایع و مدیریت فناوری اطلاعات، در زمینه ادغام هوش مصنوعی در سیستم‌های سازمانی، از جمله توسعه به کمک هوش مصنوعی و ابزارهای پشتیبانی هوشمند تخصص دارد.',
            'کار او پل ارتباطی بین پیاده‌سازی فنی و کارایی عملیاتی در دنیای واقعی است.'
        ],
        'linkedin' => 'https://www.linkedin.com/in/hamed-rouhani-b1296164',
        'icon' => 'fa-laptop-code'
    ]
];
?>

<style>
    /* ======================================== */
    /* استایل‌های اختصاصی صفحه درباره ما */
    /* ======================================== */
    .about-container {
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
    
    /* بخش نویسندگان */
    .authors-section {
        margin-bottom: 3rem;
    }
    
    .section-title {
        text-align: center;
        font-size: 2.2rem;
        color: #2d3748;
        margin-bottom: 0.5rem;
        position: relative;
        padding-bottom: 1rem;
        font-weight: 800;
    }
    
    .section-subtitle {
        text-align: center;
        color: #4a5568;
        font-size: 1.1rem;
        margin-bottom: 2.5rem;
        opacity: 0.8;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 3px;
    }
    
    .section-title i {
        color: #667eea;
        margin-left: 10px;
    }
    
    .authors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2.5rem;
        margin-bottom: 3rem;
    }
    
    .author-card {
        background: white;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .author-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px rgba(102, 126, 234, 0.15);
        border-color: rgba(102, 126, 234, 0.2);
    }
    
    .author-avatar {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2.5rem 2rem 1.5rem 2rem;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        position: relative;
    }
    
    .author-avatar::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    
    .author-card:hover .author-avatar::after {
        opacity: 1;
    }
    
    .author-avatar img {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .author-card:hover .author-avatar img {
        transform: scale(1.05);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        border-color: #667eea;
    }
    
    .avatar-placeholder {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: 4px solid white;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .avatar-placeholder i {
        font-size: 5rem;
        color: rgba(255, 255, 255, 0.9);
    }
    
    .author-header {
        text-align: center;
        padding: 1rem 1.5rem 0.5rem 1.5rem;
    }
    
    .author-header h2 {
        font-size: 1.6rem;
        margin-bottom: 0.3rem;
        color: #2d3748;
        font-weight: 700;
    }
    
    .author-header .author-english {
        font-size: 0.95rem;
        color: #a0aec0;
        font-weight: 400;
        display: block;
        margin-bottom: 0.3rem;
    }
    
    .author-header .author-title {
        font-size: 1rem;
        color: #667eea;
        font-weight: 500;
        display: block;
    }
    
    .author-body {
        padding: 0 2rem 2rem 2rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .author-bio {
        color: #4a5568;
        line-height: 1.9;
        margin-bottom: 1.8rem;
        text-align: justify;
    }
    
    .author-bio p {
        margin-bottom: 0.8rem;
    }
    
    .author-bio p:last-child {
        margin-bottom: 0;
    }
    
    .author-bio strong {
        color: #2d3748;
    }
    
    .linkedin-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #0077b5;
        color: white;
        padding: 10px 24px;
        border-radius: 50px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-top: auto;
        align-self: flex-start;
        border: none;
        cursor: pointer;
    }
    
    .linkedin-link:hover {
        background: #005e8c;
        transform: translateX(-5px);
        color: white;
        box-shadow: 0 5px 20px rgba(0, 119, 181, 0.3);
    }
    
    .linkedin-link i {
        font-size: 1.2rem;
    }
    
    /* ======================================== */
    /* دکمه گیت‌هاب (اضافه شده) */
    /* ======================================== */
    .github-section {
        text-align: center;
        margin: 2.5rem 0 3rem 0;
    }
    
    .github-btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: #24292e;
        color: white !important;
        padding: 14px 35px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.05rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        box-shadow: 0 5px 15px rgba(36, 41, 46, 0.2);
    }
    
    .github-btn:hover {
        background: #2d3748;
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(36, 41, 46, 0.3);
        border-color: #667eea;
        color: white !important;
    }
    
    .github-btn i {
        font-size: 1.6rem;
    }
    
    .github-btn .arrow-icon {
        transition: transform 0.3s ease;
        font-size: 0.9rem;
    }
    
    .github-btn:hover .arrow-icon {
        transform: translateX(-5px);
    }
    
    /* ======================================== */
    /* بخش درباره سایت */
    /* ======================================== */
    .about-site {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 25px;
        padding: 3rem;
        color: white;
        text-align: center;
        margin-top: 1rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .about-site h3 {
        font-size: 2rem;
        margin-bottom: 1rem;
        font-weight: 800;
    }
    
    .about-site p {
        font-size: 1.05rem;
        line-height: 1.9;
        opacity: 0.95;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .about-site i {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        display: inline-block;
        opacity: 0.9;
    }
    
    /* ======================================== */
    /* استایل‌های ریسپانسیو */
    /* ======================================== */
    @media (max-width: 992px) {
        .authors-grid {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
    }
    
    @media (max-width: 768px) {
        .about-container {
            padding: 0 1rem;
        }
        
        .authors-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .section-title {
            font-size: 1.8rem;
        }
        
        .section-subtitle {
            font-size: 1rem;
        }
        
        .author-avatar img,
        .avatar-placeholder {
            width: 160px;
            height: 160px;
        }
        
        .author-avatar {
            padding: 2rem 1.5rem 1rem 1.5rem;
        }
        
        .author-header h2 {
            font-size: 1.3rem;
        }
        
        .author-body {
            padding: 0 1.5rem 1.5rem 1.5rem;
        }
        
        .about-site {
            padding: 2rem 1.5rem;
        }
        
        .about-site h3 {
            font-size: 1.5rem;
        }
        
        .about-site p {
            font-size: 0.95rem;
        }
        
        .about-site i {
            font-size: 2.5rem;
        }
        
        .github-btn {
            padding: 12px 25px;
            font-size: 0.95rem;
        }
        
        .github-btn i {
            font-size: 1.3rem;
        }
    }
    
    @media (max-width: 480px) {
        .section-title {
            font-size: 1.5rem;
        }
        
        .author-avatar img,
        .avatar-placeholder {
            width: 130px;
            height: 130px;
        }
        
        .author-avatar {
            padding: 1.5rem 1rem 0.5rem 1rem;
        }
        
        .author-bio {
            line-height: 1.7;
            font-size: 0.9rem;
        }
        
        .linkedin-link {
            padding: 8px 18px;
            font-size: 12px;
        }
        
        .avatar-placeholder i {
            font-size: 3rem;
        }
        
        .about-site {
            padding: 1.5rem;
        }
        
        .about-site h3 {
            font-size: 1.2rem;
        }
        
        .about-site p {
            font-size: 0.85rem;
        }
        
        .github-btn {
            padding: 10px 20px;
            font-size: 0.85rem;
            gap: 8px;
        }
        
        .github-btn i {
            font-size: 1.1rem;
        }
    }
</style>

<!-- ======================================== -->
<!-- محتوای صفحه درباره ما -->
<!-- ======================================== -->
<div class="about-container">
    
    <!-- مسیر راهنما (Breadcrumb) -->
    <nav class="breadcrumb" aria-label="مسیر راهنما">
        <a href="/">خانه</a>
        <span> / </span>
        <span>درباره ما</span>
    </nav>
    
    <!-- بخش معرفی نویسندگان -->
    <section class="authors-section">
        <h1 class="section-title">
            <i class="fas fa-users" aria-hidden="true"></i>
            درباره نویسندگان
        </h1>
        <p class="section-subtitle">
            آشنایی با بنیان‌گذاران و متخصصان مجموعه AI Productivity Strategy
        </p>
        
        <div class="authors-grid">
            
            <?php foreach ($authors as $author): ?>
            <article class="author-card" itemscope itemtype="https://schema.org/Person">
                <div class="author-avatar">
                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $author['image'])): ?>
                        <img src="<?= htmlspecialchars($author['image']) ?>" 
                             alt="<?= htmlspecialchars($author['name']) ?> - <?= htmlspecialchars($author['title']) ?>"
                             itemprop="image"
                             loading="lazy"
                             width="200"
                             height="200">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <i class="fas <?= htmlspecialchars($author['icon']) ?>" aria-hidden="true"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="author-header">
                    <h2 itemprop="name"><?= htmlspecialchars($author['name']) ?></h2>
                    <span class="author-english" itemprop="alternateName"><?= htmlspecialchars($author['english_name']) ?></span>
                    <span class="author-title" itemprop="jobTitle"><?= htmlspecialchars($author['title']) ?></span>
                </div>
                
                <div class="author-body">
                    <div class="author-bio" itemprop="description">
                        <?php foreach ($author['bio'] as $paragraph): ?>
                            <p><?= htmlspecialchars($paragraph) ?></p>
                        <?php endforeach; ?>
                    </div>
                    
                    <a href="<?= htmlspecialchars($author['linkedin']) ?>" 
                       target="_blank" 
                       class="linkedin-link" 
                       rel="noopener noreferrer"
                       itemprop="sameAs">
                        <i class="fab fa-linkedin" aria-hidden="true"></i>
                        پروفایل لینکدین
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
            
        </div>
    </section>
    
    <!-- ======================================== -->
    <!-- ✅ بخش دکمه گیت‌هاب (اضافه شده) -->
    <!-- ======================================== -->
    <div class="github-section">
        <a href="https://github.com/HamedRouhani/AIProductivitySterategy.ir-WebSite" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="github-btn">
            <i class="fab fa-github" aria-hidden="true"></i>
            <span>مشاهده کد منبع پروژه در گیت‌هاب</span>
            <i class="fas fa-arrow-left arrow-icon" aria-hidden="true"></i>
        </a>
    </div>
    
    <!-- بخش درباره سایت -->
    <section class="about-site" itemscope itemtype="https://schema.org/AboutPage">
        <i class="fas fa-brain" aria-hidden="true"></i>
        <h3 itemprop="name">AI Productivity Strategy</h3>
        <p itemprop="description">
            ما به شما یاد می‌دهیم که با استفاده از ابزارهای <strong>هوش مصنوعی</strong>، بهره‌وری خود را تا چندین برابر افزایش دهید. 
            هدف ما ارائه محتوای کاربردی و به‌روز در زمینه استفاده از هوش مصنوعی برای بهبود کارایی فردی و سازمانی است.
            با ما همراه باشید تا در <strong>عصر تحول دیجیتال</strong>، از قدرت AI برای رشد و پیشرفت استفاده کنید.
        </p>
    </section>
</div>

<?php include 'footer.php'; ?>