<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// admins/authorized users can access this page lang
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

// dropdown customer list
$customers = $conn->query("SELECT customer_id, first_name, last_name FROM customer_details ORDER BY last_name ASC");

// get available items
$items_result = $conn->query("SELECT item_id, title, artist, price FROM item ORDER BY title ASC");

if ($customers === false || $items_result === false) {
    $_SESSION['error'] = "Could not load required lists (Customers or Items).";
}
?>

<div class="container-fluid site-content-wrapper">

    <div class="card-crud my-5">
        <div class="card-header-crud d-flex justify-content-between align-items-center">
            <h2 class="card-heading-crud">Create New Order</h2>
            <a href="index.php" class="btn back-btn"><i class="fas fa-chevron-left me-1"></i> Back to Orders</a>
        </div>
        
        <div class="card-body-crud p-4">
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class='alert alert-danger alert-crud'>
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="store.php" method="post" class="crud-form">
                
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-heading mb-3">Order Details</h4>
                        <div class="form-group mb-3">
                            <label for="customer_id">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-control custom-input" required>
                                <option value="">--- Select Customer ---</option>
                                <?php if ($customers && $customers->num_rows > 0): ?>
                                    <?php while($c = $customers->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($c['customer_id']) ?>">
                                            <?= htmlspecialchars($c['last_name'] . ', ' . $c['first_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="" disabled>No customers available</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="order_status">Order Status <span class="text-danger">*</span></label>
                            <select name="order_status" id="order_status" class="form-control custom-input" required>
                                <option value="Pending" selected>Pending</option>
                                <option value="Processing">Processing</option>
                                <option value="Shipped">Shipped</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <small class="form-text text-muted">Initial status for a new order.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="section-heading mb-3">Shipping & Notes</h4>
                        <div class="form-group mb-3">
                            <label for="shipping_address">Shipping Address <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_address" id="shipping_address" class="form-control custom-input" required>
                            <small class="form-text text-muted">Use the customer's default address or enter a new one.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="remarks">Remarks / Notes</label>
                            <textarea name="remarks" id="remarks" class="form-control custom-textarea" rows="4"></textarea>
                        </div>
                    </div>
                </div>

                <hr class="form-divider mt-0">
                <h4 class="section-heading mb-3">Initial Item to Add</h4>

                <div class="row align-items-end">
                    <div class="col-md-7 mb-3">
                        <label for="item_id_1">Select Item <span class="text-danger">*</span></label>
                        <select name="item_id[]" id="item_id_1" class="form-control custom-input" required>
                            <option value="">--- Select Item ---</option>
                            <?php 
                            if ($items_result) $items_result->data_seek(0); 
                            ?>
                            <?php if ($items_result && $items_result->num_rows > 0): ?>
                                <?php while($item_opt = $items_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($item_opt['item_id']) ?>">
                                        <?= htmlspecialchars($item_opt['title']) ?> (by <?= htmlspecialchars($item_opt['artist']) ?>) - ₱<?= number_format($item_opt['price'], 2) ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="" disabled>No items available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="quantity_1">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity[]" id="quantity_1" class="form-control custom-input" value="1" min="1" required>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <span class="text-muted small-text d-block">Required to calculate initial total.</span>
                    </div>
                </div>
                <hr class="form-divider">

                <div class="d-flex justify-content-end gap-2">
                    <a href="index.php" class="btn btn-secondary-theme">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn login-btn">
                        <i class="fas fa-save me-1"></i> Create Order & Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
if ($items_result) $items_result->free();
if ($customers) $customers->free();
mysqli_close($conn); 
include('../includes/footer.php'); 
?>