<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();
redirectIfNotAuthorized('admin');

$page_title = 'Create Bon de Livraison';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = 'Bon de livraison created successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Bon - Courier Management System</title>
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
                        <h2 class="card-title">Create Bon de Livraison</h2>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference" class="form-control" placeholder="e.g., BL-2024-001" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="text-right">
                            <a href="/cms/bons/list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Bon</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="/cms/assets/js/app.js"></script>
</body>
</html>
