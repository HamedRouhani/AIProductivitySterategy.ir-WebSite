<?php
/**
 * includes/jalali.php
 * توابع تبدیل تاریخ شمسی
 */

// جلوگیری از بارگذاری مجدد
if (!function_exists('to_jalali')) {
    require_once __DIR__ . '/jdf.php';
}

// تابع‌های کمکی برای استفاده آسان
if (!function_exists('toJalali')) {
    function toJalali($date) {
        return to_jalali($date);
    }
}

if (!function_exists('toJalaliDateTime')) {
    function toJalaliDateTime($date) {
        return to_jalali_datetime($date);
    }
}

if (!function_exists('getCurrentJalaliDate')) {
    function getCurrentJalaliDate() {
        return get_current_jalali_date();
    }
}

// تابع برای اصلاح زمان بدون تغییر در دیتابیس
if (!function_exists('fixTehranTime')) {
    function fixTehranTime($datetime) {
        if (empty($datetime)) return '';
        $timestamp = strtotime($datetime);
        $adjusted = $timestamp + (3 * 3600) + (30 * 60);
        return date('Y-m-d H:i:s', $adjusted);
    }
}
?>