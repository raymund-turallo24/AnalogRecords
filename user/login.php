<?php
session_start();
include("../includes/config.php");

if (isset($_POST['submit'])) {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    $errors = [];

    if (empty($email)) $errors[] = "Email is required.";
    if (empty($pass))  $errors[] = "Password is required.";

    if (empty($errors)) {
        $sql = "SELECT account_id, email, password, role, status FROM accounts WHERE email=? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if ($row['status'] !== 'active') {
                $errors[] = "Your account is inactive. Please contact the administrator.";
            } elseif (password_verify($pass, $row['password'])) {
                $_SESSION['account_id'] = $row['account_id'];
                $_SESSION['email']      = $row['email'];
                $_SESSION['role']       = $row['role'];

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="/AnalogRecords/includes/style/style.css" rel="stylesheet" type="text/css">
    <title>Analog Records - Login</title>

    <style>
        /* Permanently disable any green checkmark icons (just in case) */
        .form-control.is-valid,
        .was-validated .form-control:valid {
            background-image: none !important;
        }
        .valid-feedback { display: none !important; }
    </style>
</head>

<body class="login-page-body">
    <div class="login-container">
        <div class="logo-placeholder">
           <a href="/AnalogRecords/index.php" class="text-white text-decoration-none h4">Analog Records</a>
        </div>

        <h1 class="welcome-text">Welcome back</h1>
        
        <?php 
        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-danger" role="alert">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>

        <!-- Super clean form – zero client-side anything -->
        <form action="" method="POST" class="login-form">
            
            <div class="mb-3">
                <label for="email" class="form-label input-label">Email or username</label>
                <input type="text" 
                       id="email" 
                       name="email" 
                       class="form-control custom-input"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label input-label">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control custom-input" />
            </div>

            <button type="submit" class="btn continue-btn" name="submit">Continue</button>
            
            <div class="text-center mt-4">
                <p>Not a member? <a href="register.php" class="text-decoration-none">Register</a></p>
            </div>
        </form>
    </div>
</body>
</html>