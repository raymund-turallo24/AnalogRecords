<?php
session_start();
include('../includes/config.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

if(isset($_GET['id'])){
    $order_id = $_GET['id'];
    $conn->query("DELETE FROM orderline WHERE order_id = $order_id");
    $conn->query("DELETE FROM orderinfo WHERE order_id = $order_id");
}

header("Location: index.php");
exit();
?>