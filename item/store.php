
<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

// Check if form was submitted
if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    $genre = trim($_POST['genre']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $quantity = trim($_POST['quantity']);

    // Validate required fields
    if (empty($title) || empty($price) || empty($quantity)) {
        echo "<div class='alert alert-danger'>Please fill out all required fields.</div>";
    } else {
        // Insert item into main table
        $sql = "INSERT INTO item (title, artist, genre, price, description, quantity)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssdsd", $title, $artist, $genre, $price, $description, $quantity);

        if (mysqli_stmt_execute($stmt)) {
            $item_id = mysqli_insert_id($conn); // get the new item's ID

            // ✅ MULTIPLE IMAGE UPLOAD SECTION
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $uploadDir = '../images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] == 0) {
                        $fileName = time() . '_' . basename($_FILES['images']['name'][$key]);
                        $target = $uploadDir . $fileName;
                        $fileType = mime_content_type($tmp_name);

                        // Only allow certain file types
                        if (in_array($fileType, ["image/jpeg", "image/jpg", "image/png"])) {
                            if (move_uploaded_file($tmp_name, $target)) {
                                // Insert image path into item_images table
                                $sql_img = "INSERT INTO item_images (item_id, image) VALUES (?, ?)";
                                $stmt_img = mysqli_prepare($conn, $sql_img);
                                mysqli_stmt_bind_param($stmt_img, "is", $item_id, $fileName);
                                mysqli_stmt_execute($stmt_img);
                                mysqli_stmt_close($stmt_img);
                            }
                        }
                    }
                }
            }

            echo "<div class='alert alert-success'>Item and images added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error saving item: " . mysqli_error($conn) . "</div>";
        }

        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<body>
    <div class="container mt-4">
        <h3>Add New Item</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Artist</label>
                <input type="text" name="artist" class="form-control">
            </div>

            <div class="mb-3">
                <label>Genre</label>
                <input type="text" name="genre" class="form-control">
            </div>

            <div class="mb-3">
                <label>Price</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Upload Images</label>
                <input type="file" name="images[]" multiple class="form-control" accept="image/*">
                <small class="text-muted">You can upload multiple images (JPEG, JPG, PNG).</small>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">Add Item</button>
        </form>
    </div>
</body>
</html>
