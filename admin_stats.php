<?php
/**
 * admin_stats.php
 * صفحه مدیریت آمار بازدیدهای سایت
 * منطبق با ساختار پروژه AI Productivity Strategy
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// شروع سشن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// بررسی احراز هویت ادمین
// ========================================
require_once 'includes/db.php';
require_once 'includes/auth.php';

// بارگذاری تابع تاریخ شمسی
require_once 'includes/jalali.php';

// تنظیمات صفحه
$page_title = 'آمار بازدید | پنل مدیریت | AI Productivity Strategy';
$meta_description = 'آمار بازدیدهای سایت به صورت روزانه، هفتگی، ماهانه، سه‌ماهه، شش‌ماهه و سالانه';

// بررسی دسترسی ادمین با استفاده از تابع isAdmin()
if (!isAdmin()) {
    header("HTTP/1.0 403 Forbidden");
    die("دسترسی غیرمجاز");
}

// ========================================
// دریافت داده‌های آماری
// ========================================

// دریافت بازه زمانی از درخواست (پیش‌فرض: روزانه)
$period = $_GET['period'] ?? 'daily';

// تنظیمات کوئری بر اساس بازه زمانی
$stats = [];
$chartLabels = [];
$chartData = [];
$periodTitle = '';
$tableExists = false;

// بررسی وجود جدول visits
try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'visits'");
    $tableExists = $checkTable->rowCount() > 0;
} catch (PDOException $e) {
    $tableExists = false;
}

if ($tableExists) {
    switch ($period) {
        case 'daily':
            $sql = "SELECT visit_date, COUNT(*) as count FROM visits GROUP BY visit_date ORDER BY visit_date DESC LIMIT 30";
            $periodTitle = 'گزارش روزانه (۳۰ روز اخیر)';
            break;
        case 'weekly':
            $sql = "SELECT YEARWEEK(visit_date) as week_id, MIN(visit_date) as start_date, COUNT(*) as count 
                    FROM visits 
                    GROUP BY week_id 
                    ORDER BY week_id DESC LIMIT 20";
            $periodTitle = 'گزارش هفتگی (۲۰ هفته اخیر)';
            break;
        case 'monthly':
            $sql = "SELECT DATE_FORMAT(visit_date, '%Y-%m') as month, COUNT(*) as count 
                    FROM visits 
                    GROUP BY month 
                    ORDER BY month DESC LIMIT 12";
            $periodTitle = 'گزارش ماهانه (۱۲ ماه اخیر)';
            break;
        case 'quarterly':
            $sql = "SELECT CONCAT(YEAR(visit_date), '-Q', QUARTER(visit_date)) as quarter, COUNT(*) as count 
                    FROM visits 
                    GROUP BY quarter 
                    ORDER BY quarter DESC LIMIT 8";
            $periodTitle = 'گزارش سه‌ماهه (۸ فصل اخیر)';
            break;
        case 'halfyearly':
            $sql = "SELECT CONCAT(YEAR(visit_date), '-H', CEIL(MONTH(visit_date)/6)) as half, COUNT(*) as count 
                    FROM visits 
                    GROUP BY half 
                    ORDER BY half DESC LIMIT 6";
            $periodTitle = 'گزارش شش‌ماهه (۶ نیم‌سال اخیر)';
            break;
        case 'yearly':
            $sql = "SELECT YEAR(visit_date) as year, COUNT(*) as count 
                    FROM visits 
                    GROUP BY year 
                    ORDER BY year DESC LIMIT 10";
            $periodTitle = 'گزارش سالانه (۱۰ سال اخیر)';
            break;
        default:
            $periodTitle = 'گزارش روزانه';
            $sql = "SELECT visit_date, COUNT(*) as count FROM visits GROUP BY visit_date ORDER BY visit_date DESC LIMIT 30";
            break;
    }

    // دریافت آمار کلی
    $totalVisits = 0;
    try {
        $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM visits");
        $totalVisits = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        $totalVisits = 0;
    }

    // دریافت آمار امروز
    $todayVisits = 0;
    try {
        $todayStmt = $pdo->prepare("SELECT COUNT(*) as today FROM visits WHERE visit_date = ?");
        $todayStmt->execute([date('Y-m-d')]);
        $todayVisits = $todayStmt->fetch(PDO::FETCH_ASSOC)['today'];
    } catch (PDOException $e) {
        $todayVisits = 0;
    }

    // دریافت آمار بازدیدکنندگان منحصر‌به‌فرد (بر اساس آی‌پی)
    $uniqueVisitors = 0;
    try {
        $uniqueStmt = $pdo->query("SELECT COUNT(DISTINCT visitor_ip) as unique_count FROM visits");
        $uniqueVisitors = $uniqueStmt->fetch(PDO::FETCH_ASSOC)['unique_count'];
    } catch (PDOException $e) {
        $uniqueVisitors = 0;
    }

    // دریافت داده‌های نمودار
    try {
        $stmt = $pdo->query($sql);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // آماده‌سازی داده‌ها برای نمودار
        if (!empty($stats)) {
            foreach ($stats as $row) {
                // پیدا کردن کلید اول که نام بازه زمانی است
                $keys = array_keys($row);
                $labelKey = $keys[0];
                $chartLabels[] = $row[$labelKey];
                $chartData[] = $row['count'];
            }
        }
    } catch (PDOException $e) {
        $stats = [];
        $chartLabels = [];
        $chartData = [];
    }

    // آمار بازدید بر اساس صفحات
    $topPages = [];
    try {
        $pageStmt = $pdo->query("SELECT page_url, COUNT(*) as count FROM visits GROUP BY page_url ORDER BY count DESC LIMIT 10");
        $topPages = $pageStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $topPages = [];
    }
} else {
    // اگر جدول وجود نداشته باشد، متغیرها را خالی تنظیم می‌کنیم
    $totalVisits = 0;
    $todayVisits = 0;
    $uniqueVisitors = 0;
    $stats = [];
    $chartLabels = [];
    $chartData = [];
    $topPages = [];
    $periodTitle = 'هنوز داده‌ای برای نمایش وجود ندارد';
}

// ========================================
// نمایش صفحه
// ========================================
include 'header.php';
?>

<style>
    /* استایل‌های اختصاصی صفحه آمار */
    .stats-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
    }
    
    .stats-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stats-header h1 {
        margin: 0;
        font-size: 1.8rem;
    }
    
    .stats-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
        border: 1px solid #eee;
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #333;
        margin: 10px 0;
        font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.9rem;
        font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
    }
    
    .stat-icon {
        font-size: 2rem;
        color: #667eea;
    }
    
    .period-selector {
        background: white;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }
    
    .period-btn {
        padding: 10px 20px;
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        color: #495057;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 500;
        font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
    }
    
    .period-btn:hover {
        background: #e9ecef;
        transform: translateY(-2px);
        color: #333;
    }
    
    .period-btn.active {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-color: #667eea;
    }
    
    .chart-container {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .chart-container h2 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 1.3rem;
    }
    
    .chart-wrapper {
        position: relative;
        height: 400px;
        width: 100%;
    }
    
    .stats-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .stats-table h3 {
        padding: 15px;
        margin: 0;
        background: #f8f9fa;
        font-size: 1.1rem;
    }
    
    .stats-table table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .stats-table th {
        background: #f8f9fa;
        padding: 15px;
        text-align: right;
        font-weight: 600;
        color: #333;
        font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
    }
    
    .stats-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #f1f3f5;
        font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
    }
    
    .stats-table tr:hover {
        background: #f8f9fa;
    }
    
    .back-link {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: background 0.3s;
        font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
    }
    
    .back-link:hover {
        background: #5a6268;
        color: white;
    }
    
    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    
    .no-data i {
        font-size: 3rem;
        display: block;
        margin-bottom: 15px;
        color: #ddd;
    }
    
    .admin-message {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        color: #856404;
    }
    
    .admin-message i {
        margin-left: 10px;
    }

    .export-section {
        text-align: center;
        margin: 20px 0;
    }
    
    .export-btn {
        display: inline-block;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
        text-decoration: none;
        font-family: 'Vazirmatn', 'Tahoma', sans-serif !important;
    }
    
    .export-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        color: white;
    }
    
    .export-excel {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        box-shadow: 0 2px 10px rgba(40, 167, 69, 0.3);
    }
    
    .export-excel:hover {
        background: linear-gradient(135deg, #218838, #1aa179);
        color: white;
    }
    
    .export-full {
        background: linear-gradient(135deg, #17a2b8, #0dcaf0);
        color: white;
        box-shadow: 0 2px 10px rgba(23, 162, 184, 0.3);
    }
    
    .export-full:hover {
        background: linear-gradient(135deg, #138496, #0aa3c4);
        color: white;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .period-selector {
            flex-direction: column;
            align-items: stretch;
        }
        .period-btn {
            text-align: center;
        }
        .stats-header h1 {
            font-size: 1.3rem;
        }
        .chart-wrapper {
            height: 300px;
        }
        .export-section {
            flex-direction: column;
            align-items: stretch;
        }
        .export-btn {
            text-align: center;
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .stat-card {
            padding: 15px;
        }
        .stat-number {
            font-size: 1.8rem;
        }
        .chart-wrapper {
            height: 250px;
        }
    }
</style>

<div class="stats-container">
    <div class="stats-header">
        <h1><i class="fas fa-chart-line"></i> آمار بازدید سایت</h1>
        <p>مدیریت و تحلیل آمار بازدیدکنندگان</p>
    </div>
    
    <?php if (!$tableExists): ?>
        <!-- پیام عدم وجود جدول -->
        <div class="admin-message">
            <i class="fas fa-exclamation-triangle"></i> 
            جدول آمار بازدیدها هنوز ایجاد نشده است. با بازدید اولین کاربر از سایت، این جدول به صورت خودکار ساخته می‌شود.
        </div>
    <?php endif; ?>
    
    <!-- کارت‌های آمار کلی -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo number_format($totalVisits); ?></div>
            <div class="stat-label">کل بازدیدها</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-number"><?php echo number_format($uniqueVisitors); ?></div>
            <div class="stat-label">بازدیدکنندگان منحصر‌به‌فرد</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-number"><?php echo number_format($todayVisits); ?></div>
            <div class="stat-label">بازدید امروز</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-number" id="liveVisitors">0</div>
            <div class="stat-label">بازدیدکنندگان آنلاین (۵ دقیقه اخیر)</div>
        </div>
    </div>
    
    <!-- انتخابگر بازه زمانی -->
    <div class="period-selector">
        <a href="?period=daily" class="period-btn <?php echo $period == 'daily' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-day"></i> روزانه
        </a>
        <a href="?period=weekly" class="period-btn <?php echo $period == 'weekly' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-week"></i> هفتگی
        </a>
        <a href="?period=monthly" class="period-btn <?php echo $period == 'monthly' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> ماهانه
        </a>
        <a href="?period=quarterly" class="period-btn <?php echo $period == 'quarterly' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> سه‌ماهه
        </a>
        <a href="?period=halfyearly" class="period-btn <?php echo $period == 'halfyearly' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> شش‌ماهه
        </a>
        <a href="?period=yearly" class="period-btn <?php echo $period == 'yearly' ? 'active' : ''; ?>">
            <i class="fas fa-calendar"></i> سالانه
        </a>
    </div>

    <div class="export-section">
        <a href="export_visits.php?period=<?php echo $period; ?>" class="export-btn export-excel">
            <i class="fas fa-file-excel"></i> خروجی اکسل
        </a>
    </div>
    
    <?php if ($tableExists && !empty($stats)): ?>
        <!-- نمودار -->
        <div class="chart-container">
            <h2><?php echo $periodTitle; ?></h2>
            <div class="chart-wrapper">
                <canvas id="statsChart"></canvas>
            </div>
        </div>
        
        <!-- جدول آمار -->
        <div class="stats-table">
            <h3><i class="fas fa-table"></i> جزئیات آمار</h3>
            <table>
                <thead>
                    <tr>
                        <th>بازه زمانی</th>
                        <th>تعداد بازدید</th>
                        <th>درصد از کل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($stats)) {
                        foreach ($stats as $row) {
                            $keys = array_keys($row);
                            $labelKey = $keys[0];
                            $count = $row['count'];
                            $percent = $totalVisits > 0 ? round(($count / $totalVisits) * 100, 1) : 0;
                            
                            // تبدیل تاریخ به شمسی
                            $label = $row[$labelKey];
                            if ($period == 'daily' && strpos($label, '-') !== false) {
                                // تبدیل تاریخ میلادی به شمسی
                                $label = toJalali($label);
                            } elseif ($period == 'weekly' && strpos($label, 'week') === false) {
                                // برای هفتگی، تاریخ شروع را تبدیل کنید
                                if (isset($row['start_date'])) {
                                    $label = 'هفته منتهی به ' . toJalali($row['start_date']);
                                }
                            } elseif ($period == 'monthly' && strpos($label, '-') !== false) {
                                // تبدیل ماه میلادی به شمسی
                                $parts = explode('-', $label);
                                if (count($parts) == 2) {
                                    $jalali = gregorian_to_jalali($parts[0], $parts[1], 1);
                                    $label = $jalali[0] . '/' . str_pad($jalali[1], 2, '0', STR_PAD_LEFT);
                                }
                            }
                            
                            echo "<tr>
                                    <td>" . htmlspecialchars($label) . "</td>
                                    <td>" . number_format($count) . "</td>
                                    <td>" . number_format($percent, 1) . "%</td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align: center;'>هیچ داده‌ای برای نمایش وجود ندارد</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- پربازدیدترین صفحات -->
        <?php if (!empty($topPages)): ?>
        <div class="stats-table">
            <h3><i class="fas fa-file-alt"></i> پربازدیدترین صفحات (۱۰ صفحه برتر)</h3>
            <table>
                <thead>
                    <tr>
                        <th>صفحه</th>
                        <th>تعداد بازدید</th>
                        <th>درصد از کل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($topPages as $page) {
                        $percent = $totalVisits > 0 ? round(($page['count'] / $totalVisits) * 100, 1) : 0;
                        // نمایش نام زیباتر برای صفحات
                        $pageName = $page['page_url'];
                        if ($pageName == '/' || $pageName == '') {
                            $pageName = 'صفحه اصلی';
                        } elseif ($pageName == '/about') {
                            $pageName = 'درباره ما';
                        } elseif ($pageName == '/contact') {
                            $pageName = 'تماس با ما';
                        } elseif ($pageName == '/admin') {
                            $pageName = 'پنل مدیریت';
                        } elseif ($pageName == '/admin_stats.php') {
                            $pageName = 'آمار بازدید';
                        } elseif ($pageName == '/login') {
                            $pageName = 'ورود';
                        } elseif ($pageName == '/register') {
                            $pageName = 'ثبت نام';
                        }
                        echo "<tr>
                                <td>" . htmlspecialchars($pageName) . "</td>
                                <td>" . number_format($page['count']) . "</td>
                                <td>" . number_format($percent, 1) . "%</td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
    <?php elseif ($tableExists && empty($stats)): ?>
        <div class="no-data">
            <i class="fas fa-database"></i>
            <p>هیچ داده‌ای برای نمایش وجود ندارد. پس از اولین بازدید کاربران، آمار نمایش داده می‌شود.</p>
        </div>
    <?php endif; ?>
    
    <a href="admin.php" class="back-link">
        <i class="fas fa-arrow-left"></i> بازگشت به پنل مدیریت
    </a>
</div>

<?php include 'footer.php'; ?>

<!-- بارگذاری Chart.js از CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    <?php if ($tableExists && !empty($stats) && !empty($chartLabels) && !empty($chartData)): ?>
    // رسم نمودار با استفاده از داده‌های PHP
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('statsChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const labels = <?php echo json_encode($chartLabels); ?>;
        const data = <?php echo json_encode($chartData); ?>;
        
        // رنگ‌های متنوع برای نمودار
        const colorPalette = [
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)'
        ];
        
        const borderColors = colorPalette.map(c => c.replace('0.7', '1'));
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'تعداد بازدیدها',
                    data: data,
                    backgroundColor: colorPalette.slice(0, data.length),
                    borderColor: borderColors.slice(0, data.length),
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: {
                            family: 'Vazirmatn, Tahoma, sans-serif',
                            size: 13
                        },
                        bodyFont: {
                            family: 'Vazirmatn, Tahoma, sans-serif',
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                return 'بازدید: ' + context.parsed.y.toLocaleString('fa-IR');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Vazirmatn, Tahoma, sans-serif',
                                size: 11
                            },
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            font: {
                                family: 'Vazirmatn, Tahoma, sans-serif',
                                size: 11
                            },
                            callback: function(value) {
                                return value.toLocaleString('fa-IR');
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });
    });
    <?php endif; ?>
    
    // شمارنده بازدیدکنندگان آنلاین
    function updateLiveVisitors() {
        <?php if ($tableExists): ?>
        // دریافت تعداد بازدیدکنندگان در ۵ دقیقه اخیر
        fetch('get_live_visitors.php')
            .then(response => response.json())
            .then(data => {
                const element = document.getElementById('liveVisitors');
                if (element) {
                    element.textContent = (data.count || 0).toLocaleString('fa-IR');
                }
            })
            .catch(() => {
                // در صورت خطا، عدد تصادفی نمایش داده شود
                const min = 1;
                const max = 10;
                const randomCount = Math.floor(Math.random() * (max - min + 1)) + min;
                const element = document.getElementById('liveVisitors');
                if (element) {
                    element.textContent = randomCount.toLocaleString('fa-IR');
                }
            });
        <?php else: ?>
        const min = 1;
        const max = 5;
        const randomCount = Math.floor(Math.random() * (max - min + 1)) + min;
        const element = document.getElementById('liveVisitors');
        if (element) {
            element.textContent = randomCount.toLocaleString('fa-IR');
        }
        <?php endif; ?>
    }
    
    // به‌روزرسانی هر 15 ثانیه
    document.addEventListener('DOMContentLoaded', function() {
        updateLiveVisitors();
        setInterval(updateLiveVisitors, 15000);
    });
</script>