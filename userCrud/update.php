<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

$update_success = false;
$no_change = false;
$error = '';

// fetch user data
function fetch_user_data($conn, $account_id) {
    if (!$account_id) return null;
    $sql = "SELECT a.*, c.first_name, c.last_name, c.contact, c.address, c.image 
            FROM accounts a 
            LEFT JOIN customer_details c ON a.account_id = c.account_id
            WHERE a.account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

if (isset($_POST['update'])) {
    $account_id = filter_input(INPUT_POST, 'account_id', FILTER_VALIDATE_INT);
    if (!$account_id) {
        $error = "Invalid account ID provided.";
    } else {
        // fetch old data to compare
        $oldData = fetch_user_data($conn, $account_id);
        if (!$oldData) {
            $error = "User not found for update.";
        } else {
            // Personal Details 
            $first_name = trim(!empty($_POST['first_name']) ? $_POST['first_name'] : ($oldData['first_name'] ?? ''));
            $last_name = trim(!empty($_POST['last_name']) ? $_POST['last_name'] : ($oldData['last_name'] ?? ''));
            $contact = trim(!empty($_POST['contact']) ? $_POST['contact'] : ($oldData['contact'] ?? ''));
            $address = trim(!empty($_POST['address']) ? $_POST['address'] : ($oldData['address'] ?? ''));
            
            // Account Settings 
            $role = trim(!empty($_POST['role']) ? $_POST['role'] : ($oldData['role'] ?? 'customer'));
            $status = trim(!empty($_POST['status']) ? $_POST['status'] : ($oldData['status'] ?? 'inactive'));
            
            $image = $oldData['image'];

            $upload_dir = "../uploads/";
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $fileExt = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png'];

                if (in_array($fileExt, $allowedTypes)) {
                    $img_name = time() . "_" . uniqid() . "." . $fileExt;
                    $tmp_name = $_FILES['image']['tmp_name'];
                    
                    if (!is_dir($upload_dir)) {
                        @mkdir($upload_dir, 0777, true);
                    }
                    
                    if (move_uploaded_file($tmp_name, $upload_dir . $img_name)) {
                        $image = $img_name;
                        // delete old image file
                        if (!empty($oldData['image']) && file_exists($upload_dir . $oldData['image'])) {
                            @unlink($upload_dir . $oldData['image']);
                        }
                    } else {
                        $error = "Failed to move uploaded image.";
                    }
                } else {
                    $error = "Only JPG, JPEG, and PNG files are allowed.";
                }
            }
        }
    }


    // check if nagbago 
    if (!$error) {
        if (
            // Compare the determined new value (which might be oldData) against the old data
            $first_name == $oldData['first_name'] && $last_name == $oldData['last_name'] &&
            $contact == $oldData['contact'] && $address == $oldData['address'] &&
            $role == $oldData['role'] && $status == $oldData['status'] &&
            $image == $oldData['image']
        ) {
            $no_change = true;
        } else {
            mysqli_begin_transaction($conn);
            try {

                $stmt = $conn->prepare("UPDATE accounts SET role=?, status=? WHERE account_id=?");
                $stmt->bind_param("ssi", $role, $status, $account_id);
                if (!$stmt->execute()) {
                    throw new Exception("Account update failed: " . $stmt->error);
                }
                $stmt->close();

                $sql_check = "SELECT account_id FROM customer_details WHERE account_id = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("i", $account_id);
                $stmt_check->execute();
                $exists = $stmt_check->get_result()->num_rows > 0;
                $stmt_check->close();

                if ($exists) {
                    $sql_details = "UPDATE customer_details SET first_name=?, last_name=?, contact=?, address=?, image=? WHERE account_id=?";
                } else {
                    $sql_details = "INSERT INTO customer_details (first_name, last_name, contact, address, image, account_id) VALUES (?, ?, ?, ?, ?, ?)";
                }

                $stmt2 = $conn->prepare($sql_details);
                $stmt2->bind_param("sssssi", $first_name, $last_name, $contact, $address, $image, $account_id);
                
                if (!$stmt2->execute()) {
                    throw new Exception("Details update/insert failed: " . $stmt2->error);
                }
                $stmt2->close();
                
                mysqli_commit($conn);
                $update_success = true;
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
    // re-fetch the data after update
    if ($account_id) {
        $_GET['id'] = $account_id;
    }
}

$id = $_GET['id'] ?? ($_POST['account_id'] ?? 0);
$user = fetch_user_data($conn, $id);

if (!$user) {
    $_SESSION['error'] = "Error: Target user not found in the database.";
    header("Location: index.php");
    exit();
}

$display_image_path = (!empty($user['image']) && file_exists("../uploads/" . $user['image']))
                         ? "../uploads/" . htmlspecialchars($user['image'])
                         : "https://via.placeholder.com/100/343a40/ffffff?text=User";
?>

<div class="container-fluid site-content-wrapper">

    <div class="card-crud my-5 mx-auto" style="max-width: 650px;">
        <div class="card-header-crud d-flex justify-content-between align-items-center">
            <h2 class="card-heading-crud">Update User: <?= htmlspecialchars($user['email'] ?? 'N/A') ?></h2>
            <a href="index.php" class="btn back-btn"><i class="fas fa-chevron-left me-1"></i> Back to User List</a>
        </div>
        
        <div class="card-body-crud p-4">
            
            <?php if (!empty($error)): ?>
                <div class='alert alert-danger alert-crud'><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($update_success): ?>
                <div class='alert alert-success alert-crud'><i class="fas fa-check-circle me-2"></i>User updated successfully!</div>
            <?php elseif ($no_change): ?>
                <div class='alert alert-info alert-crud'><i class="fas fa-info-circle me-2"></i>No changes detected.</div>
            <?php endif; ?>
            
            <form action="update.php?id=<?= $user['account_id'] ?>" method="POST" enctype="multipart/form-data" class="crud-form">
                <input type="hidden" name="account_id" value="<?= htmlspecialchars($user['account_id']) ?>">

                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-heading mb-3">Personal Details</h4>
                        
                        <div class="form-group mb-3">
                            <label for="email">Email (Read Only)</label>
                            <input type="email" id="email" class="form-control custom-input" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>

                        <div class="form-group mb-3">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control custom-input" value="">
                        </div>

                        <div class="form-group mb-3">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control custom-input" value="">
                        </div>

                        <div class="form-group mb-3">
                            <label for="contact">Contact No.</label>
                            <input type="text" name="contact" id="contact" class="form-control custom-input" value="">
                        </div>

                        <div class="form-group mb-3">
                            <label for="address">Address</label>
                            <input type="text" name="address" id="address" class="form-control custom-input" value="">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="section-heading mb-3">Account Settings</h4>
                        
                        <div class="form-group mb-3">
                            <label for="role">Role:</label>
                            <select name="role" id="role" class="form-control custom-input">
                                <option value="customer" <?= ($user['role']=='customer' ? 'selected' : '') ?>>Customer</option>
                                <option value="admin" <?= ($user['role']=='admin' ? 'selected' : '') ?>>Admin</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="status">Status:</label>
                            <select name="status" id="status" class="form-control custom-input">
                                <option value="active" <?= ($user['status']=='active' ? 'selected' : '') ?>>Active</option>
                                <option value="inactive" <?= ($user['status']=='inactive' ? 'selected' : '') ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3 text-center">
                            <label class="d-block">Current Image:</label>
                            <img src="<?= $display_image_path ?>" class="rounded-circle profile-img-thumb mb-2" width="100" height="100" alt="User Image">
                            
                            <label for="image_upload" class="small d-block mt-2">Change Image (Optional):</label>
                            <input type="file" name="image" id="image_upload" class="form-control custom-input">
                        </div>
                    </div>
                </div>

                <hr class="form-divider">

                <div class="d-flex justify-content-end">
                    <button type="submit" name="update" class="btn login-btn">
                        <i class="fas fa-save me-1"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>