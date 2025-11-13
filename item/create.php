<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
?>

<body>
    <div class="container mt-4">
        <?php include('../includes/alert.php'); ?>

        <h3 class="mb-4">Add New Item</h3>

        <form method="POST" action="store.php" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="title">Title</label>
                <input type="text"
                    class="form-control"
                    id="title"
                    name="title"
                    placeholder="Enter record title"
                    required>
            </div>

            <div class="form-group mb-3">
                <label for="artist">Artist</label>
                <input type="text"
                    class="form-control"
                    id="artist"
                    name="artist"
                    placeholder="Enter artist name">
            </div>

            <div class="form-group mb-3">
                <label for="genre">Genre</label>
                <input type="text"
                    class="form-control"
                    id="genre"
                    name="genre"
                    placeholder="Enter genre">
            </div>

            <div class="form-group mb-3">
                <label for="price">Price (₱)</label>
                <input type="number"
                    step="0.01"
                    class="form-control"
                    id="price"
                    name="price"
                    placeholder="Enter price"
                    required>
            </div>

            <div class="form-group mb-3">
                <label for="description">Description</label>
                <textarea class="form-control"
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Enter item description"></textarea>
            </div>

            <div class="form-group mb-3">
                <label for="quantity">Quantity</label>
                <input type="number"
                    class="form-control"
                    id="quantity"
                    name="quantity"
                    placeholder="Enter quantity"
                    min="1"
                    required>
            </div>

           <div class="form-group mb-3">
    <label for="images">Upload Images</label>
    <input type="file"
           class="form-control"
           id="images"
           name="images[]"
           accept="image/*"
           multiple
           required>
    <small class="form-text text-muted">Upload JPG or PNG files (max 5MB each).</small>
</div>


            <button type="submit" name="submit" class="btn btn-primary">Add Item</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>