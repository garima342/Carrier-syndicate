<?php
// TEMPORARY DEBUG — remove these two lines once OTP sending works
ini_set("display_errors", 1);
error_reporting(E_ALL);

session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/PHPMailer/src/Exception.php";
require_once __DIR__ . "/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/src/SMTP.php";
require_once __DIR__ . "/mail_config.php"; // holds your Gmail SMTP credentials

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}

$email = trim($_POST["email"] ?? "");

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Valid email required."]);
    exit;
}

// Generate 6-digit OTP
$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Store in session, keyed by email, with a 10-minute expiry
$_SESSION["otp_" . md5($email)] = [
    "otp"      => $otp,
    "email"    => $email,
    "expires"  => time() + 600
];

$subject = "Your InternHub Company Registration OTP";
$message = "Hello,\n\nYour OTP for email verification is: $otp\n\nThis code expires in 10 minutes.\n\nRegards,\nInternHub Team";

$mail = new PHPMailer(true);

try {
    // Gmail SMTP (requires a Google App Password, not your normal password)
    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;   // defined in mail_config.php, e.g. yourname@gmail.com
    $mail->Password   = MAIL_PASSWORD;   // defined in mail_config.php, the 16-char App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom(MAIL_USERNAME, "InternHub");
    $mail->addAddress($email);
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->isHTML(false);

    $mail->send();
    echo json_encode(["success" => true, "message" => "OTP sent to $email."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to send OTP email: " . $mail->ErrorInfo
    ]);
}