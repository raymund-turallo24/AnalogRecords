<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

if (isset($_POST['submit'])) {
    $account_id = $_SESSION['account_id'] ?? null;

    if (!$account_id) {
        $_SESSION['error'] = "You must be logged in.";
        header("Location: ../login.php");
        exit();
    }

    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $contact    = trim($_POST['contact']);
    $address    = trim($_POST['address']);
    $date_created = date('Y-m-d H:i:s');

    // Handle image upload
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = basename($_FILES["image"]["name"]);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowedTypes)) {
            $newFileName = uniqid("img_") . "." . $fileExt;
            $targetFilePath = $targetDir . $newFileName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $image_name = $newFileName;
            }
        }
    }

    // Check if the user already has a profile
    $check_sql = "SELECT * FROM customer_details WHERE account_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $account_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // ✅ Update existing profile
        $user = $check_result->fetch_assoc();
        $image_to_use = $image_name ?? $user['image']; // keep old image if no new upload

        $update_sql = "UPDATE customer_details 
                       SET first_name = ?, last_name = ?, contact = ?, address = ?, image = ? 
                       WHERE account_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssi", $first_name, $last_name, $contact, $address, $image_to_use, $account_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update profile: " . $conn->error;
        }

    } else {
        // ✅ Insert new profile
        $stmt = $conn->prepare("INSERT INTO customer_details 
                                (account_id, first_name, last_name, contact, address, image, date_created) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $account_id, $first_name, $last_name, $contact, $address, $image_name, $date_created);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile saved successfully!";
            $_SESSION['customer_id'] = $stmt->insert_id;
        } else {
            $_SESSION['error'] = "Error saving profile: " . $conn->error;
        }
    }

    header("Location: profileUser.php");
    exit();
}

include("../includes/footer.php");