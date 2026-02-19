<?php
session_start();
include('../includes/config.php');

if (isset($_GET['id'])) {
    $item_id = intval($_GET['id']);

    $uploadDir = '../images/';

    $stmt_imgs = $conn->prepare("SELECT image FROM item_images WHERE item_id = ?");
    $stmt_imgs->bind_param("i", $item_id);
    $stmt_imgs->execute();
    $result_imgs = $stmt_imgs->get_result();

    if ($result_imgs->num_rows > 0) {
        while ($img = $result_imgs->fetch_assoc()) {
            $filePath = $uploadDir . $img['image'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
    $stmt_imgs->close();

    $stmt_del_imgs = $conn->prepare("DELETE FROM item_images WHERE item_id = ?");
    $stmt_del_imgs->bind_param("i", $item_id);
    $stmt_del_imgs->execute();
    $stmt_del_imgs->close();

    // Delete stock record first
    $stmt_stock = $conn->prepare("DELETE FROM stock WHERE item_id = ?");
    $stmt_stock->bind_param("i", $item_id);
    $stmt_stock->execute();
    $stmt_stock->close();

    // Delete the item itself
    $stmt_item = $conn->prepare("DELETE FROM item WHERE item_id = ?");
    $stmt_item->bind_param("i", $item_id);
    if ($stmt_item->execute()) {
        $_SESSION['success'] = "Item deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete item. Please try again.";
    }
    $stmt_item->close();

} else {
    $_SESSION['error'] = "Invalid request. No item selected.";
}

header("Location: index.php");
exit();
?>