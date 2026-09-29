<?php
session_start();
require_once "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}

$full_name        = trim($_POST["full_name"] ?? "");
$email            = trim($_POST["email"] ?? "");
$phone            = trim($_POST["phone"] ?? "");
$college          = trim($_POST["college"] ?? "");
$course           = trim($_POST["course"] ?? "");
$graduation_year  = trim($_POST["graduation_year"] ?? "");
$password         = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";
$submitted_otp    = trim($_POST["email_otp"] ?? "");
$submitted_captcha= trim($_POST["captcha"] ?? "");

$errors = [];

if ($full_name === "") $errors[] = "Full name is required.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";
if (!preg_match("/^[0-9]{10}$/", $phone)) $errors[] = "Valid 10-digit mobile number required.";
if ($college === "") $errors[] = "College / University is required.";
if ($course === "") $errors[] = "Course is required.";
if (!preg_match("/^[0-9]{4}$/", $graduation_year)) $errors[] = "Valid graduation year required.";
if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

if (
    $submitted_captcha === "" ||
    !isset($_SESSION['captcha']) ||
    strcasecmp($submitted_captcha, $_SESSION['captcha']) !== 0
) {
    $errors[] = "Invalid captcha. Please try again.";
}

$otp_key = "otp_" . md5($email);
if (!preg_match("/^[0-9]{6}$/", $submitted_otp)) {
    $errors[] = "A valid 6-digit Email OTP is required.";
} elseif (!isset($_SESSION[$otp_key])) {
    $errors[] = "No OTP was requested for this email. Click 'Send OTP' first.";
} elseif (time() > $_SESSION[$otp_key]["expires"]) {
    unset($_SESSION[$otp_key]);
    $errors[] = "Your OTP has expired. Please request a new one.";
} elseif ($_SESSION[$otp_key]["otp"] !== $submitted_otp) {
    $errors[] = "The Email OTP you entered is incorrect.";
}

if (!empty($errors)) {
    unset($_SESSION['captcha']);
    http_response_code(400);
    echo json_encode(["success" => false, "message" => implode(" ", $errors)]);
    exit;
}

// Existing account check
$stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "An account with this email already exists."]);
    exit;
}
$stmt->close();

$password_hash = password_hash($password, PASSWORD_BCRYPT);
$email_verified = "Yes";

$stmt = $conn->prepare(
    "INSERT INTO students (full_name, email, phone, college, course, graduation_year, password_hash, email_otp, email_verified)
     VALUES (?,?,?,?,?,?,?,?,?)"
);
$stmt->bind_param(
    "sssssisss",
    $full_name, $email, $phone, $college, $course, $graduation_year, $password_hash, $submitted_otp, $email_verified
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not create the account. Please try again."]);
    exit;
}

$student_id = $stmt->insert_id;
$stmt->close();

// Clear OTP + CAPTCHA now that registration succeeded
unset($_SESSION[$otp_key]);
unset($_SESSION['captcha']);

// Log the student straight in, same session key student pages check.
session_regenerate_id(true);
$_SESSION["student_id"]   = $student_id;
$_SESSION["student_name"] = $full_name;

echo json_encode([
    "success" => true,
    "message" => "Student account created successfully.",
    "student_id" => $student_id,
    "redirect" => "student/index.php"
]);

$conn->close();
