<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="/cms/dashboard/index.php" class="logo">CourierMS</a>
    </div>
    
    <div class="user-info">
        <div class="user-avatar">
            <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
        </div>
        <div class="user-details">
            <h4><?php echo htmlspecialchars($_SESSION['full_name']); ?></h4>
            <small><?php echo ucfirst($_SESSION['role']); ?></small>
        </div>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="/cms/dashboard/index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                📊 Dashboard
            </a>
        </li>
        
        <?php if ($_SESSION['role'] != 'livreur'): ?>
            <li class="nav-item">
                <a href="/cms/orders/add.php" class="nav-link <?php echo $current_page == 'add.php' ? 'active' : ''; ?>">
                    ➕ New Order
                </a>
            </li>
        <?php endif; ?>
        
        <li class="nav-item">
            <a href="/cms/orders/list.php" class="nav-link <?php echo $current_page == 'list.php' ? 'active' : ''; ?>">
                📋 Orders
            </a>
        </li>
        
        <?php if ($_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
                <a href="/cms/bons/list.php" class="nav-link <?php echo $current_page == 'list.php' ? 'active' : ''; ?>">
                    🚚 Bons de Livraison
                </a>
            </li>
            <li class="nav-item">
                <a href="/cms/analytics/index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    📈 Analytics
                </a>
            </li>
        <?php endif; ?>
        
        <?php if ($_SESSION['role'] == 'livreur'): ?>
            <li class="nav-item">
                <a href="/cms/livreur/assigned.php" class="nav-link">
                    📦 My Assignments
                </a>
            </li>
        <?php endif; ?>
        
        <li class="nav-item">
            <a href="/cms/auth/logout.php" class="nav-link">
                🔓 Logout
            </a>
        </li>
    </ul>
</aside>
