<?php
/**
 * visit_tracker.php
 * ماژول ثبت و مدیریت بازدیدهای سایت
 */

// شروع سشن اگر قبلاً شروع نشده باشد
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// فعال‌سازی دیباگ
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// بارگذاری توابع احراز هویت
if (file_exists(__DIR__ . '/includes/auth.php')) {
    require_once __DIR__ . '/includes/auth.php';
}

// تابع کمکی برای نوشتن لاگ
function writeLog($message) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents(__DIR__ . '/tracker_log.txt', $logMessage, FILE_APPEND);
}

/**
 * تابع بررسی و اصلاح ساختار جدول
 */
function ensureVisitsTableStructure($pdo) {
    try {
        // بررسی وجود ستون session_id
        $stmt = $pdo->query("SHOW COLUMNS FROM visits LIKE 'session_id'");
        if ($stmt->rowCount() == 0) {
            writeLog("ستون session_id وجود ندارد، در حال اضافه کردن...");
            $pdo->exec("ALTER TABLE visits ADD COLUMN session_id VARCHAR(128) NULL AFTER visit_datetime");
            $pdo->exec("ALTER TABLE visits ADD INDEX idx_session (session_id)");
            writeLog("ستون session_id با موفقیت اضافه شد");
            return true;
        }
        return true;
    } catch (PDOException $e) {
        writeLog("خطا در بررسی ساختار جدول: " . $e->getMessage());
        return false;
    }
}

/**
 * تابع اصلی ثبت بازدید
 */
function logVisit($pdo) {
    // بررسی کاربر ادمین
    $isAdmin = false;
    if (function_exists('isAdmin')) {
        $isAdmin = isAdmin();
    } else {
        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    if ($isAdmin) {
        writeLog("کاربر ادمین است، بازدید ثبت نمی‌شود");
        return false;
    }
    
    // جلوگیری از ثبت بازدیدهای تکراری
    if (isset($_SESSION['last_visit_logged']) && (time() - $_SESSION['last_visit_logged'] < 900)) {
        writeLog("بازدید تکراری در 15 دقیقه اخیر");
        return false;
    }

    // دریافت اطلاعات بازدیدکننده
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $page_url = $_SERVER['REQUEST_URI'] ?? '/';
    $page_url = strtok($page_url, '?');
    $today = date('Y-m-d');
    $datetime = date('Y-m-d H:i:s');

    try {
        // بررسی وجود جدول
        $checkTable = $pdo->query("SHOW TABLES LIKE 'visits'");
        if ($checkTable->rowCount() == 0) {
            writeLog("جدول visits وجود ندارد، در حال ایجاد...");
            createVisitsTable($pdo);
        }
        
        // اطمینان از وجود ستون session_id
        ensureVisitsTableStructure($pdo);

        // ثبت بازدید
        $stmt = $pdo->prepare("INSERT INTO visits (visitor_ip, user_agent, page_url, visit_date, visit_datetime, session_id) VALUES (?, ?, ?, ?, ?, ?)");
        $sessionId = session_id();
        $result = $stmt->execute([$ip, $user_agent, $page_url, $today, $datetime, $sessionId]);
        
        if ($result) {
            $_SESSION['last_visit_logged'] = time();
            writeLog("بازدید با موفقیت ثبت شد - IP: $ip, Page: $page_url");
            return true;
        } else {
            writeLog("خطا در ثبت بازدید");
            return false;
        }
    } catch (PDOException $e) {
        writeLog("خطا در ثبت بازدید: " . $e->getMessage());
        return false;
    }
}

/**
 * تابع ایجاد جدول بازدیدها
 */
function createVisitsTable($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visitor_ip VARCHAR(45) NOT NULL,
        user_agent TEXT,
        page_url VARCHAR(255) NOT NULL,
        visit_date DATE NOT NULL,
        visit_datetime DATETIME NOT NULL,
        session_id VARCHAR(128) NULL,
        INDEX idx_date (visit_date),
        INDEX idx_ip (visitor_ip),
        INDEX idx_session (session_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $pdo->exec($sql);
        writeLog("جدول visits با موفقیت ایجاد شد");
        return true;
    } catch (PDOException $e) {
        writeLog("خطا در ایجاد جدول: " . $e->getMessage());
        return false;
    }
}

// اجرای تابع ثبت بازدید
writeLog("=== شروع رهگیری بازدید ===");

if (isset($pdo) && $pdo instanceof PDO) {
    logVisit($pdo);
} else {
    writeLog("PDO وجود ندارد!");
}

writeLog("=== پایان رهگیری بازدید ===");
?>