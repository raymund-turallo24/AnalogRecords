<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

// admin only access
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    $_SESSION['error'] = "No order ID provided.";
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];

// get order info
$stmt = $conn->prepare("
    SELECT o.*, c.first_name, c.last_name
    FROM orderinfo o
    JOIN customer_details c ON o.customer_id = c.customer_id
    WHERE o.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['error'] = "Order #{$order_id} not found.";
    header("Location: index.php");
    exit();
}

// get order items at calculate ang subtotal
$items_subtotal = 0;
$order_items = [];

$stmt_items = $conn->prepare("
    SELECT oi.orderline_id, oi.quantity, oi.price, 
           i.title, i.artist 
    FROM orderline oi             
    JOIN item i ON oi.item_id = i.item_id
    WHERE oi.order_id = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

if ($result_items) {
    while ($item = $result_items->fetch_assoc()) {
        $subtotal = $item['quantity'] * $item['price']; 
        $item['subtotal'] = $subtotal;
        $item['price_at_purchase'] = $item['price']; // Used for display consistency
        $items_subtotal += $subtotal; 
        $order_items[] = $item;
    }
}
$stmt_items->close();


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_details'])){

    $order_status = trim($_POST['order_status']);
    $shipping_address = trim($_POST['shipping_address']);
    $remarks = trim($_POST['remarks']);

    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
    if (!in_array($order_status, $allowed_statuses)) {
        $_SESSION['error'] = "Invalid order status submitted.";
        header("Location: update.php?id=" . $order_id);
        exit();
    }
    
    $update_stmt = $conn->prepare("
        UPDATE orderinfo
        SET order_status=?, shipping_address=?, remarks=?
        WHERE order_id=?
    ");
    $update_stmt->bind_param("sssi", $order_status, $shipping_address, $remarks, $order_id);

    if($update_stmt->execute()){
        $_SESSION['success'] = "Order details updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating order: " . $conn->error;
    }

    $update_stmt->close();
    header("Location: update.php?id=" . $order_id);
    exit();
}
?>

<div class="container-fluid site-content-wrapper">

    <div class="card-crud my-5">
        <div class="card-header-crud d-flex justify-content-between align-items-center">
            <h2 class="card-heading-crud">Update Order #<?= htmlspecialchars($order_id) ?></h2>
            <a href="index.php" class="btn back-btn"><i class="fas fa-chevron-left me-1"></i> Back to Orders</a>
        </div>

        <div class="card-body-crud p-4">

            <?php if (isset($_SESSION['success'])): ?>
                <div class='alert alert-success alert-crud'>
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class='alert alert-danger alert-crud'>
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-5">
                    <h4 class="section-heading mb-3">Order Information</h4>
                    <form method="POST" class="crud-form">
                        <input type="hidden" name="update_details" value="1">

                        <div class="form-group mb-3">
                            <label>Customer:</label>
                            <input type="text" class="form-control custom-input"
                                   value="<?= htmlspecialchars($order['first_name'] . " " . $order['last_name']) ?>"
                                   readonly>
                            <small class="form-text text-muted">Order Date: <?= date("Y-m-d H:i", strtotime($order['order_date'])) ?></small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="order_status">Order Status:</label>
                            <select name="order_status" id="order_status" class="form-control custom-input" required>
                                <?php
                                $statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
                                foreach($statuses as $status){
                                    $selected = ($status == $order['order_status']) ? 'selected' : '';
                                    echo "<option value='{$status}' {$selected}>{$status}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="shipping_address">Shipping Address:</label>
                            <textarea name="shipping_address" id="shipping_address" class="form-control custom-textarea" rows="3" required><?= htmlspecialchars($order['shipping_address']) ?></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="remarks">Remarks:</label>
                            <textarea name="remarks" id="remarks" class="form-control custom-textarea" rows="2"><?= htmlspecialchars($order['remarks']) ?></textarea>
                        </div>

                        <hr class="form-divider">
                        <button type="submit" class="btn btn-primary-theme w-100">
                            <i class="fas fa-sync-alt me-1"></i> Update Details
                        </button>
                    </form>
                </div>
                
                <div class="col-md-7">
                    <h4 class="section-heading mb-3">Order Items</h4>
                    
                    <?php if (!empty($order_items)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm data-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                    <tr class="order-item-row mt-4 p-3 bg-light-dark" style=> <td>
                                            <strong><?= htmlspecialchars($item['title']) ?></strong><br>
                                            <span class="text-secondary small-text">by <?= htmlspecialchars($item['artist']) ?></span>
                                        </td>
                                        <td class="text-end">₱<?= number_format($item['price_at_purchase'], 2) ?></td>
                                        <td class="text-end"><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td class="text-end">₱<?= number_format($item['subtotal'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning alert-crud">
                            <i class="fas fa-exclamation-circle me-2"></i> This order currently contains no items.
                        </div>
                    <?php endif; ?>
                    
                    <div class="financial-summary mt-4 p-3 bg-light-dark" style="border: 1px solid #444;">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Item Subtotal:</span>
                            <strong>₱<?= number_format($order['subtotal'], 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Shipping Fee:</span>
                            <strong>₱<?= number_format($order['shipping_fee'], 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2" style="border-top: 1px solid #666;">
                            <strong>Grand Total:</strong>
                            <strong class="text-success fs-5">₱<?= number_format($order['total'], 2) ?></strong>
                        </div>
                    </div>

                    <a href="index.php" class="btn btn-secondary-theme mt-3"><i class="fas fa-list me-1"></i> Back to All Orders</a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php 

mysqli_close($conn); 
include('../includes/footer.php'); 
?>