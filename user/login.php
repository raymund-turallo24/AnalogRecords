<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

// Handle form submission
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    $errors = [];

    // Server-side validation
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($pass)) $errors[] = "Password is required.";

    if (empty($errors)) {
        // Fetch account info
        $sql = "SELECT account_id, email, password, role FROM accounts WHERE email=? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (password_verify($pass, $row['password'])) {
                // Set session variables
                $_SESSION['account_id'] = $row['account_id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = $row['role'];

                // If customer, fetch customer_id
                if ($row['role'] === 'customer') {
                    $sql2 = "SELECT customer_id FROM customer_details WHERE account_id=?";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param("i", $row['account_id']);
                    $stmt2->execute();
                    $res2 = $stmt2->get_result();
                    if ($res2->num_rows === 1) {
                        $cust = $res2->fetch_assoc();
                        $_SESSION['customer_id'] = $cust['customer_id'];
                    }
                    header("Location: http://localhost/AnalogRecords/index.php");
                    exit();
                } else {
                    header("Location: http://localhost/AnalogRecords/admin/index.php");
                    exit();
                }
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "Account not found.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
    }
}
?>

<div class="row col-md-8 mx-auto">
    <?php include("../includes/alert.php"); ?>
    <form action="" method="POST">
        <div class="form-outline mb-4">
            <input type="text" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
            <label class="form-label">Email address</label>
        </div>
        <div class="form-outline mb-4">
            <input type="password" class="form-control" name="password" />
            <label class="form-label">Password</label>
        </div>
        <button type="submit" class="btn btn-primary btn-block mb-4" name="submit">Sign in</button>
        <div class="text-center">
            <p>Not a member? <a href="register.php">Register</a></p>
        </div>
    </form>
</div>

<?php include("../includes/footer.php"); ?>