<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');
include('./includes/alert.php');

//search and sort input
$search_term = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : '';

$order_by = "i.item_id DESC"; 

if ($sort_by === 'artist_asc') {
    $order_by = "i.artist ASC";
} elseif ($sort_by === 'genre_asc') {
    $order_by = "i.genre ASC";
}

$sql = "
SELECT 
    i.item_id, 
    i.title, 
    i.artist, 
    i.genre, 
    i.price,
    s.quantity,
    (
        SELECT image 
        FROM item_images 
        WHERE item_id = i.item_id 
        ORDER BY image_id ASC 
        LIMIT 1
    ) AS main_image
FROM 
    item i
JOIN 
    stock s ON i.item_id = s.item_id 
WHERE 
    s.quantity > 0 
";

// search condition
if (!empty($search_term)) {
    $sql .= "
    AND (
        i.title LIKE '%{$search_term}%' OR
        i.artist LIKE '%{$search_term}%'
    )
    ";
}

$sql .= "
ORDER BY 
    {$order_by}
";


$results = mysqli_query($conn, $sql);

if (!$results) {
    echo '<div class="alert alert-danger container mt-5">Database error: ' . mysqli_error($conn) . '</div>';
    exit;
}
?>

<div class="container-fluid site-content-wrapper"> 

    <div class="row mb-4 search-sort-bar justify-content-end">
        
        <div class="col-12 col-md-6 d-none d-md-block">
            </div>

        <div class="col-12 col-md-6 ms-auto text-end">
            
            <form action="index.php" method="GET" class="d-flex search-form justify-content-end mb-2">
                
                <?php if (!empty($sort_by)): ?>
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
                <?php endif; ?>
                
                <input type="text" name="search" class="form-control custom-input search-input me-2" 
                           placeholder="Search by Album Title or Artist..." 
                           value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" class="btn login-btn search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <form action="index.php" method="GET" id="sort-form">
                <?php if (!empty($search_term)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                <?php endif; ?>
                
                <div class="d-flex align-items-center justify-content-end">
                    <label for="sort" class="me-2 text-secondary sort-label small fw-bold">Sort By:</label>
                    <select name="sort" id="sort" class="form-select custom-input sort-dropdown" onchange="this.form.submit()" style="max-width: 200px;">
                        <option value="latest" <?php if ($sort_by === 'latest' || empty($sort_by)) echo 'selected'; ?>>Latest Added</option>
                        <option value="artist_asc" <?php if ($sort_by === 'artist_asc') echo 'selected'; ?>>Artist (A-Z)</option>
                        <option value="genre_asc" <?php if ($sort_by === 'genre_asc') echo 'selected'; ?>>Genre (A-Z)</option>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <hr class="form-divider mb-4">

    <div class="section-header-row mb-4">
        <h2 class="section-title text-white">
            <i class="fas fa-record-vinyl me-2 text-danger"></i>
            <?php 
            if (!empty($search_term)) {
                echo 'Search Results for "' . htmlspecialchars($search_term) . '"';
            } else {
                echo 'Top Picks for You';
            }
            ?>
        </h2>
        <p class="section-subtitle text-secondary">
            <?php 
            if (empty($search_term) && empty($sort_by)) {
                echo 'Explore our newest additions and best sellers.';
            } elseif (!empty($search_term)) {
                echo mysqli_num_rows($results) . ' items found matching your criteria.';
            } else {
                echo 'Currently displaying items sorted by ' . htmlspecialchars(str_replace('_', ' ', $sort_by));
            }
            ?>
        </p>
    </div>

    <div class="product-carousel"> 
        <?php
        if (mysqli_num_rows($results) > 0) {
            while ($row = mysqli_fetch_assoc($results)) {
                
                $image_path = "./images/no-image.png"; 
                
                if (!empty($row['main_image'])) {
                    $image_path = "./images/" . htmlspecialchars($row['main_image']);
                }
                
                $detail_url = "item/show.php?id=" . intval($row['item_id']);
                
                ?>
                
                <a href="<?php echo $detail_url; ?>" class="music-card-link">
                    <div class="music-card card-crud"> <div class="card-image-wrapper">
                            <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" class="card-image">
                            
                            <div class="card-overlay-text">
                                <p class="card-title"><?php echo htmlspecialchars($row['title']); ?></p>
                                <p class="card-artist">
                                    <?php echo htmlspecialchars($row['artist']) . ' (' . htmlspecialchars($row['genre']) . ')'; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="text-center p-2">
                            <p class="text-info fw-bold mb-0">$<?php echo number_format($row['price'], 2); ?></p>
                        </div>
                    </div>
                </a>

                <?php
            }
        } else {
            ?>
            <div class="col-12 mt-4">
                <div class="card-crud text-center p-5">
                    <p class="text-light mb-3">No products match your search or filter criteria.</p>
                    <a href="index.php" class="btn login-btn">Reset Filters</a>
                </div>
            </div>
            <?php
        }
        ?>
    </div> 
</div>
<?php
 include('./includes/footer.php');
?>