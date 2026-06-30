<?php
// فایل: refresh_register_captcha.php

session_start();
$captcha_code = rand(10000, 99999);
$_SESSION['register_captcha'] = $captcha_code;
header('Content-Type: application/json');
echo json_encode(['captcha' => $captcha_code]);
?>