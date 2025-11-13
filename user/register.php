<?php
session_start();
include("../includes/header.php");
include("../includes/config.php"); // database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirmPass = trim($_POST["confirmPass"]);

    $errors = [];

    // Server-side validation
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($confirmPass)) $errors[] = "Confirm Password is required.";

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirmPass) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $check = $conn->prepare("SELECT account_id FROM accounts WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already registered.";
        }
    }

    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new account
        $query = "INSERT INTO accounts (email, password) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $email, $hashed_password);

        if ($stmt->execute()) {
            $account_id = $stmt->insert_id;

            // Optional: Create empty customer_details row
            $stmt2 = $conn->prepare("INSERT INTO customer_details (first_name, last_name, address, account_id) VALUES ('', '', '', ?)");
            $stmt2->bind_param("i", $account_id);
            $stmt2->execute();

            // Store info in session
            $_SESSION["account_id"] = $account_id;
            $_SESSION["email"] = $email;
            $_SESSION["success"] = "Account registered successfully.";

            header("Location: profile.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }

    if (!empty($errors)) {
        $_SESSION["error"] = implode("<br>", $errors);
        header("Location: register.php");
        exit();
    }
}
?>

<div class="container-fluid container-lg">
    <?php include("../includes/alert.php"); ?>
    <form action="" method="POST" class="p-3 border rounded shadow-sm bg-light">
        <h3 class="mb-3">Create an Account</h3>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="text" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password">
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="confirmPass">
        </div>

        <button type="submit" class="btn btn-primary w-100">Register</button>
        <p class="mt-3 text-center">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </form>
</div>

<?php include("../includes/footer.php"); ?>