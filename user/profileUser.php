<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}

$account_id = $_SESSION['account_id'];

// Fetch profile info from database (accounts + customer_details)
$sql = "SELECT a.email, a.role, a.status, a.date_created AS acc_created,
               c.first_name, c.last_name, c.contact, c.address, c.image, c.date_created AS cust_created
        FROM accounts a
        JOIN customer_details c ON a.account_id = c.account_id
        WHERE a.account_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<div class='alert alert-danger'>Profile not found.</div>";
    exit();
}
?>

<div class="container mt-5">
    <h2 class="mb-4">My Profile</h2>
    <div class="card p-4 shadow-sm" style="max-width: 600px;">
        <div class="text-center mb-3">
            <?php 
            $image_path = "../uploads/" . $user['image'];
            if (!empty($user['image']) && file_exists($image_path)): ?>
                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                     alt="Profile Picture" class="rounded-circle" width="120" height="120">
            <?php else: ?>
                <!-- Placeholder if no image -->
                <div class="rounded-circle d-flex justify-content-center align-items-center" 
                     style="width:120px; height:120px; background-color:#ddd; color:#555; font-weight:bold;">
                    No Image
                </div>
            <?php endif; ?>
        </div>

        <table class="table table-borderless">
            <tr><th>Full Name:</th><td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td></tr>
            <tr><th>Email:</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
            <tr><th>Contact:</th><td><?php echo htmlspecialchars($user['contact']); ?></td></tr>
            <tr><th>Address:</th><td><?php echo htmlspecialchars($user['address']); ?></td></tr>
            <tr><th>Role:</th><td><?php echo htmlspecialchars($user['role']); ?></td></tr>
            <tr><th>Status:</th><td><?php echo htmlspecialchars($user['status']); ?></td></tr>
            <tr><th>Account Created:</th><td><?php echo htmlspecialchars($user['acc_created']); ?></td></tr>
        </table>

        <div class="text-center">
            <a href="profileUpdate.php" class="btn btn-primary">Update Profile</a>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
