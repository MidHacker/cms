<header class="header">
    <div class="header-left">
        <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
    </div>
    <div class="header-right">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search orders, clients...">
        </div>
        <button class="notification-btn">
            🔔
            <span class="notification-badge">3</span>
        </button>
        <div class="user-menu">
            <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
        </div>
    </div>
</header>
