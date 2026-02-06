<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

$page_title = 'Order Details';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /cms/orders/list.php');
    exit();
}

$order_id = intval($_GET['id']);

if ($role === 'admin') {
    $order_query = mysqli_prepare($conn, "SELECT o.*, u.full_name as client_name FROM orders o JOIN users u ON o.client_id = u.id WHERE o.id = ?");
    mysqli_stmt_bind_param($order_query, "i", $order_id);
} elseif ($role === 'client') {
    $order_query = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND client_id = ?");
    mysqli_stmt_bind_param($order_query, "ii", $order_id, $user_id);
} else {
    $order_query = mysqli_prepare($conn, "SELECT o.* FROM orders o JOIN livreur_assignments la ON la.order_id = o.id WHERE o.id = ? AND la.livreur_id = ?");
    mysqli_stmt_bind_param($order_query, "ii", $order_id, $user_id);
}

mysqli_stmt_execute($order_query);
$result = mysqli_stmt_get_result($order_query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header('Location: /cms/orders/list.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Courier Management System</title>
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
                        <h2 class="card-title">Order <?php echo htmlspecialchars($order['tracking_code']); ?></h2>
                        <a href="/cms/orders/list.php" class="btn btn-secondary btn-sm">Back to Orders</a>
                    </div>

                    <div class="form-row">
                        <div class="card">
                            <h3 class="mb-2">📦 Shipment Details</h3>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                            <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                            <?php if (!empty($order['client_name'])): ?>
                                <p><strong>Client:</strong> <?php echo htmlspecialchars($order['client_name']); ?></p>
                            <?php endif; ?>
                            <p><strong>COD Amount:</strong> <?php echo number_format($order['cod_amount'], 2); ?> MAD</p>
                        </div>
                        <div class="card">
                            <h3 class="mb-2">📋 Package Details</h3>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($order['product_description']); ?></p>
                            <p><strong>Weight:</strong> <?php echo htmlspecialchars($order['weight']); ?> kg</p>
                            <p><strong>Dimensions:</strong> <?php echo htmlspecialchars($order['dimensions'] ?: 'N/A'); ?></p>
                            <p><strong>Parcel Type:</strong> <?php echo htmlspecialchars($order['parcel_type']); ?></p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="card">
                            <h3 class="mb-2">🙋 Sender Information</h3>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['sender_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['sender_phone']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['sender_address']); ?></p>
                        </div>
                        <div class="card">
                            <h3 class="mb-2">📍 Receiver Information</h3>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['receiver_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['receiver_phone']); ?></p>
                            <p><strong>City:</strong> <?php echo htmlspecialchars($order['receiver_city']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['receiver_address']); ?></p>
                        </div>
                    </div>

                    <div class="text-right">
                        <a href="/cms/tickets/pdf.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">Generate PDF</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/cms/assets/js/app.js"></script>
</body>
</html>
