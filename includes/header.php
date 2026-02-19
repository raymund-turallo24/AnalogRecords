<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function homeLink() {
    $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
    return $isAdmin
        ? '/AnalogRecords/admin/index.php'
        : '/AnalogRecords/index.php';
}

$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="/AnalogRecords/includes/style/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Analog Records</title>
</head>

<body>
    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo homeLink(); ?>">Analog Records</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto ms-3"> 
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo homeLink(); ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/AnalogRecords/about.php">About Us</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">

                    <?php if (!$isAdmin): ?>
                        <li class="nav-item cart-item">
                            <a class="nav-link" href="<?php 
                                echo isset($_SESSION['account_id']) 
                                    ? '/AnalogRecords/cart/view_cart.php' 
                                    : '/AnalogRecords/user/login.php'; 
                            ?>" aria-label="Cart">
                                <i class="fa-solid fa-cart-shopping"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <span class="nav-link pipe-divider px-2">|</span>
                        </li>
                    <?php endif; ?>

                    <?php if (!isset($_SESSION['account_id'])): ?>
                        <li class="nav-item d-flex align-items-center gap-2">
                            <a class="signup-link" href="/AnalogRecords/user/register.php">Sign Up</a>
                            <a class="btn login-btn" href="/AnalogRecords/user/login.php">Sign in</a>
                        </li>
                    <?php elseif ($isAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/AnalogRecords/admin/index.php" title="Admin Profile">
                                <i class="fa-solid fa-user-gear me-1"></i> 
                                <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link pipe-divider px-2">|</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/AnalogRecords/user/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end custom-dropdown" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/AnalogRecords/user/profile.php">My Account</a></li>
                                <li><a class="dropdown-item" href="/AnalogRecords/cart/myreviews.php">My Reviews</a></li>
                                <li><a class="dropdown-item" href="/AnalogRecords/cart/view_order.php">My Orders</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/AnalogRecords/user/logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>