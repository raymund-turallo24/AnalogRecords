<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// admin access only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You do not have permission to access the item creation page.";
    header("Location: ../index.php");
    exit();
}
?>

<div class="site-content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <h1 class="crud-title mt-4 mb-4"><i class="fas fa-plus-circle me-2"></i> Add New Item</h1>

            <?php include('../includes/alert.php'); ?>

            <div class="card card-crud mb-5">
                <div class="card-header-crud" style="color: white;">Item Details</div>
                
                <div class="card-body-crud p-4">
                    
                    <form method="POST" action="store.php" enctype="multipart/form-data" class="crud-form">
                        
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="title" class="form-group-label">Title <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control custom-input"
                                    id="title"
                                    name="title"
                                    placeholder="Enter record title"
                                    required>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="artist" class="form-group-label">Artist</label>
                                <input type="text"
                                    class="form-control custom-input"
                                    id="artist"
                                    name="artist"
                                    placeholder="Enter artist name">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="genre" class="form-group-label">Genre</label>
                                <input type="text"
                                    class="form-control custom-input"
                                    id="genre"
                                    name="genre"
                                    placeholder="Enter genre">
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
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="quantity" class="form-group-label">Initial Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number"
                                    class="form-control custom-input"
                                    id="quantity"
                                    name="quantity"
                                    placeholder="Enter quantity"
                                    min="1"
                                    required>
                            </div>
                            
                            <div class="col-md-6 form-group mb-3">
                                <label for="images" class="form-group-label">Upload Item Images <span class="text-danger">*</span></label>
                                <input type="file"
                                    class="form-control custom-file-input"
                                    id="images"
                                    name="images[]"
                                    accept="image/jpeg,image/png,image/webp"
                                    multiple
                                    required>
                                <small class="form-text text-muted">Upload one or more JPG, PNG, or WEBP files.</small>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="description" class="form-group-label">Description</label>
                            <textarea class="form-control custom-textarea"
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Enter item description"></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end pt-3 border-top border-secondary">
                            <a href="index.php" class="btn btn-secondary-theme me-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" name="submit" class="btn btn-success-theme">
    <i class="fas fa-save me-1"></i> Add New Item
</button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>