<?php
include("../includes/config.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM customer_details WHERE account_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt2 = $conn->prepare("DELETE FROM accounts WHERE account_id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    header("Location: index.php");
    exit();
}
?>