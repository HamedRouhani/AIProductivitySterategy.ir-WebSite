<?php
// ========================================
// فایل: includes/db.php
// اتصال به پایگاه داده با رعایت امنیت
// ========================================

// بارگذاری متغیرهای محیطی (در صورت وجود)
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// تنظیمات از متغیرهای محیطی یا مقادیر پیش‌فرض
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: '******';
$username = getenv('DB_USER') ?: '*******';
$password = getenv('DB_PASS') ?: '*******';
$port = getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

// تنظیمات اتصال
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, // جلوگیری از SQL Injection
    PDO::ATTR_STRINGIFY_FETCHES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // لاگ‌گیری خطا بدون نمایش به کاربر
    error_log('Database Connection Error: ' . $e->getMessage());
    
    // نمایش پیام کاربرپسند
    die('متاسفانه در ارتباط با پایگاه داده مشکلی پیش آمده است. لطفاً بعداً تلاش کنید.');
}

// تنظیمات اضافی برای امنیت
$pdo->exec("SET time_zone = '+03:30'"); // تنظیم منطقه زمانی ایران
?>