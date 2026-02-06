<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get dashboard stats
$stats = [];

if ($role == 'admin') {
    $stats['total_orders'] = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM orders"))['count'];
    $stats['new_orders'] = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as count FROM orders WHERE status = 'NOUVEAU_COLIS'"))['count'];
    $stats['delivered'] = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as count FROM orders WHERE status = 'DELIVERED'"))['count'];
    $stats['total_clients'] = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as count FROM users WHERE role = 'client'"))['count'];
    $stats['total_livreurs'] = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as count FROM users WHERE role = 'livreur'"))['count'];
    
    // Recent orders for admin
    $recent_orders = mysqli_query($conn, 
        "SELECT o.*, u.full_name as client_name 
         FROM orders o 
         JOIN users u ON o.client_id = u.id 
         ORDER BY o.created_at DESC LIMIT 10");
} elseif ($role == 'client') {
    $stats['total_orders'] = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM orders WHERE client_id = $user_id"))['count'];
    $stats['new_orders'] = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as count FROM orders WHERE client_id = $user_id AND status = 'NOUVEAU_COLIS'"))['count'];
    $stats['delivered'] = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as count FROM orders WHERE client_id = $user_id AND status = 'DELIVERED'"))['count'];
    $stats['in_progress'] = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as count FROM orders WHERE client_id = $user_id AND status LIKE '%PROGRESS%'"))['count'];
    
    // Recent orders for client
    $recent_orders = mysqli_query($conn, 
        "SELECT * FROM orders 
         WHERE client_id = $user_id 
         ORDER BY created_at DESC LIMIT 10");
} else { // livreur
    $stats['assigned'] = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(DISTINCT o.id) as count 
         FROM livreur_assignments la 
         JOIN orders o ON la.order_id = o.id 
         WHERE la.livreur_id = $user_id AND o.status NOT IN ('DELIVERED', 'RETURNED', 'CANCELED')"))['count'];
    $stats['delivered'] = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(DISTINCT o.id) as count 
         FROM livreur_assignments la 
         JOIN orders o ON la.order_id = o.id 
         WHERE la.livreur_id = $user_id AND o.status = 'DELIVERED'"))['count'];
    $stats['pending'] = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(DISTINCT o.id) as count 
         FROM livreur_assignments la 
         JOIN orders o ON la.order_id = o.id 
         WHERE la.livreur_id = $user_id AND o.status IN ('PICKED_UP', 'IN_PROGRESS', 'DISTRIBUTION')"))['count'];
    
    // Recent assigned orders
    $recent_orders = mysqli_query($conn, 
        "SELECT o.*, la.assigned_at 
         FROM livreur_assignments la 
         JOIN orders o ON la.order_id = o.id 
         WHERE la.livreur_id = $user_id 
         ORDER BY la.assigned_at DESC LIMIT 10");
}

// Get activity logs
$activity_logs = mysqli_query($conn,
    "SELECT sl.*, u.full_name as changed_by_name, o.tracking_code 
     FROM status_logs sl 
     JOIN users u ON sl.changed_by = u.id 
     JOIN orders o ON sl.order_id = o.id 
     ORDER BY sl.created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Courier Management System</title>
    <link rel="stylesheet" href="/cms/assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <?php include '../partials/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <?php include '../partials/header.php'; ?>
            
            <div class="content">
                <!-- Welcome Section -->
                <div class="card mb-3">
                    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👋</h2>
                    <p class="text-secondary mt-1">Here's what's happening with your shipments today.</p>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <?php if ($role == 'admin'): ?>
                        <div class="stat-card">
                            <div class="stat-icon primary">📦</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['total_orders']; ?></h3>
                                <p>Total Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success">🆕</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['new_orders']; ?></h3>
                                <p>New Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon warning">✅</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['delivered']; ?></h3>
                                <p>Delivered</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon info">👥</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['total_clients']; ?></h3>
                                <p>Active Clients</p>
                            </div>
                        </div>
                    <?php elseif ($role == 'client'): ?>
                        <div class="stat-card">
                            <div class="stat-icon primary">📦</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['total_orders']; ?></h3>
                                <p>My Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success">🆕</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['new_orders']; ?></h3>
                                <p>New Shipments</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon warning">🚚</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['in_progress']; ?></h3>
                                <p>In Progress</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon info">✅</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['delivered']; ?></h3>
                                <p>Delivered</p>
                            </div>
                        </div>
                    <?php else: // livreur ?>
                        <div class="stat-card">
                            <div class="stat-icon primary">📋</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['assigned']; ?></h3>
                                <p>Assigned Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success">✅</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['delivered']; ?></h3>
                                <p>Delivered</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon warning">⏳</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['pending']; ?></h3>
                                <p>Pending Delivery</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Orders</h3>
                        <a href="/cms/orders/list.php" class="btn btn-secondary btn-sm">View All</a>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tracking Code</th>
                                    <?php if ($role == 'admin'): ?>
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
                                <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($order['tracking_code']); ?></strong></td>
                                        <?php if ($role == 'admin'): ?>
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
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                    </div>
                    <div class="timeline">
                        <?php while ($log = mysqli_fetch_assoc($activity_logs)): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <strong><?php echo htmlspecialchars($log['changed_by_name']); ?></strong> 
                                    changed status of 
                                    <strong><?php echo htmlspecialchars($log['tracking_code']); ?></strong>
                                    from 
                                    <span class="text-secondary"><?php echo htmlspecialchars($log['old_status'] ?: 'N/A'); ?></span>
                                    to
                                    <span class="text-primary"><?php echo htmlspecialchars($log['new_status']); ?></span>
                                    <?php if ($log['comment']): ?>
                                        <p class="mt-1"><?php echo htmlspecialchars($log['comment']); ?></p>
                                    <?php endif; ?>
                                    <div class="timeline-date">
                                        <?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/cms/assets/js/app.js"></script>
</body>
</html>
