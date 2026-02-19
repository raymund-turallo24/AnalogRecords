<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

$success_message = '';
$error_message = '';

// check if nakalogin si customer
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// cancel ng order
if (isset($_GET['cancel_order'])) {
    $order_id = intval($_GET['cancel_order']);

    $stmt_check = $conn->prepare("SELECT order_status FROM orderinfo WHERE order_id = ? AND customer_id = ?");
    $stmt_check->bind_param("ii", $order_id, $customer_id);
    $stmt_check->execute();
    $stmt_check->bind_result($status);
    
    if ($stmt_check->fetch()) {
        $stmt_check->close();
        
        if ($status === 'Pending') {
            $conn->begin_transaction();
            try {
                // kinukuha ang items and quantities galing sa order
                $stmt_items = $conn->prepare("SELECT item_id, quantity FROM orderline WHERE order_id = ?");
                $stmt_items->bind_param("i", $order_id);
                $stmt_items->execute();
                $result_items = $stmt_items->get_result();
                
                // ibabalik ang item stock
                while ($row = $result_items->fetch_assoc()) {
                    $item_id = $row['item_id'];
                    $quantity = $row['quantity'];
                    $stmt_stock = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE item_id = ?");
                    $stmt_stock->bind_param("ii", $quantity, $item_id);
                    $stmt_stock->execute();
                    $stmt_stock->close();
                }
                $stmt_items->close();

                // 3. update the order --cancel
                $stmt_update = $conn->prepare("UPDATE orderinfo SET order_status = 'Canceled' WHERE order_id = ?");
                $stmt_update->bind_param("i", $order_id);
                $stmt_update->execute();
                $stmt_update->close();

                $conn->commit();
                $success_message = "Order **#$order_id** has been canceled successfully! Stock has been replenished.";
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Error canceling order: Please contact support.";
            }
        } else {
            $error_message = "Only **Pending** orders can be canceled. This order is currently: **$status**.";
        }
    } else {
        $error_message = "Order not found or does not belong to your account.";
    }
    
    header("Location: view_order.php?status=success&msg=" . urlencode($success_message) . "&err=" . urlencode($error_message));
    exit();
}

if (isset($_GET['status']) && $_GET['status'] === 'success' && !empty($_GET['msg'])) {
    $success_message = htmlspecialchars($_GET['msg']);
}
if (!empty($_GET['err'])) {
    $error_message = htmlspecialchars($_GET['err']);
}

// Fetch lahat ng orders
$stmt_orders = $conn->prepare("SELECT * FROM orderinfo WHERE customer_id = ? ORDER BY order_date DESC");
$stmt_orders->bind_param("i", $customer_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
?>

<div class="site-content-wrapper">
    <div class="row justify-content-center">
        <div class="col-12">
            <h1 class="crud-title">My Orders</h1>
            
            <?php 
            if ($success_message) echo "<div class='alert alert-success alert-crud text-center'>{$success_message}</div>";
            if ($error_message) echo "<div class='alert alert-danger alert-crud text-center'>{$error_message}</div>";
            ?>

            <?php if ($result_orders->num_rows > 0): ?>
                <div class="orders-list">
                    <?php 
                    $status_colors = [
                        'Pending' => 'text-warning',
                        'Processing' => 'text-info',
                        'Shipped' => 'text-primary',
                        'Completed' => 'text-success',
                        'Canceled' => 'text-danger',
                    ];
                    
                    while ($order = $result_orders->fetch_assoc()): 
                        $order_status = $order['order_status'];
                        $status_class = $status_colors[$order_status] ?? 'text-white';
                    ?>
                        <div class="card-crud mb-4">
                            <div class="card-body p-4"> 
                                
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
                                    <div class="text-start">
                                        <h3 class="mb-1 text-light">Order #<?php echo $order['order_id']; ?></h3>
                                        <p class="small mb-0" style="color: #bbb;">
                                            Placed on: <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="text-end">
                                        <p class="fw-bold mb-2 text-white">
                                            Status: <span class="<?php echo $status_class; ?>"><?php echo $order_status; ?></span>
                                        </p>
                                        <?php if ($order_status === 'Pending'): ?>
                                            <a href="?cancel_order=<?php echo $order['order_id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to cancel Order #<?php echo $order['order_id']; ?>? This action cannot be undone and stock will be returned.');">
                                                Cancel Order
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <p class="mb-3"> 
                                    <strong class="text-white">Shipping Address:</strong> 
                                    <span class="text-light"><?php echo htmlspecialchars($order['shipping_address']); ?></span>
                                </p>

                                <div class="table-responsive">
                                    <table class="data-table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="width: 50%;">Item</th>
                                                <th style="width: 10%;">Qty</th>
                                                <th style="width: 15%;">Price (PHP)</th>
                                                <th style="width: 15%;">Subtotal (PHP)</th>
                                                <?php if ($order_status === 'Completed'): ?>
                                                    <th style="width: 10%;" class="text-center">Review</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $stmt_items = $conn->prepare("
                                            SELECT i.item_id, i.title, ol.quantity, ol.price 
                                            FROM orderline ol 
                                            JOIN item i ON ol.item_id = i.item_id 
                                            WHERE ol.order_id = ?
                                        ");
                                        $stmt_items->bind_param("i", $order['order_id']);
                                        $stmt_items->execute();
                                        $result_items = $stmt_items->get_result();
                                        $total = 0;

                                        while ($item = $result_items->fetch_assoc()):
                                            $subtotal = $item['quantity'] * $item['price'];
                                            $total += $subtotal;

                                            // Check if nareview ng ang item
                                            $reviewed = false;
                                            if ($order_status === 'Completed') {
                                                $stmt_check_review = $conn->prepare("
                                                    SELECT review_id 
                                                    FROM item_reviews 
                                                    WHERE item_id = ? AND customer_id = ? AND order_id = ?
                                                ");
                                                $stmt_check_review->bind_param("iii", $item['item_id'], $customer_id, $order['order_id']);
                                                $stmt_check_review->execute();
                                                $stmt_check_review->store_result();
                                                if ($stmt_check_review->num_rows > 0) $reviewed = true;
                                                $stmt_check_review->close();
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                                <td>₱<?php echo number_format($subtotal, 2); ?></td>
                                                <?php if ($order_status === 'Completed'): ?>
                                                    <td class="text-center">
                                                        <?php if ($reviewed): ?>
                                                            <span class="text-success small fw-bold">Reviewed</span>
                                                        <?php else: ?>
                                                            <a href="review_item.php?item_id=<?php echo $item['item_id']; ?>&order_id=<?php echo $order['order_id']; ?>" 
                                                               class="btn btn-sm btn-success-theme">
                                                                Write Review
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endwhile; $stmt_items->close(); ?>
                                        </tbody>

                                        <?php 
                                        $shipping_fee = 80;
                                        ?>

                                        <tfoot>
                                            <tr>
                                                <td colspan="<?php echo ($order_status === 'Completed') ? 4 : 3; ?>" 
                                                    class="text-end fw-bold" style="border-top: 1px solid #444;">
                                                    Items Total:
                                                </td>
                                                <td class="fw-bold" style="color: #c3c3c3ff; border-top: 1px solid #444;">
                                                    ₱<?php echo number_format($total, 2); ?>
                                                </td>
                                                <?php if ($order_status === 'Completed'): ?>
                                                    <td style="border-top: 1px solid #444;"></td>
                                                <?php endif; ?>
                                            </tr>

                                            <tr>
                                                <td colspan="<?php echo ($order_status === 'Completed') ? 4 : 3; ?>" 
                                                    class="text-end fw-bold">
                                                    Shipping Fee:
                                                </td>
                                                <td class="fw-bold" style="color: #c3c3c3ff;">
                                                    ₱<?php echo number_format($shipping_fee, 2); ?>
                                                </td>
                                                <?php if ($order_status === 'Completed'): ?>
                                                    <td></td>
                                                <?php endif; ?>
                                            </tr>

                                            <tr>
                                                <td colspan="<?php echo ($order_status === 'Completed') ? 4 : 3; ?>" 
                                                    class="text-end fw-bold" style="border-top: 1px solid #444;">
                                                    Total:
                                                </td>
                                                <td class="fw-bold" style="color: #00a884; border-top: 1px solid #444;">
                                                    ₱<?php echo number_format($total + $shipping_fee, 2); ?>
                                                </td>
                                                <?php if ($order_status === 'Completed'): ?>
                                                    <td style="border-top: 1px solid #444;"></td>
                                                <?php endif; ?>
                                            </tr>
                                        </tfoot>

                                    </table>
                                </div>
                                
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card-crud">
                    <div class="card-body text-center">
                        <p class="mb-0">You haven't placed any orders yet.</p>
                        <a href="../index.php" class="btn btn-info mt-3" style="color: #ffffff; font-weight: bold;">Start Shopping</a>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php
$stmt_orders->close();
include('../includes/footer.php');
?>
