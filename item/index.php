<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");
?>

<div class="container-fluid site-content-wrapper">

    <h1 class="crud-title mt-4 mb-4">Item Inventory Management</h1>
    
    <div class="card-crud mb-5">
        <div class="card-header-crud d-flex justify-content-between align-items-center">
            <h2 class="card-heading-crud">Products List</h2>
            <a href="create.php" class="btn login-btn">
                <i class="fas fa-plus me-1"></i> Add New Item
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class='alert alert-success alert-crud'>
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class='alert alert-danger alert-crud'>
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <?php
            // get all the items with stock
            $sql_items = "SELECT i.*, IFNULL(s.quantity, 0) AS quantity
                          FROM item i
                          LEFT JOIN stock s ON i.item_id = s.item_id
                          ORDER BY i.item_id DESC";
            $result_items = mysqli_query($conn, $sql_items);

            if (mysqli_num_rows($result_items) > 0) {
            ?>
                <table class='data-table'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title/Artist</th>
                            <th>Genre</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Images</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_items)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['item_id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                    <span class="text-muted small-text" style="color: #828282ff !important;">by <?php echo htmlspecialchars($row['artist']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['genre']); ?></td>
                                <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <span class="badge badge-status status-<?php echo ($row['quantity'] > 0 ? 'active' : 'inactive'); ?>">
                                        <?php echo intval($row['quantity']); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="image-gallery-list">
                                    <?php
                                    $item_id = $row['item_id'];
                                    $sql_images = "SELECT image FROM item_images WHERE item_id = ?";
                                    $stmt_images = mysqli_prepare($conn, $sql_images);
                                    mysqli_stmt_bind_param($stmt_images, "i", $item_id);
                                    mysqli_stmt_execute($stmt_images);
                                    $result_images = mysqli_stmt_get_result($stmt_images);

                                    if (mysqli_num_rows($result_images) > 0) {
                                        while ($img_row = mysqli_fetch_assoc($result_images)) {
                                            $imgPath = "../images/" . htmlspecialchars($img_row['image']);
                                            if (file_exists($imgPath)) { 
                                                echo "<img src='" . $imgPath . "' alt='Item Image' class='table-thumb'>";
                                            }
                                        }
                                    } else {
                                        echo "<span class='text-muted small-text'>No Image</span>";
                                    }
                                    mysqli_stmt_close($stmt_images);
                                    ?>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <a href='update.php?id=<?php echo $row['item_id']; ?>' class='action-link edit-link'><i class="fas fa-edit"></i> Edit</a>
                                    <span class="text-muted">|</span>
                                    <a href='delete.php?id=<?php echo $row['item_id']; ?>' class='action-link delete-link' 
                                        onclick='return confirm("Are you sure you want to delete this item and ALL associated data?")'><i class="fas fa-trash-alt"></i> Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php 
            } else {
                echo "<div class='alert alert-info alert-crud'>No items found in the inventory.</div>";
            }
            ?>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
include("../includes/footer.php"); 
?>