<?php
session_start();
include('../includes/config.php');

// admin check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    function calculateInitialTotals($conn, $item_id_arr, $quantity_arr) {
        $item_id = $item_id_arr[0];
        $quantity = $quantity_arr[0];

        $stmt_price = $conn->prepare("SELECT price FROM item WHERE item_id = ?");
        $stmt_price->bind_param("i", $item_id);
        $stmt_price->execute();
        $result = $stmt_price->get_result();
        $item_price = $result->fetch_assoc()['price'] ?? 0.00;
        $stmt_price->close();

        $initial_subtotal = 0.00;
        if ($item_price > 0) {
            $initial_subtotal = $item_price * $quantity;
        }
        
        $shipping_fee = 0.00; 
        $initial_total = $initial_subtotal + $shipping_fee;

        return [
            'subtotal' => $initial_subtotal, 
            'shipping_fee' => $shipping_fee, 
            'total' => $initial_total,
            'item_price' => $item_price
        ];
    }

    $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $order_status = trim($_POST['order_status']);
    $shipping_address = trim($_POST['shipping_address']);
    $remarks = trim($_POST['remarks']);

    $item_ids = $_POST['item_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    
    if (!$customer_id || empty($order_status) || empty($shipping_address) || empty($item_ids) || empty($quantities)) {
        $_SESSION['error'] = "Missing required information (Customer, Status, Address, or Item details).";
        header("Location: create.php");
        exit();
    }
    
    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
    if (!in_array($order_status, $allowed_statuses)) {
        $order_status = 'Pending';
    }

    $totals = calculateInitialTotals($conn, $item_ids, $quantities);
    $initial_subtotal = $totals['subtotal'];
    $initial_shipping_fee = $totals['shipping_fee'];
    $initial_total = $totals['total'];
    $item_price_at_purchase = $totals['item_price'];
    $initial_item_id = $item_ids[0];
    $initial_quantity = $quantities[0];


    $conn->begin_transaction();
    $new_order_id = 0;

    try {
        $stmt_order = $conn->prepare("
            INSERT INTO orderinfo (customer_id, order_date, order_status, shipping_address, remarks, subtotal, shipping_fee, total) 
            VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)
        ");
        $stmt_order->bind_param("isssddd", 
            $customer_id, $order_status, $shipping_address, $remarks, 
            $initial_subtotal, $initial_shipping_fee, $initial_total
        );
        
        if (!$stmt_order->execute()) {
            throw new Exception("Error executing order insertion: " . $stmt_order->error);
        }

        $new_order_id = $conn->insert_id;
        $stmt_order->close();
        
        if ($new_order_id === 0) {
              throw new Exception("Failed to retrieve new order ID.");
        }
        
        if ($item_price_at_purchase > 0 && $initial_quantity > 0) {
            $stmt_line = $conn->prepare("
                INSERT INTO orderline (order_id, item_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");

            $stmt_line->bind_param("iiid", 
                $new_order_id, $initial_item_id, $initial_quantity, $item_price_at_purchase
            );

            if (!$stmt_line->execute()) {
                throw new Exception("Error executing order line insertion: " . $stmt_line->error);
            }
            $stmt_line->close();
        }

        // commit transaction
        $conn->commit();

        $_SESSION['success'] = "Order #{$new_order_id} created and initial item added. You can now add more items.";
        
        header("Location: index.php?id={$new_order_id}");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        
        error_log("Order Insertion Failed: " . $e->getMessage());

        $_SESSION['error'] = "Failed to create order: A database error occurred. " . $e->getMessage();
        header("Location: create.php");
        exit();
    }

} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: create.php");
    exit();
}

mysqli_close($conn);
?>