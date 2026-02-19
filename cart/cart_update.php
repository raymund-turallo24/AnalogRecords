<?php
session_start();


ob_start(); 

include('../includes/header.php');
include('../includes/config.php');

// adding item logic
// Check if the request is to ADD ng item and kapag valid ang item ID
if (isset($_POST["type"]) && $_POST["type"] == 'add' && isset($_POST["item_id"])) {

    $item_id = intval($_POST['item_id']);
    $quantity_to_add = intval($_POST['item_qty'] ?? 1); 

    // check if valid ang quantity
    if ($quantity_to_add < 1) {
        $quantity_to_add = 1;
    }

    if ($item_id > 0) {
        
        $sql = "
            SELECT 
                i.item_id AS itemId,
                i.title,
                i.price,
                img.image AS img_path
            FROM item i
            INNER JOIN stock s ON i.item_id = s.item_id
            LEFT JOIN item_images img ON i.item_id = img.item_id
            WHERE i.item_id = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $item_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($row) {
            
            $product_data = [
                "item_id"    => $item_id,
                "item_qty"   => $quantity_to_add,
                "item_name"  => $row['title'],
                "item_price" => $row['price'],
                "item_image" => $row['img_path'],
            ];
            
            if (isset($_SESSION["cart_products"][$item_id])) {
                $_SESSION["cart_products"][$item_id]["item_qty"] += $quantity_to_add;
            } else {
                $_SESSION["cart_products"][$item_id] = $product_data;
            }

            header("Location: ../item/show.php?id=" . $item_id . "&status=added");
            ob_end_flush();
            exit();
        }
    }
    header('Location: ../index.php');
    ob_end_flush();
    exit();
}

// update/remove item logic
if (isset($_POST["product_qty"]) || isset($_POST["remove_code"])) {

    // Update quantity
    if (isset($_POST["product_qty"]) && is_array($_POST["product_qty"])) {
        foreach ($_POST["product_qty"] as $key => $value) {
            $safe_qty = intval($value);
            if ($safe_qty > 0) {
                // makes sure quantity is updated correctly as an integer
                $_SESSION["cart_products"][$key]["item_qty"] = $safe_qty;
            } else {
                // Remove item kung ang quantity ay nakaset sa 0 or negative
                unset($_SESSION["cart_products"][$key]);
            }
        }
    }

    // Remove item
    if (isset($_POST["remove_code"]) && is_array($_POST["remove_code"])) {
        foreach ($_POST["remove_code"] as $key) {
            unset($_SESSION["cart_products"][$key]);
        }
    }
    
    header('Location: view_cart.php');
    ob_end_flush();
    exit();
}

header('Location: ../index.php');
ob_end_flush();
exit();
?>