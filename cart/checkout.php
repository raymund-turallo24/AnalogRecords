<?php
session_start();
include('../includes/config.php');
include('../includes/mail.php');

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    $_SESSION['status'] = ['type' => 'warning', 'message' => "You must be logged in to checkout. Please log in or register."];
    header("Location: view_cart.php");
    exit();
}

// Check if cart is empty
if (empty($_SESSION['cart_products'])) {
    $_SESSION['status'] = ['type' => 'warning', 'message' => "Your cart is empty! Cannot checkout."];
    header("Location: view_cart.php");
    exit();
}

$account_id = $_SESSION['account_id'];
$remarks = $_SESSION['remarks'] ?? ''; 
unset($_SESSION['remarks']);

$cart_items_for_email = $_SESSION['cart_products'];

try {
    // Fetch Customer Details
    $sql = "SELECT cd.customer_id, cd.address, a.email, cd.first_name, cd.last_name 
            FROM customer_details cd
            INNER JOIN accounts a ON cd.account_id = a.account_id
            WHERE a.account_id = ? 
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $account_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $customer_id = $row['customer_id'];
        $shipping_address = $row['address'];
        $customer_email = $row['email'];
        $customer_name = trim($row['first_name'] . ' ' . $row['last_name']) ?: "Customer"; 
    } else {
        mysqli_stmt_close($stmt);
        throw new Exception("Customer details not found for account ID $account_id.");
    }
    mysqli_stmt_close($stmt);

    if (empty($shipping_address)) {
        throw new Exception("No shipping address found for your account. Please update your profile.");
    }

    // icalculate ang total
    $subtotal = 0;
    $shipping_fee = 80; // Default fixed shipping

    foreach ($cart_items_for_email as $cart_itm) {
        $quantity = intval($cart_itm['item_qty']);
        $price = floatval($cart_itm['item_price']);
        $subtotal += $quantity * $price;
    }

    $total = $subtotal + $shipping_fee;
    
    // start transac and order process
    mysqli_begin_transaction($conn);

    $q1 = "INSERT INTO orderinfo (customer_id, shipping_address, remarks, order_status, subtotal, shipping_fee, total) 
           VALUES (?, ?, ?, 'Pending', ?, ?, ?)";
    $stmt1 = mysqli_prepare($conn, $q1);
    mysqli_stmt_bind_param($stmt1, "issddd", $customer_id, $shipping_address, $remarks, $subtotal, $shipping_fee, $total);
    mysqli_stmt_execute($stmt1);
    $order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt1);

    $q2 = "INSERT INTO orderline (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt2 = mysqli_prepare($conn, $q2);

    $q3 = "UPDATE stock SET quantity = quantity - ? WHERE item_id = ? AND quantity >= ?";
    $stmt3 = mysqli_prepare($conn, $q3);

    foreach ($cart_items_for_email as $cart_itm) {
        $item_id = intval($cart_itm['item_id']);
        $quantity = intval($cart_itm['item_qty']);
        $price = floatval($cart_itm['item_price']);

        mysqli_stmt_bind_param($stmt2, "iiid", $order_id, $item_id, $quantity, $price);
        mysqli_stmt_execute($stmt2);

        mysqli_stmt_bind_param($stmt3, "iii", $quantity, $item_id, $quantity);
        mysqli_stmt_execute($stmt3);

        if (mysqli_stmt_affected_rows($stmt3) === 0) {
            throw new Exception("Insufficient stock for item $item_id: " . htmlspecialchars($cart_itm['item_name']));
        }
    }

    mysqli_stmt_close($stmt2);
    mysqli_stmt_close($stmt3);

    mysqli_commit($conn);
    
    unset($_SESSION['cart_products']);

    // mail trap
    $orderHtml = "<h2>Thank you for your order!</h2>";
    $orderHtml .= "<p>Hi <strong>{$customer_name}</strong>,</p>";
    $orderHtml .= "<p>Your order <strong>#{$order_id}</strong> has been received and is pending.</p>";

    $orderHtml .= "<h3>Order Items</h3>";
    $orderHtml .= "<table border='1' cellpadding='6' cellspacing='0' width='100%'>";
    $orderHtml .= "<tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>";

    foreach ($cart_items_for_email as $item) {
        $line_total = intval($item['item_qty']) * floatval($item['item_price']);
        $orderHtml .= "<tr>
                        <td>" . htmlspecialchars($item['item_name']) . "</td>
                        <td>" . intval($item['item_qty']) . "</td>
                        <td>₱" . number_format($item['item_price'], 2) . "</td>
                        <td>₱" . number_format($line_total, 2) . "</td>
                       </tr>";
    }
    $orderHtml .= "</table>";

    $orderHtml .= "<p><strong>Shipping Address:</strong> " . htmlspecialchars($shipping_address) . "</p>";
    $orderHtml .= "<p><strong>Subtotal:</strong> ₱" . number_format($subtotal, 2) . "</p>";
    $orderHtml .= "<p><strong>Shipping:</strong> ₱" . number_format($shipping_fee, 2) . "</p>";
    $orderHtml .= "<h3>Total: ₱" . number_format($total, 2) . "</h3>";
    $orderHtml .= "<p>We will notify you when your order ships.</p>";
    $orderHtml .= "<p>— Your Team</p>";

    $emailResult = smtp_send_mail($customer_email, "Your Order Confirmation — Order #{$order_id}", $orderHtml);

    if (!$emailResult['success']) {
        $_SESSION['status'] = [
            'type' => 'success',
            'message' => "Checkout successful! Order **#{$order_id}** created. (Email failed: " . htmlspecialchars($emailResult['error']) . ")"
        ];
    } else {
        $_SESSION['status'] = [
            'type' => 'success',
            'message' => "Checkout successful! Order **#{$order_id}** created. A confirmation email has been sent to <strong>" . htmlspecialchars($customer_email) . "</strong>."
        ];
    }

    header("Location: ../index.php");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    error_log("Checkout error (Account ID: $account_id): " . $e->getMessage());

    $_SESSION['status'] = [
        'type' => 'error',
        'message' => "Error placing order: " . htmlspecialchars($e->getMessage()) . " Please try again."
    ];

    header("Location: view_cart.php");
    exit();
}
?>
