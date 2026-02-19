<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

// check if nalogin si customer
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['error'] = "You must be logged in to leave a review.";
    header("Location: ../user/login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$item_id     = intval($_GET['item_id'] ?? 0);
$order_id    = intval($_GET['order_id'] ?? 0);

if ($item_id === 0 || $order_id === 0) {
    $_SESSION['error'] = "Invalid item or order specified.";
    header("Location: ../user/orders.php");
    exit();
}

$success_message = '';
$error_message   = '';
$has_reviewed    = false;
$previous_rating = 0;
$previous_review = '';

// fetch ang item and check if already reviewed
$stmt_item = $conn->prepare("
    SELECT i.title, 
           r.review_id, r.rating, r.review
    FROM item i
    LEFT JOIN item_reviews r 
        ON i.item_id = r.item_id 
        AND r.customer_id = ? 
        AND r.order_id = ?
    WHERE i.item_id = ?
");
$stmt_item->bind_param("iii", $customer_id, $order_id, $item_id);
$stmt_item->execute();
$result_item = $stmt_item->get_result();

if ($result_item->num_rows === 0) {
    $_SESSION['error'] = "Item not found or not part of this order.";
    header("Location: ../user/orders.php");
    exit();
}

$item_data   = $result_item->fetch_assoc();
$item_title  = $item_data['title'];

if (!empty($item_data['review_id'])) {
    $has_reviewed    = true;
    $previous_rating = $item_data['rating'];
    $previous_review = $item_data['review'];
}
$stmt_item->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error_message = "Please select a rating from 1 to 5 stars.";
    }
    // makakapag-insert ng review kung hindi pa nakakagawa
    elseif (!$has_reviewed) {
        $stmt_insert = $conn->prepare("
            INSERT INTO item_reviews 
                (item_id, customer_id, order_id, rating, review, date_created) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt_insert->bind_param("iiiis", $item_id, $customer_id, $order_id, $rating, $review);

        if ($stmt_insert->execute()) {
            $success_message = "Thank you! Your review has been submitted successfully.";
            $has_reviewed    = true;
            $previous_rating = $rating;
            $previous_review = $review;
        } else {
            $error_message = "Failed to submit review. Please try again.";
        }
        $stmt_insert->close();
    }
}
?>

<div class="container-fluid site-content-wrapper">
    <div class="card-crud my-5">
        <div class="card-header-crud d-flex justify-content-between align-items-center">
            <h2 class="card-heading-crud">
                <i class="fas fa-star me-2"></i> Review: <?php echo htmlspecialchars($item_title); ?>
            </h2>
            <a href="../user/orders.php" class="btn back-btn">
                <i class="fas fa-chevron-left me-1"></i> Back to Orders
            </a>
        </div>

        <div class="card-body-crud p-4">

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-crud">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-crud">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($has_reviewed): ?>
                <div class="alert alert-success border-0 bg-light text-dark shadow-sm">
                    <i class="fas fa-check me-2"></i>
                    <strong>Thank you for your review!</strong><br>
                    Your feedback has been recorded for Order #<?php echo $order_id; ?>.
                </div>

                <h4 class="mt-4 mb-3">Your Review</h4>
                <div class="p-4 border rounded bg-white shadow-sm">
                    <p class="mb-3 fs-5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php echo ($i <= $previous_rating) 
                                ? '<i class="fas fa-star text-warning"></i>' 
                                : '<i class="far fa-star text-muted"></i>'; ?>
                        <?php endfor; ?>
                        <span class="ms-2 fw-bold">(<?php echo $previous_rating; ?>/5)</span>
                    </p>

                    <?php if (!empty($previous_review)): ?>
                        <p class="mb-0 text-dark"><?php echo nl2br(htmlspecialchars($previous_review)); ?></p>
                    <?php else: ?>
                        <p class="mb-0 text-muted fst-italic">No written review provided.</p>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="../cart/view_order.php" class="btn btn-primary-theme">
                            <i class="fas fa-arrow-left me-1"></i> Back to My Orders
                        </a>
                    </div>
                </div>

            <!-- review form (first time only) -->
            <?php else: ?>
                <p class="mb-4 text-muted">
                    Share your experience with this item from Order #<?php echo $order_id; ?>.
                </p>

                <form method="POST" class="crud-form">
                    <div class="form-group mb-4">
                        <label class="form-label d-block">
                            Rating <span class="text-danger">*</span>
                        </label>
                        <div class="rating-stars">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="review" class="form-label">Written Review (Optional)</label>
                        <textarea name="review" id="review" class="form-control custom-textarea" rows="5"
                                  placeholder="Tell us what you liked or any suggestions..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="../cart/view_order.php" class="btn btn-secondary-theme">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn login-btn">
                            <i class="fas fa-paper-plane me-1"></i> Submit Review
                        </button>
                    </div>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
.rating-stars {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 10px;
}
.rating-stars input { display: none; }
.rating-stars label {
    font-size: 2.4rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}
.rating-stars label i { pointer-events: none; }
.rating-stars input:checked ~ label,
.rating-stars label:hover,
.rating-stars label:hover ~ label {
    color: #ffc107;
}
</style>

<?php include('../includes/footer.php'); ?>