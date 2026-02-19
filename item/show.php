<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
include('../includes/alert.php');

// regex
function filter_bad_words($text) {
    $bad_words = [
    'fuck', 'fucking', 'fucker', 'motherfucker', 'shit', 'bullshit', 'asshole', 'ass', 'arse', 
    'bitch', 'cunt', 'dick', 'cock', 'pussy', 'whore', 'slut', 'nigger', 'nigga', 
    'faggot', 'fag', 'retard', 'bastard', 'damn', 'goddamn', 'wanker', 'twat', 'prick',

    'puta', 'putangina', 'putang ina mo', 'puta ka', 'gago', 'gagi', 'gagu', 'tangina', 
    'tangina mo', 'tanginamo', 'tanga', 'tanga ka', 'tarantado', 'ulol', 'baliw', 
    'bobo', 'bobo ka', 'kupal', 'panget', 'inutil', 'hindot', 'kantot', 'kantutan', 
    'leche', 'lecheng', 'yawa', 'animal', 'hayop', 'hayop ka', 'buang', 'pakshet', 
    'pakyu', 'pakyu', 'pakyu ka', 'pokpok', 'bilat', 'puke', 'pepe', 'kiki', 'tite', 
    'titi', 'jakol', 'jako', 'bj', 'chupa', 'supsup', 'libog', 'malibog', 'kingina', 
    'kanyam', 'kagaguhan', 'engot', 'gunggong', 'lechugas', 'walanghiya', 'walangya', 
    'demonyo', 'susmari', 'satan', 'punyeta', 'punyemas', 'lintik', 'sus ginoo', 
    'ulol ka', 'gaga ka', 'burat', 'susmaryosep', 'susmariosep', 'putres', 'pota', 
    'potangina', 'potaena', 'bwisit', 'bwiset', 'pesti', 'peste', 'gaga', 'shunga', 
    'siraulo', 'tado', 'tarugo', 'ogag', 'ampota', 'ampucha', 'euta', 'eutha'
];

    $patterns = [];
    foreach ($bad_words as $word) {
        $patterns[] = '/\b' . preg_quote($word, '/') . '\b/i';
    }

    return preg_replace($patterns, '****', $text);
}

$id = $_GET['id'] ?? 0;
$id = intval($id);

if ($id <= 0) {
    echo "<div class='container product-page-content'><p class='text-danger'>Invalid product ID.</p></div>";
    include('../includes/footer.php');
    exit;
}

// get all product details
$sql = "SELECT i.item_id, i.title, i.artist, i.genre, i.price, i.description, s.quantity
        FROM item i
        INNER JOIN stock s ON i.item_id = s.item_id
        WHERE i.item_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<div class='container product-page-content'><p class='text-muted'>Product not found.</p></div>";
    include('../includes/footer.php');
    exit;
}

$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// get images
$img_sql = "SELECT image FROM item_images WHERE item_id = ?";
$stmt_img = mysqli_prepare($conn, $img_sql);
mysqli_stmt_bind_param($stmt_img, "i", $id);
mysqli_stmt_execute($stmt_img);
$result_img = mysqli_stmt_get_result($stmt_img);

$images = [];
while ($img_row = mysqli_fetch_assoc($result_img)) {
    $images[] = "../images/" . htmlspecialchars($img_row['image']);
}
mysqli_stmt_close($stmt_img);
?>

<div class="container product-page-content">

    <?php include('../includes/alert.php'); ?>

    <div class="row product-detail-row">
        
        <div class="col-md-5 mb-4">
            <div class="main-image-wrapper">
                <?php 
                $main_image = !empty($images) ? $images[0] : '../images/no-image.png'; 
                ?>
                <img src="<?php echo $main_image; ?>" id="mainProductImage" class="main-product-image" alt="Album Cover">
            </div>

            <?php if (!empty($images)): ?>
            <div class="product-thumbnails mt-3">
                <?php foreach ($images as $index => $img): ?>
                    <img 
                        src="<?php echo $img; ?>" 
                        class="thumbnail-image <?php echo $index === 0 ? 'active-thumbnail' : ''; ?>" 
                        alt="Thumbnail <?php echo $index + 1; ?>"
                        onclick="swapImage('<?php echo $img; ?>', this)"
                    >
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-7">
            <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
            <p class="product-artist"><?php echo htmlspecialchars($product['artist']); ?></p>

            <div class="product-meta mb-3">
                <span class="text-muted">Genre:</span> <?php echo htmlspecialchars($product['genre']); ?>
                <span class="meta-divider">|</span>
                <span class="text-muted">Stock:</span> 
                <?php if (intval($product['quantity']) > 0): ?>
                    <span class="in-stock"><?php echo intval($product['quantity']); ?> In Stock</span>
                <?php else: ?>
                    <span class="out-of-stock">Out of Stock</span>
                <?php endif; ?>
            </div>

            <div class="product-price-box mb-4">
                ₱<?php echo number_format($product['price'], 2); ?>
            </div>

            <?php if (!empty($product['description'])): ?>
                <h4 class="description-heading">Description</h4>
                <div class="description-body mb-4">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            <?php endif; ?>

            <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
            <div class="add-to-cart-section">
                <h4 class="form-heading">Add to Cart</h4>
                
                <form id="itemAddToCartForm" method="POST" action="../cart/cart_update.php" 
                      data-logged-in="<?php echo isset($_SESSION['account_id']) ? '1' : '0'; ?>">
                    <div class="qty-control mb-3">
                        <label class="qty-label" for="item_qty">Quantity:</label>
                        <input type="number" name="item_qty" id="item_qty" value="1" 
                            class="form-control custom-input qty-input"
                            max="<?php echo intval($product['quantity']); ?>" 
                            min="1" 
                            <?php echo (intval($product['quantity']) <= 0) ? 'disabled' : ''; ?>
                        />
                    </div>
                    
                    <input type="hidden" name="item_id" value="<?php echo intval($product['item_id']); ?>" />
                    <input type="hidden" name="type" value="add" />
                    
                    <button type="submit" id="addToCartButton" class="btn btn-primary-theme add-cart-btn" 
                        <?php echo (intval($product['quantity']) <= 0) ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                    </button>
                </form>
            </div>
            <?php else: ?>
                <p class="mt-4 text-muted fst-italic">Admin View: Purchasing is disabled on the detail page.</p>
            <?php endif; ?>

            <div class="back-link mt-4">
                <a href="../index.php"><i class="fas fa-arrow-left me-1"></i> Back to Products</a>
            </div>
        </div>
    </div>

    <div class="row review-section-row">
        <div class="col-12">
            <h3 class="review-heading">Customer Reviews</h3>
        </div>

        <?php
        $sql_reviews = "
            SELECT r.rating, r.review, r.date_created,
                   cd.first_name, cd.last_name
            FROM item_reviews r
            INNER JOIN customer_details cd ON r.customer_id = cd.customer_id
            WHERE r.item_id = ?
            ORDER BY r.date_created DESC
        ";

        $stmt_rev = mysqli_prepare($conn, $sql_reviews);
        mysqli_stmt_bind_param($stmt_rev, "i", $id);
        mysqli_stmt_execute($stmt_rev);
        $result_rev = mysqli_stmt_get_result($stmt_rev);

        if (mysqli_num_rows($result_rev) > 0):
            while ($rev = mysqli_fetch_assoc($result_rev)):

                $clean_review = filter_bad_words($rev['review']);
                $review_text = nl2br(htmlspecialchars($clean_review));
                $fullname = htmlspecialchars($rev['first_name'] . " " . $rev['last_name']);
                $rating = intval($rev['rating']);
                $date = date("F j, Y", strtotime($rev['date_created']));
        ?>
            <div class="col-md-6 mb-4">
                <div class="review-card">

                    <p class="review-rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php echo ($i <= $rating) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                        <?php endfor; ?>
                    </p>

                    <?php if (!empty($review_text)): ?>
                        <p class="review-body"><?php echo $review_text; ?></p>
                    <?php endif; ?>

                    <p class="review-meta">
                        <strong><?php echo $fullname; ?></strong> on <?php echo $date; ?>
                    </p>
                </div>
            </div>

        <?php endwhile; else: ?>
            <div class="col-12">
                <p class="no-reviews-msg text-center">No reviews for this item yet.</p>
            </div>
        <?php endif;
        mysqli_stmt_close($stmt_rev);
        ?>

    </div>
</div>


<style>
.custom-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    animation: fadeInOverlay 0.3s ease forwards;
}
@keyframes fadeInOverlay { from { opacity: 0; } to { opacity: 1; } }

.custom-modal {
    background: #1d1d1d;
    color: #fff;
    padding: 30px 35px;
    border-radius: 15px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    transform: scale(0.8);
    animation: popIn 0.4s ease forwards;
    position: relative;
}
@keyframes popIn { to { transform: scale(1); } }

.custom-modal h4 {
    font-size: 1.6rem;
    font-weight: bold;
    margin-bottom: 15px;
    color: #f3f3f3ff;
    text-shadow: 1px 1px 5px rgba(0,0,0,0.3);
}

.custom-modal p {
    font-size: 1rem;
    opacity: 50%;
    margin-bottom: 25px;
    line-height: 1.5;
}

.custom-modal .modal-btn {
    display: inline-block;
    padding: 10px 25px;
    font-size: 1rem;
    color: #f4f4f4ff;
    background: #00745c;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}

.custom-modal .modal-btn:hover {
    background: transparent;
    color: #ffffff;
}

.custom-modal .modal-btn:focus, 
.custom-modal .modal-button:active {
    outline: none;
    box-shadow: none;
}


.custom-modal .close-modal {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 1.2rem;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
}
.custom-modal .close-modal:hover { color: #e4e4e4ff; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function swapImage(newSrc, clickedElement) {
    document.getElementById('mainProductImage').src = newSrc;
    const thumbnails = document.querySelectorAll('.thumbnail-image');
    thumbnails.forEach(thumb => thumb.classList.remove('active-thumbnail'));
    clickedElement.classList.add('active-thumbnail');
}

$(document).ready(function() {
    $('#addToCartButton').off('click').on('click', function(e) {
        e.preventDefault();
        var $form = $('#itemAddToCartForm');
        var isLoggedIn = $form.data('logged-in') == 1;

        if (!isLoggedIn) {
            $('#loginModal').remove();
            const modalHtml = `
                <div id="loginModal" class="custom-modal-overlay">
                    <div class="custom-modal">
                        <span class="close-modal">&times;</span>
                        <h4>Not Logged In</h4>
                        <p>You must be logged in to add items to your cart.</p>
                        <button class="modal-btn" id="loginRedirectBtn">Login Now</button>
                    </div>
                </div>`;
            $('body').append(modalHtml);

            $('#loginModal .close-modal').on('click', function() {
                $('#loginModal').fadeOut(200, function() { $(this).remove(); });
            });

            $('#loginRedirectBtn').on('click', function() {
                window.location.href = "../user/login.php";
            });
            return;
        }
        
        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert('Item added to cart!');
                } else if (response.status === 'unauthorized') {
                    alert('You must log in first.');
                    window.location.href = "../user/login.php";
                } else {
                    alert('Something went wrong.');
                }
            }
        });
    });
});
</script>

<?php
mysqli_close($conn);
include('../includes/footer.php');
?>
