<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

if (!isset($_POST['submit'])) {
    header("Location: profileUpdate.php");
    exit();
}

$account_id = $_SESSION['account_id'] ?? null;
if (!$account_id) {
    $_SESSION['error'] = "You must be logged in.";
    header("Location: ../login.php");
    exit();
}

// --- Get the values that were submitted, but fall back to original if blank ---
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$contact    = trim($_POST['contact'] ?? '');
$address    = trim($_POST['address'] ?? '');

// If field is empty → use the original value that was sent via hidden fields
$first_name = $first_name !== '' ? $first_name : ($_POST['original_first_name'] ?? '');
$last_name  = $last_name  !== '' ? $last_name  : ($_POST['original_last_name'] ?? '');
$contact    = $contact    !== '' ? $contact    : ($_POST['original_contact'] ?? '');
$address    = $address    !== '' ? $address    : ($_POST['original_address'] ?? '');

$date_created = date('Y-m-d H:i:s');

// --- Handle image upload (only if a new file is uploaded) ---
$image_name = $_POST['original_image_name'] ?? null; // default = keep old image

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileExt = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExt, $allowed) && $_FILES["image"]["size"] <= 5 * 1024 * 1024) {
        $newFileName = uniqid("img_") . "." . $fileExt;
        $targetPath = $targetDir . $newFileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
            // Delete old image if it exists and is not the placeholder
            if ($image_name && file_exists("../uploads/" . $image_name)) {
                @unlink("../uploads/" . $image_name);
            }
            $image_name = $newFileName;
        }
    } else {
        $_SESSION['error'] = "Invalid image file or too large (max 5MB).";
        header("Location: profileUpdate.php");
        exit();
    }
}

// --- Check if profile already exists ---
$check_sql = "SELECT image FROM customer_details WHERE account_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $account_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // === UPDATE existing profile ===
    $update_sql = "UPDATE customer_details 
                   SET first_name = ?, last_name = ?, contact = ?, address = ?, image = ? 
                   WHERE account_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $contact, $address, $image_name, $account_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update profile.";
    }
} else {
    // === CREATE new profile ===
    $insert_sql = "INSERT INTO customer_details 
                   (account_id, first_name, last_name, contact, address, image, date_created) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("issssss", $account_id, $first_name, $last_name, $contact, $address, $image_name, $date_created);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile created successfully!";
    } else {
        $_SESSION['error'] = "Failed to create profile.";
    }
}

$check_stmt->close();
if (isset($stmt)) $stmt->close();
$conn->close();

header("Location: profile.php");
exit();