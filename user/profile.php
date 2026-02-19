<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

$account_id = $_SESSION['account_id'] ?? null;

// Security check: If not logged in, redirect
if (!$account_id) {
    header("Location: ../login.php");
    exit();
}

// Fetch existing user info
$sql = "SELECT first_name, last_name, contact, address, image 
        FROM customer_details 
        WHERE account_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['error'] = "User details not found. Please complete your profile.";
    header("Location: profileUpdate.php"); // Redirect to update/setup page
    exit();
}

// Fallback logic for image path
$image_path = !empty($user['image']) && file_exists("../uploads/" . $user['image'])
              ? "../uploads/" . $user['image']
              : "https://via.placeholder.com/150/343a40/ffffff?text=User"; // Dark theme placeholder
?>

<div class="container-fluid site-content-wrapper">

    <h1 class="crud-title mt-4 mb-4">My Account Profile</h1>
    
    <nav class="nav nav-borders profile-nav">
        <a class="nav-link active ms-0" href="#"><i class="fas fa-user-circle me-1"></i> Profile Overview</a>
    </nav>
    
    <hr class="mt-0 mb-4 form-divider">

    <?php include("../includes/alert.php"); ?>
    
    <div class="row">
        <div class="col-xl-4 mb-4">
            <div class="card card-crud h-100">
                <div class="card-header-crud">Profile Picture</div>
                <div class="card-body-crud text-center p-4">
                    
                    <img class="img-account-profile profile-img-thumb rounded-circle mb-3"
                         src="<?php echo htmlspecialchars($image_path); ?>"
                         alt="Profile Picture">

                    <h4 class="text-white mb-2">
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                    </h4>
                    
                    <a href="profileUpdate.php" class="btn login-btn mt-3">
                        <i class="fas fa-edit me-1"></i> Edit Profile Details
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-8 mb-4">
            <div class="card card-crud h-100">
                <div class="card-header-crud">Personal Information</div>
                <div class="card-body-crud p-4">
                    
                    <div class="row info-display-grid">
                        
                        <div class="col-md-6 mb-3">
                            <strong class="text-secondary small">First Name:</strong>
                            <p class="text-white fs-5"><?= htmlspecialchars($user['first_name']) ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-secondary small">Last Name:</strong>
                            <p class="text-white fs-5"><?= htmlspecialchars($user['last_name']) ?></p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong class="text-secondary small">Contact Number:</strong>
                            <p class="text-white fs-5"><i class="fas fa-phone me-2 text-success"></i><?= htmlspecialchars($user['contact']) ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-secondary small">Account ID:</strong>
                            <p class="text-white fs-5"><?= htmlspecialchars($account_id) ?></p>
                        </div>

                        <div class="col-12 mb-3">
                            <strong class="text-secondary small">Shipping Address:</strong>
                            <div class="p-3 mt-1 profile-address-box">
                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                <span class="text-white"><?= htmlspecialchars($user['address']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>