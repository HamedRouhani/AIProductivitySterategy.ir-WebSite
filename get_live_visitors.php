<?php
/**
 * get_live_visitors.php
 * دریافت تعداد بازدیدکنندگان آنلاین (۵ دقیقه اخیر)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// شروع سشن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';
require_once 'includes/auth.php';

// فقط ادمین می‌تواند این اطلاعات را ببیند
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['count' => 0]);
    exit;
}

try {
    // بررسی وجود جدول
    $checkTable = $pdo->query("SHOW TABLES LIKE 'visits'");
    if ($checkTable->rowCount() == 0) {
        echo json_encode(['count' => 0]);
        exit;
    }
    
    // بازدیدهای ۵ دقیقه اخیر
    $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT session_id) as count FROM visits WHERE visit_datetime >= ?");
    $stmt->execute([$fiveMinutesAgo]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['count' => $result['count'] ?? 0]);
} catch (PDOException $e) {
    error_log("خطا در دریافت بازدیدکنندگان آنلاین: " . $e->getMessage());
    echo json_encode(['count' => 0]);
}
?>