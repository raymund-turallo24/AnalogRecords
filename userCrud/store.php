<?php
include("../includes/config.php");

if (isset($_POST['save'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO accounts (email, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $role);
    $stmt->execute();
    $account_id = $stmt->insert_id;

    $stmt2 = $conn->prepare("INSERT INTO customer_details (first_name, last_name, contact, address, account_id) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("ssisi", $first_name, $last_name, $contact, $address, $account_id);
    $stmt2->execute();

    header("Location: index.php");
    exit();
}
?>