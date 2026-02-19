<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

$account_id = $_SESSION['account_id'] ?? null;
$role       = $_SESSION['role'] ?? null;

// Security check 1: Must be logged in
if (!$account_id) {
    header("Location: ../login.php");
    exit();
}

// Security check 2: Only customers can access this page
if ($role !== 'customer') {
    $_SESSION['error'] = "Access denied.";
    header("Location: ../index.php");
    exit();
}

// --- FETCH EXISTING DATA ---
$sql  = "SELECT first_name, last_name, contact, address, image 
         FROM customer_details 
         WHERE account_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_user_data = $result->fetch_assoc();
$stmt->close();

// Determine current image path and which CSS class to use
$has_image = !empty($existing_user_data['image']) && 
             file_exists("../uploads/" . $existing_user_data['image']);

$image_path = $has_image 
    ? "../uploads/" . $existing_user_data['image']
    : "https://via.placeholder.com/150/2d3436/ffffff?text=No+Image";

$img_class = $has_image 
    ? "profile-img-thumb rounded-circle"   // clean avatar when image exists
    : "img-account-profile rounded-circle"; // fallback placeholder with text
?>

<div class="container-fluid site-content-wrapper">
    <h1 class="crud-title mt-4 mb-4">Create Profile</h1>
    
    <nav class="nav nav-borders profile-nav">
        <a class="nav-link active ms-0" href="#"><i class="fas fa-user-circle me-1"></i> Profile Setup</a>
    </nav>
    
    <hr class="mt-0 mb-4 form-divider">

    <?php include("../includes/alert.php"); ?>
    
    <div class="row">
        <!-- Profile Picture Column -->
        <div class="col-xl-4 mb-4">
            <div class="card card-crud h-100">
                <div class="card-header-crud">Profile Picture</div>
                <div class="card-body-crud text-center p-4">
                    <img id="currentProfileImage"
                         class="<?php echo $img_class; ?> mb-3"
                         src="<?php echo htmlspecialchars($image_path); ?>"
                         alt="Profile Picture"
                         style="width:150px; height:150px; object-fit:cover;">

                    <div class="small font-italic text-muted mb-3">
                        <i class="fas fa-file-image me-1"></i> JPG or PNG, max 5MB
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Column -->
        <div class="col-xl-8 mb-4">
            <div class <"card card-crud h-100">
                <div class="card-header-crud">Account Details</div>
                <div class="card-body-crud p-4">
                    <form action="store.php" method="POST" enctype="multipart/form-data" class="crud-form">
                        <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($account_id); ?>">

                        <!-- Original values (for comparison / update logic in store.php) -->
                        <input type="hidden" name="original_first_name" value="<?php echo htmlspecialchars($existing_user_data['first_name'] ?? ''); ?>">
                        <input type="hidden" name="original_last_name"  value="<?php echo htmlspecialchars($existing_user_data['last_name'] ?? ''); ?>">
                        <input type="hidden" name="original_contact"    value="<?php echo htmlspecialchars($existing_user_data['contact'] ?? ''); ?>">
                        <input type="hidden" name="original_address"    value="<?php echo htmlspecialchars($existing_user_data['address'] ?? ''); ?>">
                        <input type="hidden" name="original_image_name" value="<?php echo htmlspecialchars($existing_user_data['image'] ?? ''); ?>">

                        <div class="form-group mb-4">
                            <label class="small mb-1" for="profile_image">Upload New Profile Image (Optional)</label>
                            <input class="form-control custom-input" id="profile_image" type="file" name="image" accept="image/*">
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="first_name">First Name</label>
                                <input class="form-control custom-input" id="first_name" type="text"
                                       placeholder="Enter your first name" name="first_name"
                                       value="<?php echo htmlspecialchars($existing_user_data['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="last_name">Last Name</label>
                                <input class="form-control custom-input" id="last_name" type="text"
                                       placeholder="Enter your last name" name="last_name"
                                       value="<?php echo htmlspecialchars($existing_user_data['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row gx-3 mb-4">
                            <div class="col-md-6">
                                <label class="small mb-1" for="address">Address</label>
                                <input class="form-control custom-input" id="address" type="text"
                                       placeholder="Enter your address" name="address"
                                       value="<?php echo htmlspecialchars($existing_user_data['address'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="contact">Phone Number</label>
                                <input class="form-control custom-input" id="contact" type="tel"
                                       placeholder="Enter your phone number" name="contact"
                                       value="<?php echo htmlspecialchars($existing_user_data['contact'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <button class="btn login-btn" type="submit" name="submit">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live preview when user selects a new image -->
<script>
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Basic size validation (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be less than 5MB');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(ev) {
            const img = document.getElementById('currentProfileImage');
            img.src = ev.target.result;
            // Ensure clean styling (remove placeholder class if present)
            img.classList.remove('img-account-profile');
            img.classList.add('profile-img-thumb');
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include("../includes/footer.php"); ?>