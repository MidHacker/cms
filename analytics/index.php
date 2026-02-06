<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();
redirectIfNotAuthorized('admin');

$page_title = 'Analytics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Courier Management System</title>
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
                        <h2 class="card-title">Analytics</h2>
                    </div>
                    <div class="alert alert-warning">
                        Analytics dashboards will appear here once data is available.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/cms/assets/js/app.js"></script>
</body>
</html>
