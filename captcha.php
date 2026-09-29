<?php
session_start();

$code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 6);
$_SESSION['captcha'] = $code;

header("Content-type: image/png");

$image = imagecreate(150, 50);

$bg   = imagecolorallocate($image, 255, 255, 255);
$text = imagecolorallocate($image, 0, 0, 0);
$line = imagecolorallocate($image, 180, 180, 180);

// Random lines
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, 150), rand(0, 50), rand(0, 150), rand(0, 50), $line);
}

// CAPTCHA text
imagestring($image, 5, 30, 15, $code, $text);

imagepng($image);
imagedestroy($image);