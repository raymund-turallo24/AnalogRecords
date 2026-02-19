<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$success_message = '';
$error_message = '';

// update ang review
if (isset($_POST['update_review'])) {
    $review_id = intval($_POST['review_id']);
    $rating = intval($_POST['rating']);
    $review_text = $_POST['review_text'] ?? '';

    // makes sure if sa customer galing ang review
    $stmt_check = $conn->prepare("SELECT review_id FROM item_reviews WHERE review_id = ? AND customer_id = ?");
    $stmt_check->bind_param("ii", $review_id, $customer_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_update = $conn->prepare("UPDATE item_reviews SET rating = ?, review = ? WHERE review_id = ?");
        $stmt_update->bind_param("isi", $rating, $review_text, $review_id);
        if ($stmt_update->execute()) {
            $success_message = "Review updated successfully!";
        } else {
            $error_message = "Failed to update review.";
        }
        $stmt_update->close();
    } else {
        $error_message = "Invalid review.";
    }
    $stmt_check->close();
    
    header("Location: myreviews.php?success=" . urlencode($success_message) . "&error=" . urlencode($error_message));
    exit();
}

if (isset($_GET['success']) && $_GET['success']) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error']) && $_GET['error']) {
    $error_message = htmlspecialchars($_GET['error']);
}


// Fetch lahat ng reviews galing sa customer
$stmt_reviews = $conn->prepare("
    SELECT r.review_id, r.rating, r.review, r.date_created, i.title, o.order_id, o.order_status
    FROM item_reviews r
    JOIN item i ON r.item_id = i.item_id
    JOIN orderinfo o ON r.order_id = o.order_id
    WHERE r.customer_id = ?
    ORDER BY r.date_created DESC
");
$stmt_reviews->bind_param("i", $customer_id);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();
?>

<div class="site-content-wrapper">
    <div class="row justify-content-center">
        <div class="col-12">
            <h1 class="crud-title mt-4 mb-4">My Submitted Reviews</h1>

            <?php
            if ($success_message) echo "<div class='alert alert-success alert-crud text-center'>{$success_message}</div>";
            if ($error_message) echo "<div class='alert alert-warning alert-crud text-center'>{$error_message}</div>";
            ?>

            <?php if ($result_reviews->num_rows > 0): ?>
                <div class="reviews-list">
                    <?php while ($review = $result_reviews->fetch_assoc()): ?>
                        <div class="card-crud mb-4">
                            <div class="card-body p-4">
                                
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                    <div class="text-start">
                                        <h4 class="text-white mb-1">Item: <?php echo htmlspecialchars($review['title']); ?></h4>
                                        <p class="text-secondary small mb-0">
                                            Reviewed on: <?php echo date('F j, Y, g:i a', strtotime($review['date_created'])); ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <div class="review-rating-stars mb-1">
                                            <?php 
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo '<i class="fas fa-star' . ($i <= $review['rating'] ? '' : ' far') . '"></i>';
                                            }
                                            ?>
                                        </div>
                                        <p class="text-white fw-bold mb-0">Order #<?php echo $review['order_id']; ?></p>
                                    </div>
                                </div>
                                
                                <p class="text-white fw-bold mb-2">Your Current Review:</p>
                                <blockquote class="blockquote-footer text-light ps-3 border-start border-4 border-info">
                                    <?php echo htmlspecialchars($review['review']); ?>
                                </blockquote>

                                <form method="POST" class="mt-4 crud-form">
                                    <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                    
                                    <h5 class="text-secondary mb-3">Update Review:</h5>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-group-label" for="rating_<?php echo $review['review_id']; ?>">New Rating (1-5)</label>
                                            <input type="number" 
                                                   name="rating" 
                                                   id="rating_<?php echo $review['review_id']; ?>" 
                                                   min="1" max="5" 
                                                   class="form-control custom-input" 
                                                   value="<?php echo $review['rating']; ?>"
                                                   required>
                                            <small class="form-text text-muted">Current rating: <?php echo $review['rating']; ?></small>
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label class="form-group-label" for="review_text_<?php echo $review['review_id']; ?>">New Review Text</label>
                                            <textarea name="review_text" 
                                                      id="review_text_<?php echo $review['review_id']; ?>" 
                                                      class="form-control custom-textarea" 
                                                      rows="3" 
                                                      placeholder="Enter new review (optional)"><?php echo htmlspecialchars($review['review']); ?></textarea>
                                        </div>
                                    </div>

                                    <button type="submit" name="update_review" class="btn btn-success-theme mt-2">
                                        <i class="fas fa-save me-1"></i> Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card-crud">
                    <div class="card-body text-center">
                        <p class="text-light mb-0">You have not submitted any reviews yet.</p>
                        <a href="../index.php" class="btn btn-info mt-3" style="color: #ffffff; font-weight: bold;">Browse Products</a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
$stmt_reviews->close();
include('../includes/footer.php');
?>