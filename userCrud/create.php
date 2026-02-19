<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

$error = '';

if (isset($_POST['save'])) {
    $email      = trim($_POST['email']);
    $password   = $_POST['password']; 
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $contact    = trim($_POST['contact'] ?? '');
    $address    = trim($_POST['address']);
    $role       = trim($_POST['role']);
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (empty($email) || empty($password) || empty($first_name) || empty($address)) {
        $error = "Please fill in all required fields.";
    } else {

        $image = null;
        $targetDir = "../uploads/";
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $fileName = basename($_FILES["image"]["name"]);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = uniqid("img_") . "." . $fileExt;
            $targetFilePath = $targetDir . $newFileName;

            $allowedTypes = array("jpg", "jpeg", "png");
            if (!in_array($fileExt, $allowedTypes)) {
                $error = "Only JPG, JPEG, and PNG files are allowed for the image.";
            } elseif (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $error = "Error uploading image. Check folder permissions.";
            } else {
                $image = $newFileName;
            }
        }
        
        if (empty($error)) {
            mysqli_begin_transaction($conn);
            $account_id = 0;
            
            try {
                // insert into accounts
                $stmt = $conn->prepare("INSERT INTO accounts (email, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $hashed_password, $role); 
                
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting into accounts: " . $stmt->error);
                }
                $account_id = $stmt->insert_id;
                $stmt->close();

                // insert into customer_details
                $stmt2 = $conn->prepare("INSERT INTO customer_details (first_name, last_name, contact, address, image, account_id) 
                                         VALUES (?, ?, ?, ?, ?, ?)");

                $stmt2->bind_param("sssssi", $first_name, $last_name, $contact, $address, $image, $account_id);
                
                if (!$stmt2->execute()) {
                    throw new Exception("Error inserting into customer_details: " . $stmt2->error);
                }
                $stmt2->close();
                
                mysqli_commit($conn);
                
                $_SESSION['success'] = "User **{$email}** created successfully! Account ID: {$account_id}";
                header("Location: index.php");
                exit();
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Failed to create user: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="container-fluid site-content-wrapper">

    <div class="card-crud my-5 mx-auto" style="max-width: 700px;">
        <div class="card-header-crud d-flex justify-content-between align-items-center">
            <h2 class="card-heading-crud">Create New User</h2>
            <a href="index.php" class="btn back-btn"><i class="fas fa-chevron-left me-1"></i> Back to User List</a>
        </div>
        
        <div class="card-body-crud p-4">
            
            <?php if (!empty($error)): ?>
                <div class='alert alert-danger alert-crud'>
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form action="create.php" method="POST" enctype="multipart/form-data" class="crud-form">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-heading mb-3">Account Credentials</h4>
                        <div class="form-group mb-3">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control custom-input" required 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control custom-input" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="role">Role:</label>
                            <select name="role" id="role" class="form-control custom-input">
                                <option value="customer">Customer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="image">Upload Image (Optional, JPG/PNG):</label>
                            <input type="file" name="image" id="image" class="form-control custom-input" accept="image/jpeg, image/png">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="section-heading mb-3">Personal Details</h4>
                        <div class="form-group mb-3">
                            <label for="first_name">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control custom-input" required
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label for="last_name">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="last_name" class="form-control custom-input" required
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label for="contact">Contact (Optional):</label>
                            <input type="text" name="contact" id="contact" class="form-control custom-input"
                                   value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label for="address">Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" id="address" class="form-control custom-input" required
                                   value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <hr class="form-divider">

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" name="save" class="btn login-btn">
                        <i class="fas fa-user-plus me-1"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>