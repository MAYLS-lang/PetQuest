<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

// Check if user is logged in

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT *, bio FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile - PetQuest</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../dashboard/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/dashboard-header.php'; ?>
    
    <div class="main-content">
        <div class="profile-container">
            <div class="profile-header">
                <img src="<?php echo !empty($user['profile_picture']) ? '../../uploads/profile/' . htmlspecialchars($user['profile_picture']) : '../../assets/images/default-profile.png'; ?>" 
                     alt="Profile Picture" 
                     class="profile-image">
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="bio">
                        <?php 
                        if (!empty($user['bio'])) {
                            echo htmlspecialchars($user['bio']);
                        } else {
                            echo '<span class="empty-bio">No bio added yet. <a href="../settings/index.php">Add one now</a></span>';
                        }
                        ?>
                    </p>
                </div>
            </div>

            <div class="memories-section">
                <h3>Pet Memories</h3>
                <div class="memories-grid">
                    <?php
                    $memories_stmt = $conn->prepare("SELECT * FROM memories WHERE user_id = ? ORDER BY created_at DESC");
                    $memories_stmt->bind_param("i", $_SESSION['user_id']);
                    $memories_stmt->execute();
                    $memories = $memories_stmt->get_result();
                    
                    while ($memory = $memories->fetch_assoc()):
                    ?>
                        <div class="memory-card">
                            <img src="../../uploads/memories/<?php echo htmlspecialchars($memory['image_path']); ?>" alt="Pet Memory">
                            <div class="memory-overlay">
                                <span class="memory-date"><?php echo date('M d, Y', strtotime($memory['created_at'])); ?></span>
                                <p><?php echo htmlspecialchars($memory['description']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="js/profile.js"></script>
</body>
</html>
