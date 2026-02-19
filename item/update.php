<?php
session_start();
include('../includes/config.php');

// admin role to access this page only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You do not have permission to access the item update page.";
    header("Location: ../index.php");
    exit();
}

$item_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$item_id) {
    $_SESSION['error'] = "Invalid item ID provided.";
    header("Location: index.php");
    exit();
}

// item update logic
if (isset($_POST['submit'])) {
    
    $item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    $genre = trim($_POST['genre']);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $description = trim($_POST['description']);
    $delete_images = $_POST['delete_images'] ?? []; // Array of image_ids to delete

    $errors = [];

    if (empty($title)) $errors[] = "Title is required.";
    if ($price === false || $price <= 0) $errors[] = "Price must be a valid positive number.";
    if ($quantity === false || $quantity < 0) $errors[] = "Quantity must be a valid non-negative number.";
    if (!$item_id) $errors[] = "Invalid item ID submitted for update.";

    if (empty($errors)) {
        
        // table for item update
        $sql_update_item = "UPDATE item SET title=?, artist=?, genre=?, price=?, description=? WHERE item_id=?";
        $stmt_update_item = mysqli_prepare($conn, $sql_update_item);
        mysqli_stmt_bind_param($stmt_update_item, "sssdsi", $title, $artist, $genre, $price, $description, $item_id);
        
        if (!mysqli_stmt_execute($stmt_update_item)) {
             $errors[] = "Database error updating item details: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_update_item);
        
        // update stock table
        $sql_check_stock = "SELECT item_id FROM stock WHERE item_id = ?";
        $stmt_check_stock = mysqli_prepare($conn, $sql_check_stock);
        mysqli_stmt_bind_param($stmt_check_stock, "i", $item_id);
        mysqli_stmt_execute($stmt_check_stock);
        $result_check_stock = mysqli_stmt_get_result($stmt_check_stock);

        if (mysqli_fetch_assoc($result_check_stock)) {
            // Record exists, UPDATE
            $sql_stock = "UPDATE stock SET quantity = ? WHERE item_id = ?";
        } else {
            // Record does not exist, INSERT
            $sql_stock = "INSERT INTO stock (quantity, item_id) VALUES (?, ?)";
        }
        mysqli_stmt_close($stmt_check_stock);
        
        $stmt_stock = mysqli_prepare($conn, $sql_stock);
        mysqli_stmt_bind_param($stmt_stock, "ii", $quantity, $item_id);
        if (!mysqli_stmt_execute($stmt_stock)) {
            $errors[] = "Database error updating stock: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_stock);

        // handle image deletion
        if (!empty($delete_images)) {
            $placeholders = implode(',', array_fill(0, count($delete_images), '?'));
            $types = str_repeat('i', count($delete_images));
            
            $sql_get_files = "SELECT image FROM item_images WHERE image_id IN ($placeholders)";
            $stmt_get_files = mysqli_prepare($conn, $sql_get_files);
            mysqli_stmt_bind_param($stmt_get_files, $types, ...$delete_images);
            mysqli_stmt_execute($stmt_get_files);
            $result_files = mysqli_stmt_get_result($stmt_get_files);
            $files_to_delete = mysqli_fetch_all($result_files, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_get_files);

            // delete records sa database
            $sql_delete = "DELETE FROM item_images WHERE image_id IN ($placeholders)";
            $stmt_delete = mysqli_prepare($conn, $sql_delete);
            mysqli_stmt_bind_param($stmt_delete, $types, ...$delete_images);
            if (!mysqli_stmt_execute($stmt_delete)) {
                 $errors[] = "Database error deleting images: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_delete);
            
            foreach ($files_to_delete as $file) {
                $filepath = "../images/" . $file['image'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
        }
        
        if (!empty($_FILES['new_images']['name'][0])) {
            $upload_dir = "../images/";
            
            foreach ($_FILES['new_images']['name'] as $key => $name) {
                $tmp_name = $_FILES['new_images']['tmp_name'][$key];
                $error = $_FILES['new_images']['error'][$key];
                $type = $_FILES['new_images']['type'][$key];
                
                if ($error === UPLOAD_ERR_OK && in_array($type, ['image/jpeg', 'image/png', 'image/webp'])) {
                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $new_file_name = uniqid('img_', true) . '.' . $file_ext;
                    $target_file = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $sql_insert_image = "INSERT INTO item_images (item_id, image) VALUES (?, ?)";
                        $stmt_insert_image = mysqli_prepare($conn, $sql_insert_image);
                        mysqli_stmt_bind_param($stmt_insert_image, "is", $item_id, $new_file_name);
                        
                        if (!mysqli_stmt_execute($stmt_insert_image)) {
                            $errors[] = "Database error saving new image record: " . mysqli_error($conn);
                            unlink($target_file);
                        }
                        mysqli_stmt_close($stmt_insert_image);
                    } else {
                        $errors[] = "Failed to move uploaded file: " . htmlspecialchars($name);
                    }
                } elseif ($error !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = "Upload error for file " . htmlspecialchars($name) . ": Code " . $error;
                }
            }
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = "Failed to update item:<br>" . implode("<br>", $errors);
        header("Location: update.php?id=" . $item_id);
        exit();
    } else {
        $_SESSION['success'] = "Item **" . htmlspecialchars($title) . "** updated successfully!";
        header("Location: index.php"); // Redirect back to the item list page
        exit();
    }
}


// get item details
$sql_item = "SELECT i.*, s.quantity 
             FROM item i
             LEFT JOIN stock s ON i.item_id = s.item_id
             WHERE i.item_id = ?";

$stmt_item = mysqli_prepare($conn, $sql_item);
mysqli_stmt_bind_param($stmt_item, "i", $item_id);
mysqli_stmt_execute($stmt_item);
$result_item = mysqli_stmt_get_result($stmt_item);
$item = mysqli_fetch_assoc($result_item);
mysqli_stmt_close($stmt_item);

if (!$item) {
    $_SESSION['error'] = "Item not found.";
    header("Location: index.php");
    exit();
}

// 3. get existing image
$sql_images = "SELECT image_id, image FROM item_images WHERE item_id = ?";
$stmt_images = mysqli_prepare($conn, $sql_images);
mysqli_stmt_bind_param($stmt_images, "i", $item_id);
mysqli_stmt_execute($stmt_images);
$result_images = mysqli_stmt_get_result($stmt_images);
$images = mysqli_fetch_all($result_images, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_images);

$quantity = $item['quantity'] ?? 0; 

include('../includes/header.php');
?>

<div class="site-content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <h1 class="crud-title mt-4 mb-4"><i class="fas fa-edit me-2"></i> Edit Item: <?php echo htmlspecialchars($item['title']); ?></h1>

            <?php include('../includes/alert.php'); ?>

            <div class="card card-crud mb-5">
                <div class="card-header-crud">Update Details</div>
                
                <div class="card-body-crud p-4">
                    
                    <form method="POST" action="update.php?id=<?php echo htmlspecialchars($item['item_id']); ?>" enctype="multipart/form-data" class="crud-form">
                        
                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="title" class="form-group-label">Title <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control custom-input"
                                    id="title"
                                    name="title"
                                    placeholder="Enter record title"
                                    value="<?php echo htmlspecialchars($item['title']); ?>"
                                    required>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="artist" class="form-group-label">Artist</label>
                                <input type="text"
                                    class="form-control custom-input"
                                    id="artist"
                                    name="artist"
                                    placeholder="Enter artist name"
                                    value="<?php echo htmlspecialchars($item['artist']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="genre" class="form-group-label">Genre</label>
                                <input type="text"
                                    class="form-control custom-input"
                                    id="genre"
                                    name="genre"
                                    placeholder="Enter genre"
                                    value="<?php echo htmlspecialchars($item['genre']); ?>">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="price" class="form-group-label">Price (₱) <span class="text-danger">*</span></label>
                                <input type="number"
                                    step="0.01"
                                    class="form-control custom-input"
                                    id="price"
                                    name="price"
                                    placeholder="Enter price"
                                    min="0.01"
                                    value="<?php echo htmlspecialchars($item['price']); ?>"
                                    required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="quantity" class="form-group-label">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number"
                                    class="form-control custom-input"
                                    id="quantity"
                                    name="quantity"
                                    placeholder="Enter quantity"
                                    min="0"
                                    value="<?php echo htmlspecialchars($quantity); ?>"
                                    required>
                            </div>
                            
                            <div class="col-md-6 form-group mb-3">
                                <label for="new_images" class="form-group-label">Upload **New** Item Images (Optional)</label>
                                <input type="file"
                                    class="form-control custom-file-input"
                                    id="new_images"
                                    name="new_images[]"
                                    accept="image/jpeg,image/png,image/webp"
                                    multiple>
                                <small class="form-text text-muted">Files will be **added** to existing images.</small>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="description" class="form-group-label">Description</label>
                            <textarea class="form-control custom-textarea"
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Enter item description"><?php echo htmlspecialchars($item['description']); ?></textarea>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-group-label">Existing Images</label>
                            <?php if (!empty($images)): ?>
                                <div class="existing-images-container">
                                    <?php foreach ($images as $img): ?>
                                        <div class="existing-image-wrapper">
                                            <?php 
                                            $imgPath = "../images/" . htmlspecialchars($img['image']);
                                            if (file_exists($imgPath)):
                                            ?>
                                                <img src="<?php echo $imgPath; ?>" alt="Existing Item Image" class="img-thumbnail me-2" style="max-width: 100px; height: auto;">
                                            <?php else: ?>
                                                <span class="text-danger">File missing: <?php echo htmlspecialchars($img['image']); ?></span>
                                            <?php endif; ?>
                                            
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" 
                                                            name="delete_images[]" 
                                                            value="<?php echo htmlspecialchars($img['image_id']); ?>" 
                                                            id="delete_img_<?php echo $img['image_id']; ?>">
                                                <label class="form-check-label text-danger small-text" for="delete_img_<?php echo $img['image_id']; ?>">
                                                    Delete (<?php echo htmlspecialchars($img['image']); ?>)
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="form-text text-muted d-block mt-2">Check the box(es) to mark image(s) for deletion.</small>
                            <?php else: ?>
                                <p class="text-secondary">No images currently associated with this item.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-end pt-3 border-top border-secondary">
                            <a href="index.php" class="btn btn-secondary-theme me-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" name="submit" class="btn btn-primary-theme">
                                <i class="fas fa-sync-alt me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php 
mysqli_close($conn);
include('../includes/footer.php'); 
?>