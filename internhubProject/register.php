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
$submitted_captcha    = trim($_POST["captcha"] ?? "");
$terms_accepted       = isset($_POST["terms"]);

$errors = [];

// ---- Basic field validation ----------------------------------------
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

// ---- CAPTCHA ---------------------------------------------------------
if (
    $submitted_captcha === "" ||
    !isset($_SESSION['captcha']) ||
    strcasecmp($submitted_captcha, $_SESSION['captcha']) !== 0
) {
    $errors[] = "Invalid captcha. Please try again.";
}

// ---- OTP: verify what the user typed against what was emailed -------
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

// Stop here if anything above failed — nothing has touched the
// database or filesystem yet.
if (!empty($errors)) {
    // Refresh the captcha so the old code can't be reused/guessed.
    unset($_SESSION['captcha']);
    http_response_code(400);
    echo json_encode(["success" => false, "message" => implode(" ", $errors)]);
    exit;
}

// ---- Check for an existing account with this email ------------------
$stmt = $conn->prepare("SELECT id FROM companies WHERE company_email = ?");
$stmt->bind_param("s", $company_email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "An account with this email already exists."]);
    exit;
}
$stmt->close();

// ---- Handle logo upload (optional) -----------------------------------
$logo_path = null;
if (isset($_FILES["company_logo"]) && $_FILES["company_logo"]["error"] === UPLOAD_ERR_OK) {
    $allowed_types = ["image/png", "image/jpeg"];
    $file_type = mime_content_type($_FILES["company_logo"]["tmp_name"]);

    if (!in_array($file_type, $allowed_types, true)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Logo must be PNG or JPG."]);
        exit;
    }
    if ($_FILES["company_logo"]["size"] > 2 * 1024 * 1024) {
        http_response_code(400);
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
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to save the logo file. Check folder permissions."]);
        exit;
    }
}

// ---- Create the account ------------------------------------------------
$password_hash = password_hash($password, PASSWORD_BCRYPT);
// The OTP was already verified above; store it as a record of what
// was verified and mark the account as email-verified.
$email_otp = $submitted_otp;
$email_verified = "Yes";

$conn->begin_transaction();

try {
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
    $stmt->execute();
    $company_id = $stmt->insert_id;
    $stmt->close();

    // Provision this company's dashboard workspace (profile + settings rows)
    // so the dashboard has somewhere to read/write from immediately after login.
    $profileStmt = $conn->prepare("INSERT INTO profile (company_id, company_name, logo_path) VALUES (?, ?, ?)");
    $profileStmt->bind_param("iss", $company_id, $company_name, $logo_path);
    $profileStmt->execute();
    $profileStmt->close();

    $settingsStmt = $conn->prepare("INSERT INTO settings (company_id, site_name, contact_email) VALUES (?, ?, ?)");
    $settingsStmt->bind_param("iss", $company_id, $company_name, $company_email);
    $settingsStmt->execute();
    $settingsStmt->close();

    $notifStmt = $conn->prepare("INSERT INTO notifications (company_id, message) VALUES (?, ?)");
    $welcomeMsg = "Welcome to InternHub! Your company account was created.";
    $notifStmt->bind_param("is", $company_id, $welcomeMsg);
    $notifStmt->execute();
    $notifStmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not create the account. Please try again."]);
    exit;
}

// Clear OTP + CAPTCHA now that registration succeeded
unset($_SESSION[$otp_key]);
unset($_SESSION['captcha']);

// Log the company straight in (same session keys login.php uses) so the
// front end can send them to the dashboard immediately after registering.
session_regenerate_id(true);
$_SESSION["company_id"]   = $company_id;
$_SESSION["company_name"] = $company_name;

echo json_encode([
    "success" => true,
    "message" => "Company account created successfully.",
    "company_id" => $company_id,
    "redirect" => "dashboard/index.php"
]);

$conn->close();
