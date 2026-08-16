<?php
session_start();
require_once "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}

// Collect + sanitize input
$company_name        = trim($_POST["company_name"] ?? "");
$company_type        = trim($_POST["company_type"] ?? "");
$industry             = trim($_POST["industry"] ?? "");
$cin_number           = trim($_POST["cin_number"] ?? "");
$gst_number           = trim($_POST["gst_number"] ?? "");
$company_website      = trim($_POST["company_website"] ?? "");
$company_email        = trim($_POST["company_email"] ?? "");
$company_description  = trim($_POST["company_description"] ?? "");
$company_size         = trim($_POST["company_size"] ?? "");
$founded_year         = trim($_POST["founded_year"] ?? "");
$recruiter_name       = trim($_POST["recruiter_name"] ?? "");
$designation          = trim($_POST["designation"] ?? "");
$official_email       = trim($_POST["official_email"] ?? "");
$mobile_number        = trim($_POST["mobile_number"] ?? "");
$address_line1        = trim($_POST["address_line1"] ?? "");
$address_line2        = trim($_POST["address_line2"] ?? "");
$city                 = trim($_POST["city"] ?? "");
$state                = trim($_POST["state"] ?? "");
$country              = trim($_POST["country"] ?? "");
$pin_code             = trim($_POST["pin_code"] ?? "");
$password             = $_POST["password"] ?? "";
$confirm_password     = $_POST["confirm_password"] ?? "";
$submitted_otp        = trim($_POST["email_otp"] ?? "");
$terms_accepted       = isset($_POST["terms"]);

$errors = [];

// Basic validation
if ($company_name === "") $errors[] = "Company name is required.";
if (!filter_var($company_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid company email required.";
if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
if ($cin_number === "") $errors[] = "CIN number is required.";
if ($company_description === "") $errors[] = "Company description is required.";
if ($company_size === "") $errors[] = "Company size is required.";
if ($recruiter_name === "") $errors[] = "Recruiter name is required.";
if ($designation === "") $errors[] = "Designation is required.";
if (!filter_var($official_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid official email required.";
if ($mobile_number === "" || !preg_match("/^[0-9]{10}$/", $mobile_number)) $errors[] = "Valid 10-digit mobile number required.";
if ($address_line1 === "") $errors[] = "Address Line 1 is required.";
if ($pin_code === "" || !preg_match("/^[0-9]{6}$/", $pin_code)) $errors[] = "Valid 6-digit PIN code required.";
if (!$terms_accepted) $errors[] = "You must accept the Terms & Conditions.";

// ✅ Verify the OTP the user typed against the one we emailed them (send_otp.php)
$otp_key = "otp_" . md5($company_email);
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
    http_response_code(400);
    echo json_encode(["success" => false, "message" => implode(" ", $errors)]);
    exit;
}

// Check for existing email
$stmt = $conn->prepare("SELECT id FROM companies WHERE company_email = ?");
$stmt->bind_param("s", $company_email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "An account with this email already exists."]);
    exit;
}
$stmt->close();

// Handle logo upload (store file path)
$logo_path = null;
if (isset($_FILES["company_logo"]) && $_FILES["company_logo"]["error"] === UPLOAD_ERR_OK) {
    $allowed_types = ["image/png", "image/jpeg"];
    $file_type = mime_content_type($_FILES["company_logo"]["tmp_name"]);

    if (!in_array($file_type, $allowed_types, true)) {
        echo json_encode(["success" => false, "message" => "Logo must be PNG or JPG."]);
        exit;
    }
    if ($_FILES["company_logo"]["size"] > 2 * 1024 * 1024) {
        echo json_encode(["success" => false, "message" => "Logo must be under 2MB."]);
        exit;
    }

    $logo_dir = __DIR__ . "/logo/";
    if (!is_dir($logo_dir)) mkdir($logo_dir, 0755, true);

    $ext = $file_type === "image/png" ? "png" : "jpg";
    $filename = "company_" . bin2hex(random_bytes(8)) . "." . $ext;
    $dest = $logo_dir . $filename;

    if (move_uploaded_file($_FILES["company_logo"]["tmp_name"], $dest)) {
        $logo_path = "logo/" . $filename; // store relative path in DB
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save the logo file. Check folder permissions."]);
        exit;
    }
}

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// The OTP was already verified above using $submitted_otp; store it as the
// record of what was verified, and mark the account as email-verified.
$email_otp = $submitted_otp;
$email_verified = "Yes";
// Verify CAPTCHA
if (
    !isset($_POST['captcha']) ||
    strtoupper(trim($_POST['captcha'])) != strtoupper($_SESSION['captcha'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Captcha"
    ]);
    exit;
}
// Insert into database
$stmt = $conn->prepare("INSERT INTO companies
 (company_name, company_logo, company_type, industry, cin_number, gst_number, company_website, company_email,
  company_description, company_size, founded_year, recruiter_name, designation, official_email, mobile_number,
  address_line1, address_line2, city, state, country, pin_code, password_hash, email_otp, email_verified)
 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

 

$stmt->bind_param(
  "ssssssssssisssssssssssss",
  $company_name, $logo_path, $company_type, $industry, $cin_number, $gst_number, $company_website, $company_email,
  $company_description, $company_size, $founded_year, $recruiter_name, $designation, $official_email, $mobile_number,
  $address_line1, $address_line2, $city, $state, $country, $pin_code, $password_hash, $email_otp, $email_verified
);
if (!$terms_accepted) $errors[] = "You must accept the Terms & Conditions.";
// DELETE this second block — it's a leftover duplicate:
if (!isset($_POST['captcha']) ||
    strtoupper(trim($_POST['captcha'])) != $_SESSION['captcha']) {
    echo json_encode(["success"=>false, "message"=>"Invalid Captcha"]);
    exit;
}
if ($stmt->execute()) {

    // Clear OTP
    unset($_SESSION[$otp_key]);

    // Clear CAPTCHA
    unset($_SESSION['captcha']);

    echo json_encode([
        "success" => true,
        "message" => "Company account created successfully.",
        "company_id" => $stmt->insert_id
    ]);
}
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => implode(" ", $errors)]);
    exit;
}

$stmt->close();
$conn->close();