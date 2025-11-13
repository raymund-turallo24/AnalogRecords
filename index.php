<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');
include('./includes/alert.php');

// === PRODUCTS LIST ===
$sql = "SELECT item_id, title, artist, genre, price, quantity 
        FROM item 
        WHERE quantity > 0 
        ORDER BY item_id ASC";
$results = mysqli_query($conn, $sql);

if ($results && mysqli_num_rows($results) > 0) {
    echo '<ul class="products" style="list-style:none; padding:0; display:flex; flex-wrap:wrap;">';
    while ($row = mysqli_fetch_assoc($results)) {

        // Fetch up to 3 images
        $img_sql = "SELECT image FROM item_images WHERE item_id = ? LIMIT 3";
        $stmt_img = mysqli_prepare($conn, $img_sql);
        mysqli_stmt_bind_param($stmt_img, "i", $row['item_id']);
        mysqli_stmt_execute($stmt_img);
        $result_img = mysqli_stmt_get_result($stmt_img);

        $images = [];
        while ($img_row = mysqli_fetch_assoc($result_img)) {
            $images[] = "./images/" . htmlspecialchars($img_row['image']);
        }
        mysqli_stmt_close($stmt_img);

        echo '<li class="product" style="margin:10px; width:220px;">';
        echo '<form method="POST" action="./cart/cart_update.php">';
        echo '<div class="product-content">';
        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';

        // Display all images in a row
        echo '<div class="product-images" style="display:flex; justify-content:center; gap:5px;">';
        if (!empty($images)) {
            foreach ($images as $img) {
                echo '<img src="' . $img . '" style="width:60px; height:60px; object-fit:cover; border:1px solid #ccc; border-radius:5px;">';
            }
        } else {
            echo '<img src="./images/no-image.png" style="width:60px; height:60px; object-fit:cover; border:1px solid #ccc; border-radius:5px;">';
        }
        echo '</div>';

        // Product info
        echo '<div class="product-info" style="margin-top:10px;">';
        echo '<p>Artist: ' . htmlspecialchars($row['artist']) . '</p>';
        echo '<p>Genre: ' . htmlspecialchars($row['genre']) . '</p>';
        echo '<p>Price: ₱' . number_format($row['price'], 2) . '</p>';
        echo '<fieldset>';
        echo '<label>Quantity';
        echo '<input type="number" name="item_qty" value="1" max="' . intval($row['quantity']) . '" min="1" />';
        echo '</label>';
        echo '</fieldset>';
        echo '<input type="hidden" name="item_id" value="' . intval($row['item_id']) . '" />';
        echo '<input type="hidden" name="type" value="add" />';
        echo '<div align="center"><button type="submit" class="add_to_cart">Add to Cart</button></div>';
        echo '</div>'; // .product-info

        echo '</div>'; // .product-content
        echo '</form>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p class="text-muted">No products available.</p>';
}

mysqli_close($conn);
?>