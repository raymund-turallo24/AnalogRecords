<?php
session_start();
include("../includes/config.php");

// admin lang pwedeng mag-add
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admins only.";
    header("Location: create.php");
    exit();
}

if (!isset($_POST['submit'])) {
    header("Location: create.php");
    exit();
}

$title       = trim($_POST['title'] ?? '');
$artist      = trim($_POST['artist'] ?? '');
$genre       = trim($_POST['genre'] ?? '');
$price       = floatval($_POST['price'] ?? 0);
$quantity    = intval($_POST['quantity'] ?? 0);
$description = trim($_POST['description'] ?? '');

if (empty($title) || $price <= 0 || $quantity < 1) {
    $_SESSION['error'] = "Please fill in all required fields correctly.";
    header("Location: create.php");
    exit();
}

// insert lahat ng item sa table
$sql = "INSERT INTO item (title, artist, genre, price, description) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssds", $title, $artist, $genre, $price, $description);

if (!mysqli_stmt_execute($stmt)) {
    $_SESSION['error'] = "Error saving item: " . mysqli_error($conn);
    header("Location: create.php");
    exit();
}

$item_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// insert initial stock
$sql_stock = "INSERT INTO stock (item_id, quantity) VALUES (?, ?)";
$stmt_stock = mysqli_prepare($conn, $sql_stock);
mysqli_stmt_bind_param($stmt_stock, "ii", $item_id, $quantity);
mysqli_stmt_execute($stmt_stock);
mysqli_stmt_close($stmt_stock);

// can handle multiple image uploads
if (!empty($_FILES['images']['name'][0])) {
    $uploadDir = '../images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) continue;

        $originalName = $_FILES['images']['name'][$key];
        $fileType = mime_content_type($tmp_name);
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

        if (in_array($fileType, $allowedTypes)) {
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = $item_id . '_' . time() . "_{$key}.{$ext}";
            $target = $uploadDir . $fileName;

            if (move_uploaded_file($tmp_name, $target)) {
                $sql_img = "INSERT INTO item_images (item_id, image) VALUES (?, ?)";
                $stmt_img = mysqli_prepare($conn, $sql_img);
                mysqli_stmt_bind_param($stmt_img, "is", $item_id, $fileName);
                mysqli_stmt_execute($stmt_img);
                mysqli_stmt_close($stmt_img);
            }
        }
    }
}

$_SESSION['success'] = "New item added successfully with images!";
header("Location: index.php");
exit();
?>