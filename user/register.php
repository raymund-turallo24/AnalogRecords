<?php
session_start();
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email       = trim($_POST["email"] ?? '');
    $password    = trim($_POST["password"] ?? '');
    $confirmPass = trim($_POST["confirmPass"] ?? '');

    $errors = [];

    if (empty($email))       $errors[] = "Email is required.";
    if (empty($password))    $errors[] = "Password is required.";
    if (empty($confirmPass)) $errors[] = "Confirm Password is required.";

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirmPass) {
        $errors[] = "Passwords do not match.";
    }

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
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO accounts (email, password, role) VALUES (?, ?, 'customer')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $email, $hashed_password);

        if ($stmt->execute()) {
            $account_id = $stmt->insert_id;

            $stmt2 = $conn->prepare("INSERT INTO customer_details (first_name, last_name, address, account_id) VALUES ('', '', '', ?)");
            $stmt2->bind_param("i", $account_id);
            $stmt2->execute();

            $_SESSION["account_id"] = $account_id;
            $_SESSION["email"]      = $email;
            $_SESSION["role"]       = 'customer';
            $_SESSION["success"]    = "Account registered successfully.";

            header("Location: profileUser.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }

    if (!empty($errors)) {
        $_SESSION["message"] = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="/AnalogRecords/includes/style/style.css" rel="stylesheet" type="text/css">
    <title>Analog Records - Register</title>

    <style>
        /* COMPLETELY DISABLE Bootstrap success checkmarks (green checkmark) */
        .form-control.is-valid,
        .was-validated .form-control:valid {
            background-image: none !important;
            border-color: #ced4da !important;
        }
        .form-control.is-valid:focus,
        .was-validated .form-control:valid:focus {
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }
        /* Also hide any valid feedback text if accidentally added */
        .valid-feedback { display: none !important; }
    </style>
</head>

<body class="login-page-body">
    <div class="login-container">
        <div class="logo-placeholder">
            <a href="/AnalogRecords/index.php" class="text-white text-decoration-none h4">Analog Records</a>
        </div>

        <h1 class="welcome-text">Create Your Account</h1>
        
        <?php 
        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-danger" role="alert">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>

        <!-- Super clean form – no validation, no icons, no feedback -->
        <form action="" method="POST" class="login-form">
            
            <div class="mb-3">
                <label for="email" class="form-label input-label">Email address</label>
                <input type="text" 
                       id="email" 
                       name="email" 
                       class="form-control custom-input" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label input-label">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control custom-input" />
            </div>

            <div class="mb-4">
                <label for="confirmPass" class="form-label input-label">Confirm Password</label>
                <input type="password" 
                       id="confirmPass" 
                       name="confirmPass" 
                       class="form-control custom-input" />
            </div>

            <button type="submit" class="btn continue-btn" name="submit">Register</button>
            
            <div class="text-center mt-4">
                <p>Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
            </div>
        </form>
    </div>
</body>
</html>