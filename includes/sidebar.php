<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3><?php echo APP_NAME; ?></h3>
        <p>School Money Collection</p>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo APP_URL; ?>/pages/dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="<?php echo APP_URL; ?>/pages/students/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], '/students/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i>
                <span>Students</span>
            </a>
        </li>

        <li>
            <a href="<?php echo APP_URL; ?>/pages/fees/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], '/fees/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Fee Management</span>
            </a>
        </li>

        <li>
            <a href="<?php echo APP_URL; ?>/pages/payments/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], '/payments/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Payments</span>
            </a>
        </li>

        <?php if (isAdmin()): ?>
        <li>
            <a href="<?php echo APP_URL; ?>/pages/parents/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], '/parents/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Parents</span>
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="<?php echo APP_URL; ?>/pages/reports/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], '/reports/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>

        <?php if (isAdmin()): ?>
        <li>
            <a href="<?php echo APP_URL; ?>/pages/settings/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], '/settings/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="btn-menu-toggle" id="menuToggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h4><?php echo $pageTitle ?? 'Dashboard'; ?></h4>
        </div>

        <div class="top-bar-right">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['fullname'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($_SESSION['role'] ?? 'user'); ?></div>
                </div>
            </div>

            <button class="btn-logout" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </button>
        </div>
    </div>

    <!-- Content Area -->
    <div class="content-area">
