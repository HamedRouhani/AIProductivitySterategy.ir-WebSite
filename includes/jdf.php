<?php
/**
 * includes/jdf.php
 * تبدیل تاریخ میلادی به شمسی
 */

// تنظیم منطقه زمانی تهران
date_default_timezone_set('Asia/Tehran');

function gregorian_to_jalali($g_y, $g_m, $g_d) {
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    $gy = $g_y - 1600;
    $gm = $g_m - 1;
    $gd = $g_d - 1;

    $g_day_no = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);

    for ($i = 0; $i < $gm; ++$i) {
        $g_day_no += $g_days_in_month[$i];
    }

    if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0))) {
        $g_day_no++;
    }

    $g_day_no += $gd;

    $j_day_no = $g_day_no - 79;

    $j_np = floor($j_day_no / 12053);
    $j_day_no %= 12053;

    $jy = 979 + 33 * $j_np + 4 * floor($j_day_no / 1461);
    $j_day_no %= 1461;

    if ($j_day_no >= 366) {
        $jy += floor(($j_day_no - 1) / 365);
        $j_day_no = ($j_day_no - 1) % 365;
    }

    for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i) {
        $j_day_no -= $j_days_in_month[$i];
    }

    $jm = $i + 1;
    $jd = $j_day_no + 1;

    return array($jy, $jm, $jd);
}

function jalali_to_gregorian($j_y, $j_m, $j_d) {
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    $jy = $j_y - 979;
    $jm = $j_m - 1;
    $jd = $j_d - 1;

    $j_day_no = 365 * $jy + floor($jy / 33) * 8 + floor((($jy % 33) + 3) / 4);

    for ($i = 0; $i < $jm; ++$i) {
        $j_day_no += $j_days_in_month[$i];
    }

    $j_day_no += $jd;

    $g_day_no = $j_day_no + 79;

    $gy = 1600 + 400 * floor($g_day_no / 146097);
    $g_day_no %= 146097;

    $leap = true;
    if ($g_day_no >= 36525) {
        $g_day_no--;
        $gy += 100 * floor($g_day_no / 36524);
        $g_day_no %= 36524;

        if ($g_day_no >= 365) {
            $g_day_no++;
        } else {
            $leap = false;
        }
    }

    $gy += 4 * floor($g_day_no / 1461);
    $g_day_no %= 1461;

    if ($g_day_no >= 366) {
        $leap = false;
        $g_day_no--;
        $gy += floor($g_day_no / 365);
        $g_day_no %= 365;
    }

    for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); ++$i) {
        $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
    }

    $gm = $i + 1;
    $gd = $g_day_no + 1;

    return array($gy, $gm, $gd);
}

function to_jalali($date, $format = 'Y/m/d') {
    if (empty($date) || $date == '0000-00-00') {
        return '';
    }
    
    $timestamp = strtotime($date);
    $g_y = date('Y', $timestamp);
    $g_m = date('m', $timestamp);
    $g_d = date('d', $timestamp);
    
    $jalali = gregorian_to_jalali($g_y, $g_m, $g_d);
    
    $j_y = $jalali[0];
    $j_m = str_pad($jalali[1], 2, '0', STR_PAD_LEFT);
    $j_d = str_pad($jalali[2], 2, '0', STR_PAD_LEFT);
    
    return $j_y . '/' . $j_m . '/' . $j_d;
}

function to_jalali_datetime($date) {
    if (empty($date) || $date == '0000-00-00 00:00:00') {
        return '';
    }
    
    // اصلاح زمان با افزودن 3:30 ساعت (منطقه تهران)
    $timestamp = strtotime($date);
    
    // اضافه کردن 3 ساعت و 30 دقیقه به زمان
    $adjusted_timestamp = $timestamp + (3 * 3600) + (30 * 60);
    
    $g_y = date('Y', $adjusted_timestamp);
    $g_m = date('m', $adjusted_timestamp);
    $g_d = date('d', $adjusted_timestamp);
    $time = date('H:i:s', $adjusted_timestamp);
    
    $jalali = gregorian_to_jalali($g_y, $g_m, $g_d);
    
    $j_y = $jalali[0];
    $j_m = str_pad($jalali[1], 2, '0', STR_PAD_LEFT);
    $j_d = str_pad($jalali[2], 2, '0', STR_PAD_LEFT);
    
    return $j_y . '/' . $j_m . '/' . $j_d . ' ' . $time;
}

function get_current_jalali_date() {
    $now = time();
    $g_y = date('Y', $now);
    $g_m = date('m', $now);
    $g_d = date('d', $now);
    
    $jalali = gregorian_to_jalali($g_y, $g_m, $g_d);
    
    return $jalali[0] . '/' . str_pad($jalali[1], 2, '0', STR_PAD_LEFT) . '/' . str_pad($jalali[2], 2, '0', STR_PAD_LEFT);
}
?>