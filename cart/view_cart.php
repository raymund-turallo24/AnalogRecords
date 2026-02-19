<?php
session_start();
ob_start(); 

include('../includes/header.php');
include('../includes/config.php');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // action for updating the cart
    if (isset($_POST['update_cart'])) {
        $changes_made = false;

        // Update ng quantities
        if (isset($_POST['product_qty']) && is_array($_POST['product_qty'])) {
            foreach ($_POST['product_qty'] as $item_id => $qty) {
                $item_id = intval($item_id);
                // makes sure quantity is at least 1
                $qty = max(1, intval($qty)); 
                
                if (isset($_SESSION['cart_products'][$item_id])) {
                    if (intval($_SESSION['cart_products'][$item_id]['item_qty']) !== $qty) {
                        $_SESSION['cart_products'][$item_id]['item_qty'] = $qty;
                        $changes_made = true;
                    }
                }
            }
        }

        // remove ang selected items
        if (isset($_POST['remove_code']) && is_array($_POST['remove_code'])) {
            foreach ($_POST['remove_code'] as $item_id) {
                $item_id = intval($item_id);
                if (isset($_SESSION['cart_products'][$item_id])) {
                    unset($_SESSION['cart_products'][$item_id]);
                    $changes_made = true;
                }
            }
        }

        if ($changes_made) {
            $_SESSION['status'] = ['type' => 'success', 'message' => "Cart updated successfully!"];
        } else {
            // Check kung cart ay empty na after removing items
            if (empty($_SESSION['cart_products'])) {
                $_SESSION['status'] = ['type' => 'warning', 'message' => "Your cart is now empty."];
            } else {
                $_SESSION['status'] = ['type' => 'warning', 'message' => "No changes made. Adjust quantities or select items to remove."];
            }
        }
    }

    // checkout action
    if (isset($_POST['checkout'])) {
        if (!isset($_SESSION['account_id'])) {
             // Not logged in
            $_SESSION['status'] = ['type' => 'warning', 'message' => "You must be logged in to checkout. Please log in or register."];
        } else if (isset($_SESSION['cart_products']) && count($_SESSION['cart_products']) > 0) {
            // Logged in and cart has items, proceed to checkout processing page
            $_SESSION['remarks'] = trim($_POST['remarks'] ?? ''); 
            header("Location: checkout.php");
            ob_end_flush();
            exit();
        } else {
            // Logged in pero walang laman ang cart
            $_SESSION['status'] = ['type' => 'warning', 'message' => "Your cart is empty! Cannot checkout."];
        }
    }

    if (isset($_POST['update_cart'])) {
        header("Location: view_cart.php");
        ob_end_flush();
        exit();
    }
}

// notif
if (isset($_SESSION['status'])) {
    if ($_SESSION['status']['type'] === 'success') {
        $success_message = $_SESSION['status']['message'];
    } elseif ($_SESSION['status']['type'] === 'warning' || $_SESSION['status']['type'] === 'error') {
        $error_message = $_SESSION['status']['message'];
    }
    unset($_SESSION['status']);
}

// Set initial total for display
$total = 0;
$cart_has_items = isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0;
if ($cart_has_items) {
    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $product_qty = intval($cart_itm["item_qty"]);
        $product_price = floatval($cart_itm["item_price"]);
        $total += $product_price * $product_qty;
    }
}

?>

<div class="site-content-wrapper">
    <div class="row justify-content-center">
        <div class="col-12">
            <h1 class="crud-title">View Cart</h1>
            
            <?php
            // Consolidated alert display
            if (!empty($success_message)) {
                echo "<div class='alert alert-success alert-crud text-center' role='alert'>{$success_message}</div>";
            }
            if (!empty($error_message)) {
                echo "<div class='alert alert-info alert-crud text-center' role='alert'>{$error_message}</div>";
            }
            ?>
            
            <div class="card-crud p-0 mb-4">
                <form method="POST">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Quantity</th>
                                    <th style="width: 45%;">Name</th>
                                    <th style="width: 15%;">Price (PHP)</th>
                                    <th style="width: 15%;">Total (PHP)</th>
                                    <th style="width: 10%;" class="text-center">Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($cart_has_items) {
                                foreach ($_SESSION["cart_products"] as $cart_itm) {
                                    $product_name = htmlspecialchars($cart_itm["item_name"]);
                                    $product_qty = intval($cart_itm["item_qty"]);
                                    $product_price = floatval($cart_itm["item_price"]);
                                    $product_code = intval($cart_itm["item_id"]);
                                    $subtotal = $product_price * $product_qty;
                                    
                                    echo '<tr>';
                                    // Quantity Input field
                                    echo '<td><input type="number" min="1" size="2" maxlength="3" name="product_qty[' . $product_code . ']" value="' . $product_qty . '" class="form-control custom-input qty-input"/></td>';
                                    echo '<td>' . $product_name . '</td>';
                                    echo '<td>' . number_format($product_price, 2) . '</td>';
                                    echo '<td>' . number_format($subtotal, 2) . '</td>';
                                    // Remove Checkbox
                                    echo '<td class="text-center"><input type="checkbox" name="remove_code[]" value="' . $product_code . '" /></td>';
                                    echo '</tr>';
                                }
                                
                            } else {
                                echo '<tr><td colspan="5" class="text-center text-muted p-5">Your cart is empty.</td></tr>';
                            }
                            ?>
                            </tbody>
                            
                            <?php if ($cart_has_items): ?>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end" style="padding-right: 18px; background-color: #252525; border-top: 1px solid #444;">
                                        <h4 class="m-0 py-2" style="color:#ffffff; font-weight:700;">
                                            Amount Payable: <span style="color: #00a884;">₱<?php echo number_format($total, 2); ?></span>
                                        </h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" style="text-align:right; padding-top:20px; background-color: #1a1a1a; border-top: none;">
                                        <label for="remarks" class="input-label" style="display: block; text-align: left; margin-left: 20px;">Remarks (optional):</label>
                                        <textarea name="remarks" id="remarks" rows="3" class="form-control custom-textarea mb-3" style="width: 100%;" placeholder="Any notes for your order..."></textarea>
                                        
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="submit" name="update_cart" class="btn btn-secondary-theme" style="font-weight: bold;">Update Cart</button>
                                            
                                            <a href="../index.php" class="btn btn-info" style="color: #ffffff; font-weight: bold; text-align: center; display: inline-block; padding: 6px 12px;">Add Items</a>
                                            
                                            <button type="submit" name="checkout" class="btn add-cart-btn" style="background-color: #00a884; color: #ffffff; font-weight: bold;">Checkout</button>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>

<?php 
include('../includes/footer.php'); 
// Final flush
ob_end_flush();
?>