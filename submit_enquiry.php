<?php
session_start();
require_once "config.php"; // Database class

// =============================
// CONNECT DATABASE
// =============================


if (!$pdo) {
    die("Database connection failed");
}

// =============================
// ALLOW ONLY POST REQUEST
// =============================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// =============================
// FILE UPLOAD SETTINGS
// =============================
$uploadDir = "uploads/enquiries/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$allowedTypes = ['jpg','jpeg','png','pdf','doc','docx','psd','ai','zip'];
$maxFileSize = 10 * 1024 * 1024;

$filePath = null;

// =============================
// HANDLE FILE UPLOAD
// =============================
if (!empty($_FILES['reference_file']['name'])) {

    $fileTmp  = $_FILES['reference_file']['tmp_name'];
    $fileSize = $_FILES['reference_file']['size'];
    $ext = strtolower(pathinfo($_FILES['reference_file']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedTypes)) {
        $_SESSION['message'] = "❌ Invalid file type.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php#enquiry");
        exit;
    }

    if ($fileSize > $maxFileSize) {
        $_SESSION['message'] = "❌ File size must be under 10MB.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php#enquiry");
        exit;
    }

    $filePath = $uploadDir . time() . "_" . uniqid() . "." . $ext;
    move_uploaded_file($fileTmp, $filePath);
}

// =============================
// FORM DATA
// =============================
$serviceTypes = $_POST['service_type'] ?? [];
$contactPrefs = $_POST['contact_preference'] ?? [];

$data = [
    'full_name'           => trim($_POST['full_name']),
    'email'               => trim($_POST['email']),
    'phone'               => trim($_POST['phone']),
    'company'             => $_POST['company'] ?? null,
    'location'            => $_POST['location'] ?? null,
    'service_type'        => implode(', ', $serviceTypes),
    'other_service'       => $_POST['other_service'] ?? null,
    'project_type'        => $_POST['project_type'],
    'project_description' => $_POST['project_description'],
    'design_style'        => $_POST['design_style'] ?? null,
    'file_path'           => $filePath,
    'deadline'            => $_POST['deadline'] ?? null,
    'budget_range'        => $_POST['budget_range'] ?? null,
    'contact_preference'  => implode(', ', $contactPrefs),
    'best_time'           => $_POST['best_time'] ?? null,
    'hear_about'          => $_POST['hear_about'] ?? null,
    'other_source'        => $_POST['other_source'] ?? null,
    'additional_notes'    => $_POST['additional_notes'] ?? null
];

// =============================
// BASIC VALIDATION
// =============================
if (
    empty($data['full_name']) ||
    empty($data['email']) ||
    empty($data['phone']) ||
    empty($data['service_type']) ||
    empty($data['project_type']) ||
    empty($data['project_description']) ||
    empty($data['contact_preference'])
) {
    $_SESSION['message'] = "❌ Please fill all required fields.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php#enquiry");
    exit;
}

// =============================
// INSERT INTO DATABASE
// =============================
$sql = "
INSERT INTO enquiries (
    full_name, email, phone, company, location,
    service_type, other_service, project_type,
    project_description, design_style, file_path,
    deadline, budget_range, contact_preference,
    best_time, hear_about, other_source,
    additional_notes
)
VALUES (
    :full_name, :email, :phone, :company, :location,
    :service_type, :other_service, :project_type,
    :project_description, :design_style, :file_path,
    :deadline, :budget_range, :contact_preference,
    :best_time, :hear_about, :other_source,
    :additional_notes
)";

$stmt = $pdo->prepare($sql);
$stmt->execute($data);

// =============================
// SUCCESS MESSAGE
// =============================
$_SESSION['message'] = "✅ Thank you! Your enquiry has been submitted successfully.";
$_SESSION['message_type'] = "success";

header("Location: index.php#enquiry");
exit;
