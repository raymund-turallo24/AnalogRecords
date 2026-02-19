<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

$account_id = $_SESSION['account_id'] ?? null;
$role       = $_SESSION['role'] ?? null;

if (!$account_id || $role !== 'customer') {
    header("Location: ../login.php");
    exit();
}

// Fetch current data
$sql = "SELECT first_name, last_name, contact, address, image FROM customer_details WHERE account_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_user_data = $result->fetch_assoc();
$stmt->close();

// Profile image
$has_image = !empty($existing_user_data['image']) && file_exists("../uploads/" . $existing_user_data['image']);
$image_path = $has_image 
    ? "../uploads/" . $existing_user_data['image'] 
    : "https://via.placeholder.com/150/2d3436/ffffff?text=No+Image";
$img_class = $has_image ? "profile-img-thumb rounded-circle" : "img-account-profile rounded-circle";
?>

<!-- Force white text + left-aligned labels -->
<style>
    .white-text-card * { color: white !important; }
    .white-text-card .card-header-crud {
        background: rgba(255,255,255,0.05);
        border-bottom: 1px solid rgba(255,255,255,0.15);
    }
    .white-text-card .alert-info {
        background: rgba(13,110,253,0.25) !important;
        border-color: rgba(13,110,253,0.5) !important;
    }
    .white-text-card .text-muted { color: rgba(255,255,255,0.7) !important; }

    /* Left-align all form labels */
    .white-text-card label {
        text-align: left !important;
        display: block;
        width: 100%;
    }
</style>

<div class="container-fluid site-content-wrapper">
    <h1 class="crud-title mt-4 mb-4">Update Profile</h1>
    
    <nav class="nav nav-borders profile-nav">
        <a class="nav-link active ms-0" href="#"><i class="fas fa-user-circle me-1"></i> Profile Setup</a>
    </nav>
    
    <hr class="mt-0 mb-4 form-divider">
    <?php include("../includes/alert.php"); ?>

    <div class="row">
        <!-- Profile Picture -->
        <div class="col-xl-4 mb-4">
            <div class="card card-crud h-100">
                <div class="card-header-crud">Profile Picture</div>
                <div class="card-body-crud text-center p-4">
                    <img id="currentProfileImage"
                         class="<?php echo $img_class; ?> mb-3"
                         src="<?php echo htmlspecialchars($image_path); ?>"
                         alt="Profile Picture"
                         style="width:150px; height:150px; object-fit:cover; border:3px solid #555;">
                    <div class="small text-muted mt-2">
                        JPG/PNG • Max 5MB
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card - White Text + Left-Aligned Labels -->
        <div class="col-xl-8 mb-4">
            <div class="card card-crud h-100 white-text-card">
                <div class="card-header-crud">Account Details</div>
                <div class="card-body-crud p-4">

                    <div class="alert alert-info small mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>All fields are optional.</strong> Leave blank to keep current info.
                    </div>

                    <form action="store.php" method="POST" enctype="multipart/form-data" class="crud-form" novalidate>
                        <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($account_id); ?>">

                        <!-- Hidden original values -->
                        <input type="hidden" name="original_first_name" value="<?php echo htmlspecialchars($existing_user_data['first_name'] ?? ''); ?>">
                        <input type="hidden" name="original_last_name"  value="<?php echo htmlspecialchars($existing_user_data['last_name'] ?? ''); ?>">
                        <input type="hidden" name="original_contact"    value="<?php echo htmlspecialchars($existing_user_data['contact'] ?? ''); ?>">
                        <input type="hidden" name="original_address"    value="<?php echo htmlspecialchars($existing_user_data['address'] ?? ''); ?>">
                        <input type="hidden" name="original_image_name" value="<?php echo htmlspecialchars($existing_user_data['image'] ?? ''); ?>">

                        <div class="form-group mb-4">
                            <label class="small mb-1" for="profile_image">Upload New Profile Image (Optional)</label>
                            <input class="form-control custom-input" id="profile_image" type="file" name="image" accept="image/*">
                        </div>

                        <!-- First Name & Last Name -->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="first_name">First Name</label>
                                <input class="form-control custom-input" id="first_name" type="text"
                                       placeholder="Leave blank to keep current" 
                                       name="first_name" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="last_name">Last Name</label>
                                <input class="form-control custom-input" id="last_name" type="text"
                                       placeholder="Leave blank to keep current" 
                                       name="last_name" value="">
                            </div>
                        </div>

                        <!-- Address & Phone -->
                        <div class="row gx-3 mb-4">
                            <div class="col-md-6">
                                <label class="small mb-1" for="address">Address</label>
                                <input class="form-control custom-input" id="address" type="text"
                                       placeholder="Leave blank to keep current" 
                                       name="address" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="contact">Phone Number</label>
                                <input class="form-control custom-input" id="contact" type="tel"
                                       placeholder="Leave blank to keep current" 
                                       name="contact" value="">
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

<!-- Live Preview -->
<script>
document.getElementById('profile_image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert('Image must be less than 5MB');
        this.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(ev) {
        const img = document.getElementById('currentProfileImage');
        img.src = ev.target.result;
        img.classList.remove('img-account-profile');
        img.classList.add('profile-img-thumb');
    };
    reader.readAsDataURL(file);
});
</script>

<?php include("../includes/footer.php"); ?>