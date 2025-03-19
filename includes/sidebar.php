<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo SITE_URL; ?>/landing/index.php">
            <img src="<?php echo SITE_URL; ?>/assets/images/logo.svg" alt="PetQuest">
        </a>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="<?php echo SITE_URL; ?>/src/dashboard/dashboard.php" class="nav-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="<?php echo SITE_URL; ?>/src/missing/missing-pets.php" class="nav-link">
                <i class="fas fa-search"></i> Missing Pets
            </a>
        </div>
        <div class="nav-item">
            <a href="<?php echo SITE_URL; ?>/src/report/report-pet.php" class="nav-link">
                <i class="fas fa-plus-circle"></i> Report Missing
            </a>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="nav-item">
                <a href="<?php echo SITE_URL; ?>/src/profile/profile.php" class="nav-link">
                    <i class="fas fa-user"></i> My Profile
                </a>
            </div>
            <div class="nav-item">
                <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        <?php else: ?>
            <div class="nav-item">
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="nav-link">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
            <div class="nav-item">
                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="nav-link">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            </div>
        <?php endif; ?>
    </nav>
</div>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle">
    <i class="fas fa-bars"></i>
</button> 