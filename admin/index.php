<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// check kung admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include('../includes/header.php');
include('../includes/config.php');

$management_actions = [
    [
        'title' => 'Products & Inventory',
        'description' => 'Add, edit, and view all vinyl records and manage stock levels.',
        'icon' => 'fa-box-open',
        'view_link' => '../item/index.php',
        'manage_link' => '../item/index.php',
        'color_class' => 'theme-green',
    ],
    [
        'title' => 'Customer Orders',
        'description' => 'Review, process, and update the status of all incoming and pending orders.',
        'icon' => 'fa-clipboard-list',
        'view_link' => '../manageOrder/index.php',
        'manage_link' => '../manageOrder/index.php',
        'color_class' => 'theme-primary',
    ],
    [
        'title' => 'User Management',
        'description' => 'View, edit, or deactivate customer and other admin accounts.',
        'icon' => 'fa-users',
        'view_link' => '../userCrud/index.php',
        'manage_link' => '../userCrud/index.php',
        'color_class' => 'theme-info',
    ],
];
?>

<div class="container-fluid site-content-wrapper py-5">

    <div class="mx-auto" style="max-width: 1100px;"> 
        <h1 class="crud-title text-center mt-5 mb-5">
            <i class="fas fa-screwdriver-wrench me-2"></i> Control Panel Dashboard
        </h1> 
        
        <p class="text-light text-center mb-5">
            Welcome back, **<?= htmlspecialchars($_SESSION['email'] ?? 'Admin') ?>**! 
            Use the management tools below to oversee the store operations.
        </p>
        
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
            <?php foreach ($management_actions as $action): ?>
            <div class="col">
                <div class="card card-crud management-card h-100 p-3">
                    <div class="card-body-crud d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas <?= htmlspecialchars($action['icon']) ?> fa-2x me-3 text-white <?= htmlspecialchars($action['color_class']) ?>"></i>
                            <h4 class="text-white mb-0"><?= htmlspecialchars($action['title']) ?></h4>
                        </div>
                        
                        <p class="text-light flex-grow-1"><?= htmlspecialchars($action['description']) ?></p>
                        
                        <div class="d-flex justify-content-start gap-2 pt-2 border-top border-secondary">
                            <a href="<?= htmlspecialchars($action['view_link']) ?>" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-eye me-1"></i> View Overview
                            </a>
                            
                            <a href="<?= htmlspecialchars($action['manage_link']) ?>" class="btn btn-sm login-btn ms-auto">
                                <i class="fas fa-wrench me-1"></i> Manage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <hr class="form-divider">

        <div class="text-center mt-5">
            <a href="../index.php" class="btn btn-outline-light w-100" style="max-width: 300px;">
                <i class="fa-solid fa-home me-2"></i> Return to Store Front
            </a>
        </div>

    </div>
</div>

<?php include('../includes/footer.php'); ?>
