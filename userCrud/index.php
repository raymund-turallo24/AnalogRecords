<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

?>

<div class="container-fluid site-content-wrapper">

    <h1 class="crud-title mt-4 mb-4">User Management Dashboard</h1>

    <div class="card-crud mb-5">
        <div class="card-header-crud d-flex justify-content-between align-items-center">
            <h2 class="card-heading-crud">Accounts Table</h2>
            <a href="create.php" class="btn login-btn">
                <i class="fas fa-plus me-1"></i> Add New User
            </a>
        </div>
        
        <div class="table-responsive">
            <?php
            $sql_accounts = "SELECT * FROM accounts ORDER BY account_id DESC";
            $result_accounts = $conn->query($sql_accounts);
            ?>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Account ID</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_accounts->num_rows > 0): ?>
                        <?php while ($acc = $result_accounts->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($acc['account_id']) ?></td>
                            <td><?= htmlspecialchars($acc['email']) ?></td>
                            <td><span class="badge badge-role role-<?= strtolower($acc['role']) ?>"><?= htmlspecialchars(ucfirst($acc['role'])) ?></span></td>
                            <td><span class="badge badge-status status-<?= strtolower($acc['status']) ?>"><?= htmlspecialchars(ucfirst($acc['status'])) ?></span></td>
                            <td><?= date("Y-m-d", strtotime($acc['date_created'])) ?></td>
                            <td>
                                <a href="update.php?id=<?= $acc['account_id'] ?>" class="action-link edit-link">Edit</a> | 
                                <a href="delete.php?id=<?= $acc['account_id'] ?>" class="action-link delete-link" onclick="return confirm('WARNING: Are you sure you want to delete this account? This action is permanent.')">Delete</a>
                            </td>
                        </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card-crud mb-5">
        <div class="card-header-crud">
            <h2 class="card-heading-crud">Customer Details Table</h2>
        </div>
        
        <div class="table-responsive">
            <?php
            // show customer details
            $sql_customers = "SELECT * FROM customer_details ORDER BY customer_id DESC";
            $result_customers = $conn->query($sql_customers);
            ?>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Image</th>
                        <th>Date Created</th>
                        <th>Account ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_customers->num_rows > 0): ?>
                        <?php while ($cust = $result_customers->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($cust['customer_id']) ?></td>
                            <td><?= htmlspecialchars($cust['first_name']) ?></td>
                            <td><?= htmlspecialchars($cust['last_name']) ?></td>
                            <td><?= htmlspecialchars($cust['contact']) ?></td>
                            <td><?= htmlspecialchars($cust['address']) ?></td>
                            <td><?= htmlspecialchars($cust['image']) ?></td>
                            <td><?= date("Y-m-d", strtotime($cust['date_created'])) ?></td>
                            <td><?= htmlspecialchars($cust['account_id']) ?></td>
                        </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No customer details found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<?php
include("../includes/footer.php");
?>