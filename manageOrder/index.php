<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// admin check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

// get all orders with customer info
$sql = "SELECT o.order_id, o.order_date, o.order_status, o.shipping_address, o.remarks,
        c.first_name, c.last_name
        FROM orderinfo o
        JOIN customer_details c ON o.customer_id = c.customer_id
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);

?>

<div class="container-fluid site-content-wrapper">

    <h1 class="crud-title mt-4 mb-4">Order Management</h1>

    <div class="card-crud mb-5">
        <div class="card-header-crud d-flex justify-content-between align-items-center">
            <h2 class="card-heading-crud">Order List</h2>
            <a href="create.php" class="btn login-btn">
                <i class="fas fa-plus me-1"></i> Add New Order
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class='alert alert-success alert-crud'>
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class='alert alert-danger alert-crud'>
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <?php if ($result && $result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Address</th>
                            <th>Remarks</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['order_id']) ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= date("Y-m-d", strtotime($row['order_date'])) ?></td>
                            <td>
                                <span class="badge badge-status status-<?php echo strtolower($row['order_status']); ?>">
                                    <?= htmlspecialchars($row['order_status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(substr($row['shipping_address'], 0, 50)) . (strlen($row['shipping_address']) > 50 ? '...' : '') ?></td>
                            <td><?= htmlspecialchars(substr($row['remarks'], 0, 30)) . (strlen($row['remarks']) > 30 ? '...' : '') ?></td>
                            <td class="text-center">
                                <a href="update.php?id=<?= $row['order_id'] ?>" class="action-link edit-link" title="Edit Order"><i class="fas fa-edit"></i> Edit</a>
                                <span class="text-muted">|</span>
                                <a href="delete.php?id=<?= $row['order_id'] ?>" class="action-link delete-link" 
                                   onclick="return confirm('WARNING: Are you sure you want to delete Order #<?= $row['order_id'] ?>? This action is permanent.')" title="Delete Order">
                                   <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class='alert alert-info alert-crud'>
                    No orders found in the database.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>