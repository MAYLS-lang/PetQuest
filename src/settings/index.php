<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$stmt = $conn->prepare("SELECT *, bio FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings - PetQuest</title>
    <link rel="stylesheet" href="css/settings.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../dashboard/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/dashboard-header.php'; ?>
    
    <div class="main-content">
        <div class="settings-container">
            <h2>Account Settings</h2>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                <div class="profile-image-group">
                    <img src="<?php echo !empty($user['profile_picture']) ? '../../uploads/profile/' . htmlspecialchars($user['profile_picture']) : '../../assets/images/default-profile.png'; ?>" 
                         alt="Profile Picture" 
                         class="current-profile-image">
                    
                    <div class="form-group profile-image-input">
                        <label for="profile_picture">Change Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                        <small class="form-text">Allowed formats: JPG, JPEG, PNG, GIF (Max size: 5MB)</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4" 
                              placeholder="Tell us about yourself and your pets..."
                    ><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    <small class="form-text">Maximum 500 characters</small>
                </div>
                
                <button type="submit" name="update_profile">Update Profile</button>
            </form>

            <div class="password-section">
                <h3>Change Password</h3>
                <form action="update_password.php" method="POST">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <ul class="password-requirements">
                        <li>At least 8 characters long</li>
                        <li>Contains at least one uppercase letter</li>
                        <li>Contains at least one number</li>
                        <li>Contains at least one special character</li>
                    </ul>
                    
                    <button type="submit" name="update_password">Change Password</button>
                </form>
            </div>
        </div>
    </div>
    <script src="js/settings.js"></script>
</body>
</html>
