<?php
/**
 * export_visits.php
 * خروجی اکسل از آمار بازدیدها
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/jalali.php';

// بررسی دسترسی ادمین
if (!isAdmin()) {
    header("HTTP/1.0 403 Forbidden");
    die("دسترسی غیرمجاز");
}

// دریافت بازه زمانی
$period = $_GET['period'] ?? 'daily';

// تنظیم هدرهای خروجی اکسل
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="آمار_بازدید_' . date('Y-m-d') . '.xls"');

// شروع خروجی
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>آمار بازدید</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
echo '<style>
    th { background-color: #4CAF50; color: white; font-weight: bold; padding: 8px; border: 1px solid #ddd; }
    td { padding: 8px; border: 1px solid #ddd; }
    .header { background-color: #667eea; color: white; font-size: 18px; padding: 10px; }
    .total { background-color: #f8f9fa; font-weight: bold; }
</style>';
echo '</head><body>';
echo '<h2>📊 گزارش آمار بازدید</h2>';
echo '<p>تاریخ گزارش: ' . getCurrentJalaliDate() . '</p>';
echo '<p>نوع گزارش: ' . getPeriodTitle($period) . '</p>';

// دریافت داده‌ها
try {
    switch ($period) {
        case 'daily':
            $sql = "SELECT visit_date, COUNT(*) as count FROM visits GROUP BY visit_date ORDER BY visit_date DESC";
            $title = 'گزارش روزانه';
            break;
        case 'weekly':
            $sql = "SELECT YEARWEEK(visit_date) as week_id, MIN(visit_date) as start_date, COUNT(*) as count FROM visits GROUP BY week_id ORDER BY week_id DESC";
            $title = 'گزارش هفتگی';
            break;
        case 'monthly':
            $sql = "SELECT DATE_FORMAT(visit_date, '%Y-%m') as month, COUNT(*) as count FROM visits GROUP BY month ORDER BY month DESC";
            $title = 'گزارش ماهانه';
            break;
        case 'quarterly':
            $sql = "SELECT CONCAT(YEAR(visit_date), '-Q', QUARTER(visit_date)) as quarter, COUNT(*) as count FROM visits GROUP BY quarter ORDER BY quarter DESC";
            $title = 'گزارش سه‌ماهه';
            break;
        case 'halfyearly':
            $sql = "SELECT CONCAT(YEAR(visit_date), '-H', CEIL(MONTH(visit_date)/6)) as half, COUNT(*) as count FROM visits GROUP BY half ORDER BY half DESC";
            $title = 'گزارش شش‌ماهه';
            break;
        case 'yearly':
            $sql = "SELECT YEAR(visit_date) as year, COUNT(*) as count FROM visits GROUP BY year ORDER BY year DESC";
            $title = 'گزارش سالانه';
            break;
        default:
            $sql = "SELECT visit_date, COUNT(*) as count FROM visits GROUP BY visit_date ORDER BY visit_date DESC";
            $title = 'گزارش روزانه';
            break;
    }

    $stmt = $pdo->query($sql);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalVisits = $pdo->query("SELECT COUNT(*) as total FROM visits")->fetch()['total'];

    // جدول خلاصه
    echo '<h3>📈 خلاصه آمار</h3>';
    echo '<table border="1" cellpadding="5">';
    echo '<tr>';
    echo '<th>بازه زمانی</th>';
    echo '<th>تعداد بازدید</th>';
    echo '<th>درصد از کل</th>';
    echo '</tr>';
    
    foreach ($stats as $row) {
        $keys = array_keys($row);
        $labelKey = $keys[0];
        $count = $row['count'];
        $percent = $totalVisits > 0 ? round(($count / $totalVisits) * 100, 1) : 0;
        
        // تبدیل تاریخ
        $label = $row[$labelKey];
        if ($period == 'daily' && strpos($label, '-') !== false) {
            $label = toJalali($label);
        } elseif ($period == 'weekly' && isset($row['start_date'])) {
            $label = 'هفته منتهی به ' . toJalali($row['start_date']);
        } elseif ($period == 'monthly' && strpos($label, '-') !== false) {
            $parts = explode('-', $label);
            if (count($parts) == 2) {
                $jalali = gregorian_to_jalali($parts[0], $parts[1], 1);
                $label = $jalali[0] . '/' . str_pad($jalali[1], 2, '0', STR_PAD_LEFT);
            }
        }
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($label) . '</td>';
        echo '<td>' . number_format($count) . '</td>';
        echo '<td>' . number_format($percent, 1) . '%</td>';
        echo '</tr>';
    }
    
    echo '<tr class="total">';
    echo '<td><strong>جمع کل</strong></td>';
    echo '<td><strong>' . number_format($totalVisits) . '</strong></td>';
    echo '<td><strong>100%</strong></td>';
    echo '</tr>';
    echo '</table>';

    // جزئیات بازدیدها
    echo '<h3>📋 جزئیات بازدیدها</h3>';
    $detailSql = "SELECT id, visitor_ip, page_url, visit_datetime, session_id FROM visits ORDER BY id DESC LIMIT 1000";
    $detailStmt = $pdo->query($detailSql);
    $details = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table border="1" cellpadding="5">';
    echo '<tr>';
    echo '<th>ردیف</th>';
    echo '<th>IP</th>';
    echo '<th>صفحه</th>';
    echo '<th>تاریخ و زمان (شمسی)</th>';
    echo '<th>شناسه جلسه</th>';
    echo '</tr>';
    
    $rowNum = 1;
    foreach ($details as $detail) {
        echo '<tr>';
        echo '<td>' . $rowNum . '</td>';
        echo '<td>' . htmlspecialchars($detail['visitor_ip']) . '</td>';
        echo '<td>' . htmlspecialchars($detail['page_url']) . '</td>';
        echo '<td>' . toJalaliDateTime($detail['visit_datetime']) . '</td>';
        echo '<td>' . htmlspecialchars(substr($detail['session_id'], 0, 20) . '...') . '</td>';
        echo '</tr>';
        $rowNum++;
    }
    echo '</table>';

    echo '<p style="margin-top:20px;color:#666;">تولید شده در: ' . getCurrentJalaliDate() . ' - ' . date('H:i:s') . '</p>';

} catch (PDOException $e) {
    echo '<p style="color:red;">خطا در دریافت داده‌ها: ' . $e->getMessage() . '</p>';
}

echo '</body></html>';

function getPeriodTitle($period) {
    switch ($period) {
        case 'daily': return 'روزانه';
        case 'weekly': return 'هفتگی';
        case 'monthly': return 'ماهانه';
        case 'quarterly': return 'سه‌ماهه';
        case 'halfyearly': return 'شش‌ماهه';
        case 'yearly': return 'سالانه';
        default: return 'روزانه';
    }
}
?>