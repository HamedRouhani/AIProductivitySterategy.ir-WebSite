<?php
// software.php
// ========================================
// تنظیمات صفحه نرم‌افزارها
// ========================================
session_start();

$page_title = 'نرم‌افزارهای در حال توسعه | AI Productivity Strategy';
$meta_description = 'نرم‌افزارهای هوشمند برای افزایش بهره‌وری و تحلیل کسب‌وکار - معرفی پروژه‌های در حال توسعه';
$meta_keywords = 'نرم‌افزار, بهره‌وری, هوش مصنوعی, BABOK, تحلیل کسب‌وکار, توسعه نرم‌افزار';
$og_title = 'نرم‌افزارهای در حال توسعه - AI Productivity Strategy';
$og_description = 'پروژه‌های نرم‌افزاری با هدف افزایش بهره‌وری و تحلیل هوشمند';

include 'header.php';
?>

<style>
    /* ======================================== */
    /* استایل‌های اختصاصی صفحه نرم‌افزارها */
    /* ======================================== */
    .software-container {
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
    
    /* هدر صفحه */
    .page-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .page-header h1 {
        font-size: 2.2rem;
        color: #2d3748;
        margin-bottom: 0.5rem;
        font-weight: 800;
    }
    
    .page-header h1 i {
        color: #667eea;
        margin-left: 10px;
    }
    
    .page-header .subtitle {
        color: #4a5568;
        font-size: 1.1rem;
        opacity: 0.8;
    }
    
    .page-header:after {
        content: '';
        display: block;
        width: 80px;
        height: 4px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 3px;
        margin: 1rem auto 0;
    }
    
    /* کارت اصلی پروژه */
    .project-card {
        background: white;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        margin-bottom: 2.5rem;
    }
    
    .project-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px rgba(102, 126, 234, 0.15);
        border-color: rgba(102, 126, 234, 0.2);
    }
    
    .project-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 1.5rem 2rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
    
    .project-title {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .project-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }
    
    .project-title h2 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 700;
        color: #2d3748;
    }
    
    .project-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.3rem;
    }
    
    .project-badges .badge {
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .badge-version {
        background: #ffc107;
        color: #212529;
    }
    
    .badge-status {
        background: #28a745;
        color: white;
    }
    
    .btn-enter {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 0.6rem 1.8rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-enter:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        color: white;
    }
    
    .project-body {
        padding: 2rem;
    }
    
    .project-description {
        background: #f8fafc;
        padding: 1.2rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border-right: 4px solid #667eea;
    }
    
    .project-description p {
        margin: 0;
        color: #4a5568;
        line-height: 1.8;
    }
    
    .project-description strong {
        color: #2d3748;
    }
    
    /* گرید کارت‌های آماری */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem 1rem;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }
    
    .stat-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: inline-block;
    }
    
    .stat-icon.primary { color: #4e73df; }
    .stat-icon.warning { color: #f6c23e; }
    .stat-icon.info { color: #36b9cc; }
    .stat-icon.success { color: #1cc88a; }
    
    .stat-number {
        display: block;
        font-size: 2.2rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }
    
    .stat-label {
        display: block;
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 0.2rem;
    }
    
    /* گرید کارت‌های اطلاعات */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }
    
    .info-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    
    .info-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }
    
    .info-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: inline-block;
    }
    
    .info-icon.success { color: #28a745; }
    .info-icon.secondary { color: #6c757d; }
    .info-icon.primary { color: #4e73df; }
    
    .info-title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.3rem;
        font-size: 1rem;
    }
    
    .info-text {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 0.5rem;
        margin-bottom: 0;
    }
    
    .info-card .badge {
        padding: 0.3rem 0.8rem;
        border-radius: 50px;
        font-size: 0.8rem;
    }
    
    .info-card .badge-success { background: #28a745; color: white; }
    .info-card .badge-secondary { background: #6c757d; color: white; }
    .info-card .badge-primary { background: #4e73df; color: white; }
    
    /* بخش پایین */
    .bottom-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .bottom-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }
    
    .bottom-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
    }
    
    .bottom-icon {
        width: 65px;
        height: 65px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.8rem;
    }
    
    .bottom-icon.primary {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .bottom-icon.warning {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }
    
    .bottom-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .bottom-card p {
        color: #4a5568;
        font-size: 0.95rem;
    }
    
    .btn-outline {
        background: transparent;
        color: #667eea;
        border: 2px solid #667eea;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        margin-top: 0.5rem;
    }
    
    .btn-outline:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    /* ======================================== */
    /* استایل‌های ریسپانسیو */
    /* ======================================== */
    @media (max-width: 992px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .info-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .software-container {
            padding: 0 1rem;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
        }
        
        .project-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 1.2rem;
        }
        
        .project-title h2 {
            font-size: 1.2rem;
        }
        
        .project-body {
            padding: 1.2rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .stat-number {
            font-size: 1.6rem;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .bottom-grid {
            grid-template-columns: 1fr;
        }
        
        .btn-enter {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .page-header .subtitle {
            font-size: 0.95rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
        
        .stat-card {
            padding: 1rem 0.5rem;
        }
        
        .stat-number {
            font-size: 1.3rem;
        }
        
        .stat-label {
            font-size: 0.7rem;
        }
        
        .stat-icon {
            font-size: 1.5rem;
        }
        
        .project-description {
            padding: 1rem;
            font-size: 0.9rem;
        }
        
        .project-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }
    }
</style>

<!-- ======================================== -->
<!-- محتوای صفحه نرم‌افزارها -->
<!-- ======================================== -->
<div class="software-container">
    
    <!-- مسیر راهنما (Breadcrumb) -->
    <nav class="breadcrumb" aria-label="مسیر راهنما">
        <a href="/">خانه</a>
        <span> / </span>
        <span>نرم‌افزارهای در حال توسعه</span>
    </nav>
    
    <!-- هدر صفحه -->
    <div class="page-header">
        <h1>
            <i class="fas fa-code" aria-hidden="true"></i>
            نرم‌افزارهای در حال توسعه
        </h1>
        <p class="subtitle">پروژه‌های نرم‌افزاری با هدف افزایش بهره‌وری و تحلیل هوشمند</p>
    </div>
    
    <!-- کارت اصلی پروژه BABOK Analyzer -->
    <div class="project-card">
        <div class="project-header">
            <div class="project-title">
                <div class="project-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <div>
                    <h2>BABOK Analyzer</h2>
                    <div class="project-badges">
                        <span class="badge badge-version">
                            <i class="fas fa-code-branch"></i> v1.0.0
                        </span>
                        <span class="badge badge-status">
                            <i class="fas fa-check-circle"></i> فعال
                        </span>
                    </div>
                </div>
            </div>
            <a href="https://www.aiproductivitystrategy.ir/babok" target="_blank" class="btn-enter">
                <i class="fas fa-external-link-alt"></i>
                ورود به نرم‌افزار
            </a>
        </div>
        
        <div class="project-body">
            <!-- توضیحات -->
            <div class="project-description">
                <p>
                    <i class="fas fa-quote-right text-primary me-2"></i>
                    ابزار مدیریت و تحلیل پروژه‌های مبتنی بر استاندارد 
                    <strong>BABOK (Business Analysis Body of Knowledge)</strong> 
                    نسخه ۳، با قابلیت پیشنهاد هوشمند تکنیک‌ها.
                </p>
            </div>
            
            <!-- کارت‌های آماری -->
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-icon primary"><i class="fas fa-layer-group"></i></span>
                    <span class="stat-number">۶</span>
                    <span class="stat-label">حوزه‌های دانشی</span>
                </div>
                <div class="stat-card">
                    <span class="stat-icon warning"><i class="fas fa-tasks"></i></span>
                    <span class="stat-number">۲۹</span>
                    <span class="stat-label">وظایف استاندارد</span>
                </div>
                <div class="stat-card">
                    <span class="stat-icon info"><i class="fas fa-tools"></i></span>
                    <span class="stat-number">۵۰</span>
                    <span class="stat-label">تکنیک‌های استاندارد</span>
                </div>
                <div class="stat-card">
                    <span class="stat-icon success"><i class="fas fa-project-diagram"></i></span>
                    <span class="stat-number">۱</span>
                    <span class="stat-label">پروژه فعال</span>
                </div>
            </div>
            
            <!-- کارت‌های اطلاعات تکمیلی -->
            <div class="info-grid">
                <div class="info-card">
                    <span class="info-icon success"><i class="fas fa-microchip"></i></span>
                    <h6 class="info-title">پیشنهاد هوشمند</h6>
                    <span class="badge badge-secondary">در حال تکمیل</span>
                    <p class="info-text">پیشنهاد تکنیک‌ها بر اساس زمینه پروژه و تحلیل متن نیازمندی‌ها</p>
                </div>
                <div class="info-card">
                    <span class="info-icon secondary"><i class="fas fa-calendar-alt"></i></span>
                    <h6 class="info-title">وضعیت توسعه</h6>
                    <span class="badge badge-secondary">در حال تکمیل</span>
                    <p class="info-text">نسخه قابل استفاده اولیه منتشر شده و توسعه ادامه دارد</p>
                </div>
                <div class="info-card">
                    <span class="info-icon primary"><i class="fas fa-code"></i></span>
                    <h6 class="info-title">آخرین بروزرسانی</h6>
                    <span class="badge badge-primary">۲۹ تیر ۱۴۰۵</span>
                    <p class="info-text">
                        <a href="https://github.com/HamedRouhani/babok-analyzer" target="_blank" class="text-decoration-none">
                            <i class="fab fa-github me-1"></i> مشاهده مخزن کد
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- بخش پایین -->
    <div class="bottom-grid">
        <div class="bottom-card">
            <div class="bottom-icon primary">
                <i class="fas fa-rocket"></i>
            </div>
            <h5>پروژه بعدی در راه است</h5>
            <p>به زودی نرم‌افزارهای جدیدی برای افزایش بهره‌وری شما معرفی خواهند شد.</p>
        </div>
        <div class="bottom-card">
            <div class="bottom-icon warning">
                <i class="fas fa-lightbulb"></i>
            </div>
            <h5>پیشنهاد یا همکاری</h5>
            <p>اگر ایده یا پیشنهادی برای توسعه نرم‌افزار دارید، با ما در میان بگذارید.</p>
            <a href="contact.php" class="btn-outline">
                <i class="fas fa-paper-plane me-2"></i>ارسال پیام
            </a>
        </div>
    </div>
    
</div>

<?php include 'footer.php'; ?>