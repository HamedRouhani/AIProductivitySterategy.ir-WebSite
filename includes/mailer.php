<?php
// فایل: includes/mailer.php

// تابع ارسال کد تایید ورود
function sendVerificationCode($email, $code) {
    $subject = "=?UTF-8?B?" . base64_encode("🔐 کد تایید ورود") . "?=";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@aiproductivitystrategy.ir\r\n";
    $headers .= "Reply-To: noreply@aiproductivitystrategy.ir\r\n";
    
    $message = "
    <!DOCTYPE html>
    <html dir='rtl'>
    <head><meta charset='UTF-8'></head>
    <body style='font-family:Tahoma; padding:20px;'>
        <div style='max-width:500px; margin:auto; background:#f8f9fa; border-radius:15px; overflow:hidden;'>
            <div style='background:linear-gradient(135deg, #667eea, #764ba2); color:white; padding:20px; text-align:center;'>
                <h2>🔐 کد تایید ورود</h2>
            </div>
            <div style='padding:30px; text-align:center;'>
                <div style='font-size:36px; font-weight:bold; background:white; padding:15px; border-radius:10px; letter-spacing:5px;'>
                    $code
                </div>
                <p style='margin-top:20px;'>این کد تا 10 دقیقه اعتبار دارد.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return mail($email, $subject, $message, $headers);
}

// تابع ارسال ایمیل بازیابی رمز عبور
function sendResetPasswordEmail($email, $reset_link) {
    $subject = "=?UTF-8?B?" . base64_encode("🔐 بازیابی رمز عبور") . "?=";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@aiproductivitystrategy.ir\r\n";
    $headers .= "Reply-To: noreply@aiproductivitystrategy.ir\r\n";
    
    $message = "
    <!DOCTYPE html>
    <html dir='rtl'>
    <head><meta charset='UTF-8'></head>
    <body style='font-family:Tahoma; padding:20px;'>
        <div style='max-width:500px; margin:auto; background:#f8f9fa; border-radius:15px; overflow:hidden;'>
            <div style='background:linear-gradient(135deg, #667eea, #764ba2); color:white; padding:20px; text-align:center;'>
                <h2>🔐 بازیابی رمز عبور</h2>
            </div>
            <div style='padding:30px; text-align:center;'>
                <p>برای بازیابی رمز عبور خود روی لینک زیر کلیک کنید:</p>
                <a href='{$reset_link}' style='display:inline-block; background:#667eea; color:white; padding:12px 25px; text-decoration:none; border-radius:8px; margin:20px 0;'>
                    بازیابی رمز عبور
                </a>
                <p style='font-size:12px; color:#888;'>این لینک تا 1 ساعت اعتبار دارد.</p>
                <hr>
                <p style='font-size:11px;'>اگر درخواست نکرده‌اید، این ایمیل را نادیده بگیرید.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return mail($email, $subject, $message, $headers);
}

// تابع ارسال پاسخ به پیام تماس
function sendContactReplyEmail($email, $subject, $message) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: info@aiproductivitystrategy.ir\r\n";
    $headers .= "Reply-To: info@aiproductivitystrategy.ir\r\n";
    
    return mail($email, $subject, $message, $headers);
}
?>