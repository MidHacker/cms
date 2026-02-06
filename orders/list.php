<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

$page_title = 'Orders';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    $orders = mysqli_query($conn, "SELECT o.*, u.full_name as client_name FROM orders o JOIN users u ON o.client_id = u.id ORDER BY o.created_at DESC");
} elseif ($role === 'client') {
    $orders = mysqli_query($conn, "SELECT * FROM orders WHERE client_id = $user_id ORDER BY created_at DESC");
} else {
    $orders = mysqli_query($conn, "SELECT o.* FROM orders o JOIN livreur_assignments la ON la.order_id = o.id WHERE la.livreur_id = $user_id ORDER BY la.assigned_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Courier Management System</title>
    <link rel="stylesheet" href="/cms/assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <?php include '../partials/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../partials/header.php'; ?>
            <div class="content">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Orders</h2>
                        <?php if ($role !== 'livreur'): ?>
                            <a href="/cms/orders/add.php" class="btn btn-primary btn-sm">New Order</a>
                        <?php endif; ?>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tracking Code</th>
                                    <?php if ($role === 'admin'): ?>
                                        <th>Client</th>
                                    <?php endif; ?>
                                    <th>Receiver</th>
                                    <th>City</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($order['tracking_code']); ?></strong></td>
                                            <?php if ($role === 'admin'): ?>
                                                <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                                            <?php endif; ?>
                                            <td><?php echo htmlspecialchars($order['receiver_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['receiver_city']); ?></td>
                                            <td>
                                                <?php 
                                                $status_class = '';
                                                if (strpos($order['status'], 'NOUVEAU') !== false) $status_class = 'status-nouveau';
                                                elseif (strpos($order['status'], 'DELIVERED') !== false) $status_class = 'status-delivered';
                                                elseif (strpos($order['status'], 'PROGRESS') !== false) $status_class = 'status-progress';
                                                elseif (strpos($order['status'], 'CANCELED') !== false) $status_class = 'status-canceled';
                                                elseif (strpos($order['status'], 'RETURN') !== false) $status_class = 'status-returned';
                                                else $status_class = 'status-received';
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo htmlspecialchars($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="/cms/orders/view.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?php echo $role === 'admin' ? '7' : '6'; ?>" class="text-center">No orders available yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/cms/assets/js/app.js"></script>
</body>
</html>
