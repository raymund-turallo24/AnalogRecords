<?php
session_start();
include('../includes/config.php');
include('../includes/adminHeader.php');

// Check if item_id is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid item ID.";
    header("Location: index.php");
    exit;
}

$item_id = $_GET['id'];

// Fetch item details with stock
$sql = "SELECT item.*, stock.quantity AS stock_qty
        FROM item 
        LEFT JOIN stock USING (item_id)
        WHERE item.item_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);

if (!$item) {
    $_SESSION['error'] = "Item not found.";
    header("Location: index.php");
    exit;
}

// Fetch all images for this item
$sql_images = "SELECT * FROM item_images WHERE item_id = ?";
$stmt_img = mysqli_prepare($conn, $sql_images);
mysqli_stmt_bind_param($stmt_img, "i", $item_id);
mysqli_stmt_execute($stmt_img);
$result_images = mysqli_stmt_get_result($stmt_img);
$images = mysqli_fetch_all($result_images, MYSQLI_ASSOC);

// Handle update form submission
if (isset($_POST['update'])) {

    // Keep old data if input is empty
    $title = !empty(trim($_POST['title'])) ? trim($_POST['title']) : $item['title'];
    $artist = !empty(trim($_POST['artist'])) ? trim($_POST['artist']) : $item['artist'];
    $genre = !empty(trim($_POST['genre'])) ? trim($_POST['genre']) : $item['genre'];
    $price = !empty(trim($_POST['price'])) ? trim($_POST['price']) : $item['price'];
    $description = !empty(trim($_POST['description'])) ? trim($_POST['description']) : $item['description'];
    $quantity = !empty(trim($_POST['quantity'])) ? trim($_POST['quantity']) : $item['stock_qty'];

    // Validate numeric fields only if entered
    if (!empty($_POST['price']) && !is_numeric($price)) {
        $_SESSION['error'] = "Price must be numeric.";
        header("Location: update.php?id=$item_id");
        exit;
    }
    if (!empty($_POST['quantity']) && !is_numeric($quantity)) {
        $_SESSION['error'] = "Quantity must be numeric.";
        header("Location: update.php?id=$item_id");
        exit;
    }

    // Update item table
    $sql_update_item = "UPDATE item 
                        SET title=?, artist=?, genre=?, price=?, description=? 
                        WHERE item_id=?";
    $stmt_item = mysqli_prepare($conn, $sql_update_item);
    mysqli_stmt_bind_param($stmt_item, "sssssi", $title, $artist, $genre, $price, $description, $item_id);
    mysqli_stmt_execute($stmt_item);

    // Update stock table
    $sql_update_stock = "UPDATE stock SET quantity=? WHERE item_id=?";
    $stmt_stock = mysqli_prepare($conn, $sql_update_stock);
    mysqli_stmt_bind_param($stmt_stock, "ii", $quantity, $item_id);
    mysqli_stmt_execute($stmt_stock);

    $uploadDir = '../images/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Replace old images if new images are uploaded
    if (!empty($_FILES['images']['name'][0])) {

        // Delete old images
        foreach ($images as $img) {
            $oldFile = $uploadDir . $img['image'];
            if (file_exists($oldFile)) unlink($oldFile);

            $sql_del_img = "DELETE FROM item_images WHERE image_id=?";
            $stmt_del_img = mysqli_prepare($conn, $sql_del_img);
            mysqli_stmt_bind_param($stmt_del_img, "i", $img['image_id']);
            mysqli_stmt_execute($stmt_del_img);
            mysqli_stmt_close($stmt_del_img);
        }

        // Upload new images
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] == 0) {
                $fileType = $_FILES['images']['type'][$key];
                if (in_array($fileType, ["image/jpeg", "image/jpg", "image/png"])) {
                    $fileName = time() . '_' . basename($_FILES['images']['name'][$key]);
                    $target = $uploadDir . $fileName;
                    if (move_uploaded_file($tmp_name, $target)) {
                        $sql_add_img = "INSERT INTO item_images (item_id, image) VALUES (?, ?)";
                        $stmt_add_img = mysqli_prepare($conn, $sql_add_img);
                        mysqli_stmt_bind_param($stmt_add_img, "is", $item_id, $fileName);
                        mysqli_stmt_execute($stmt_add_img);
                        mysqli_stmt_close($stmt_add_img);
                    }
                }
            }
        }
    }

    $_SESSION['success'] = "Item successfully updated!";
    header("Location: index.php");
    exit;
}
?>

<div class="container mt-5">
    <h2>Update Item</h2>

    <?php
    // Display messages
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'>{$_SESSION['success']}</div>";
        unset($_SESSION['success']);
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?= $item_id ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" placeholder="<?= htmlspecialchars($item['title']) ?>">
        </div>

        <div class="mb-3">
            <label>Artist</label>
            <input type="text" name="artist" class="form-control" placeholder="<?= htmlspecialchars($item['artist']) ?>">
        </div>

        <div class="mb-3">
            <label>Genre</label>
            <input type="text" name="genre" class="form-control" placeholder="<?= htmlspecialchars($item['genre']) ?>">
        </div>

        <div class="mb-3">
            <label>Price</label>
            <input type="text" name="price" class="form-control" placeholder="<?= htmlspecialchars($item['price']) ?>">
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="<?= htmlspecialchars($item['description']) ?>"></textarea>
        </div>

        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" placeholder="<?= htmlspecialchars($item['stock_qty']) ?>">
        </div>

        <div class="mb-3">
            <label>Current Images</label><br>
            <?php
            if (!empty($images)) {
                foreach ($images as $img) {
                    echo "<img src='../images/" . htmlspecialchars($img['image']) . "' width='100' height='100' 
                          style='border-radius:8px; margin:5px; object-fit:cover; border:1px solid #ccc'>";
                }
            } else {
                echo "<p class='text-muted'>No images uploaded yet.</p>";
            }
            ?>
        </div>

        <div class="mb-3">
            <label>Upload New Images (Optional, replaces old)</label>
            <input type="file" name="images[]" multiple class="form-control" accept="image/*">
        </div>

        <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('../includes/footer.php'); ?>