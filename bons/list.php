<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();
redirectIfNotAuthorized('admin');

$page_title = 'Bons de Livraison';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bons de Livraison - Courier Management System</title>
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
                        <h2 class="card-title">Bons de Livraison</h2>
                        <a href="/cms/bons/create.php" class="btn btn-primary btn-sm">Create Bon</a>
                    </div>
                    <div class="alert alert-warning">
                        No delivery notes have been generated yet.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/cms/assets/js/app.js"></script>
</body>
</html>
