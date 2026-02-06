<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

// Only clients and admin can add orders
if ($_SESSION['role'] == 'livreur') {
    header('Location: /cms/dashboard/index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Generate tracking code
    $tracking_code = generateTrackingCode();
    
    // Get client ID
    $client_id = ($_SESSION['role'] == 'client') ? $_SESSION['user_id'] : $_POST['client_id'];
    
    // Collect form data
    $sender_name = sanitize($_POST['sender_name']);
    $sender_phone = sanitize($_POST['sender_phone']);
    $sender_address = sanitize($_POST['sender_address']);
    $receiver_name = sanitize($_POST['receiver_name']);
    $receiver_phone = sanitize($_POST['receiver_phone']);
    $receiver_address = sanitize($_POST['receiver_address']);
    $receiver_city = sanitize($_POST['receiver_city']);
    $product_description = sanitize($_POST['product_description']);
    $weight = floatval($_POST['weight']);
    $dimensions = sanitize($_POST['dimensions']);
    $cod_amount = floatval($_POST['cod_amount']);
    $parcel_type = sanitize($_POST['parcel_type']);
    $hub = sanitize($_POST['hub']);
    
    // Insert order
    $sql = "INSERT INTO orders (tracking_code, client_id, sender_name, sender_phone, sender_address, 
            receiver_name, receiver_phone, receiver_address, receiver_city, product_description, 
            weight, dimensions, cod_amount, parcel_type, hub) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sisssssssssdsss", 
        $tracking_code, $client_id, $sender_name, $sender_phone, $sender_address,
        $receiver_name, $receiver_phone, $receiver_address, $receiver_city, $product_description,
        $weight, $dimensions, $cod_amount, $parcel_type, $hub);
    
    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($conn);
        
        // Log status change
        $log_sql = "INSERT INTO status_logs (order_id, new_status, changed_by, comment) 
                   VALUES (?, 'NOUVEAU_COLIS', ?, 'Order created')";
        $log_stmt = mysqli_prepare($conn, $log_sql);
        mysqli_stmt_bind_param($log_stmt, "ii", $order_id, $_SESSION['user_id']);
        mysqli_stmt_execute($log_stmt);
        
        $success = "Order created successfully! Tracking Code: $tracking_code";
        
        // Clear form if success
        $_POST = array();
    } else {
        $error = "Failed to create order: " . mysqli_error($conn);
    }
}

// Get clients for admin dropdown
$clients = [];
if ($_SESSION['role'] == 'admin') {
    $clients_result = mysqli_query($conn, "SELECT id, full_name FROM users WHERE role = 'client' AND is_active = 1");
    while ($client = mysqli_fetch_assoc($clients_result)) {
        $clients[] = $client;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Order - Courier Management System</title>
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
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Create New Order</h2>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <!-- Sender Information -->
                        <div class="card mb-3">
                            <h3 class="mb-2">📦 Sender Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Sender Name *</label>
                                    <input type="text" name="sender_name" class="form-control" 
                                           value="<?php echo $_POST['sender_name'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sender Phone *</label>
                                    <input type="tel" name="sender_phone" class="form-control" 
                                           value="<?php echo $_POST['sender_phone'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sender Address *</label>
                                <textarea name="sender_address" class="form-control" rows="2" required><?php echo $_POST['sender_address'] ?? ''; ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Receiver Information -->
                        <div class="card mb-3">
                            <h3 class="mb-2">👤 Receiver Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Receiver Name *</label>
                                    <input type="text" name="receiver_name" class="form-control" 
                                           value="<?php echo $_POST['receiver_name'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Receiver Phone *</label>
                                    <input type="tel" name="receiver_phone" class="form-control" 
                                           value="<?php echo $_POST['receiver_phone'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">City *</label>
                                    <input type="text" name="receiver_city" class="form-control" 
                                           value="<?php echo $_POST['receiver_city'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Hub</label>
                                    <input type="text" name="hub" class="form-control" 
                                           value="<?php echo $_POST['hub'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Full Address *</label>
                                <textarea name="receiver_address" class="form-control" rows="2" required><?php echo $_POST['receiver_address'] ?? ''; ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Package Details -->
                        <div class="card mb-3">
                            <h3 class="mb-2">📋 Package Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Product Description *</label>
                                    <textarea name="product_description" class="form-control" rows="3" required><?php echo $_POST['product_description'] ?? ''; ?></textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.01" name="weight" class="form-control" 
                                           value="<?php echo $_POST['weight'] ?? '1.00'; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Dimensions (LxWxH)</label>
                                    <input type="text" name="dimensions" class="form-control" 
                                           value="<?php echo $_POST['dimensions'] ?? ''; ?>" placeholder="e.g., 20x15x10">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">COD Amount (MAD)</label>
                                    <input type="number" step="0.01" name="cod_amount" class="form-control" 
                                           value="<?php echo $_POST['cod_amount'] ?? '0.00'; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Parcel Type</label>
                                    <select name="parcel_type" class="form-control">
                                        <option value="normal" <?php echo ($_POST['parcel_type'] ?? 'normal') == 'normal' ? 'selected' : ''; ?>>Normal</option>
                                        <option value="fragile" <?php echo ($_POST['parcel_type'] ?? '') == 'fragile' ? 'selected' : ''; ?>>Fragile</option>
                                        <option value="autorise_ouvrir" <?php echo ($_POST['parcel_type'] ?? '') == 'autorise_ouvrir' ? 'selected' : ''; ?>>Autorisé d'ouvrir</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- For admin only: Client selection -->
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <div class="card mb-3">
                                <h3 class="mb-2">👥 Assign to Client</h3>
                                <div class="form-group">
                                    <label class="form-label">Select Client *</label>
                                    <select name="client_id" class="form-control" required>
                                        <option value="">Select a client</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?php echo $client['id']; ?>" 
                                                <?php echo ($_POST['client_id'] ?? '') == $client['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($client['full_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-right">
                            <a href="/cms/dashboard/index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/cms/assets/js/app.js"></script>
</body>
</html>
