
<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");
?>

<body>
<div class="container mt-4">
    <h3>Item List</h3>
    <a href="store.php" class="btn btn-success mb-3">Add New Item</a>

    <?php
    // Display flash messages (success / error)
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'>{$_SESSION['success']}</div>";
        unset($_SESSION['success']); // remove after showing
    }
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }

    // Fetch all items
    $sql_items = "SELECT * FROM item ORDER BY item_id DESC";
    $result_items = mysqli_query($conn, $sql_items);

    if (mysqli_num_rows($result_items) > 0) {
        echo "<table class='table table-bordered table-striped align-middle'>";
        echo "<thead class='table-dark'>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Genre</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Images</th>
                    <th>Actions</th>
                </tr>
              </thead><tbody>";

        while ($row = mysqli_fetch_assoc($result_items)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['artist']) . "</td>";
            echo "<td>" . htmlspecialchars($row['genre']) . "</td>";
            echo "<td>₱" . number_format($row['price'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";

            // ✅ DISPLAY MULTIPLE IMAGES
            echo "<td>";
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
                        echo "<img src='" . $imgPath . "' width='80' height='80' class='me-1 mb-1' 
                               style='border-radius:8px; object-fit:cover; border:1px solid #ccc'>";
                    }
                }
            } else {
                echo "<span class='text-muted'>No Image</span>";
            }
            mysqli_stmt_close($stmt_images);
            echo "</td>";

            echo "<td>
                    <a href='update.php?id=" . $row['item_id'] . "' class='btn btn-sm btn-primary'>Edit</a>
                    <a href='delete.php?id=" . $row['item_id'] . "' class='btn btn-sm btn-danger' 
                       onclick='return confirm(\"Are you sure you want to delete this item?\")'>Delete</a>
                  </td>";

            echo "</tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-info'>No items found.</div>";
    }

    mysqli_close($conn);
    ?>
</div>
</body>
</html>
