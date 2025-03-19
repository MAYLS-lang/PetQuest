
<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_landing = ($current_page === 'index.php' && strpos($_SERVER['REQUEST_URI'], '/landing/') !== false);

if (!$is_landing):
?>
<nav class="navbar">
    <div class="container">
        <div class="logo">
            <a href="<?php echo SITE_URL; ?>/landing/index.php" style="text-decoration: none;">
                <h1>PetQuest</h1>
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="<?php echo SITE_URL; ?>/landing/index.php">Home</a></li>
            <li><a href="<?php echo SITE_URL; ?>/missing-pets.php">Missing Pets</a></li>
            <li><a href="<?php echo SITE_URL; ?>/report-pet.php">Report Missing</a></li>
            <?php
            if(isset($_SESSION['user_id'])) {
                echo '<li><a href="' . SITE_URL . '/dashboard.php">Dashboard</a></li>';
                echo '<li><a href="' . SITE_URL . '/auth/logout.php">Logout</a></li>';
            } else {
                echo '<li><a href="' . SITE_URL . '/auth/login.php">Login</a></li>';
                echo '<li><a href="' . SITE_URL . '/auth/register.php">Register</a></li>';
            }
            ?>
        </ul>
    </div>
</nav>
<?php endif; ?> 