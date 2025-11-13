
<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

$account_id = $_SESSION['account_id'] ?? null;

if (!$account_id) {
    header("Location: ../login.php");
    exit();
}

// Fetch existing user info including uploaded image
$sql = "SELECT first_name, last_name, contact, address, image 
        FROM customer_details 
        WHERE account_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();
$image_path = !empty($user['image']) && file_exists("../uploads/" . $user['image'])
              ? "../uploads/" . $user['image']
              : "https://via.placeholder.com/150?text=No+Image"; // fallback placeholder
?>

<div class="container-xl px-4 mt-4">
    <?php include("../includes/alert.php"); ?>
    <nav class="nav nav-borders">
        <a class="nav-link active ms-0" href="#">Profile Setup</a>
    </nav>
    <hr class="mt-0 mb-4">
    <div class="row">
        <div class="col-xl-4">
            <div class="card mb-4 mb-xl-0">
                <div class="card-header">Profile Picture</div>
                <div class="card-body text-center">
                    <!-- Display existing or placeholder image -->
                    <img class="img-account-profile rounded-circle mb-2"
                         src="<?php echo htmlspecialchars($image_path); ?>"
                         alt="Profile Picture"
                         width="150" height="150">

                    <div class="small font-italic text-muted mb-3">
                        JPG or PNG no larger than 5 MB
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Account Details -->
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <form action="store.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="account_id" value="<?php echo $account_id; ?>">

                        <!-- Upload image field -->
                        <div class="mb-3">
                            <label class="small mb-1">Profile Image</label>
                            <input class="form-control" type="file" name="image" accept="image/*">
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">First name</label>
                                <input class="form-control" type="text" placeholder="Enter your first name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Last name</label>
                                <input class="form-control" type="text" placeholder="Enter your last name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Address</label>
                                <input class="form-control" type="text" placeholder="Enter your address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Phone number</label>
                                <input class="form-control" type="tel" placeholder="Enter your phone number" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" required>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" name="submit">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
