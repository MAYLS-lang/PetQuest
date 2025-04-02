<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: ../dashboard/index.php');
    exit();
}

$profile_id = $_GET['id'];
$viewer_id = $_SESSION['user_id'];

// Fetch user profile data
$stmt = $conn->prepare("SELECT id, name, profile_picture, email FROM users WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    header('Location: ../dashboard/index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($profile['name']); ?>'s Profile - PetQuest</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/view-profile.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../dashboard/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/dashboard-header.php'; ?>

    <div class="main-content">
        <div class="profile-container">
            <div class="profile-header">
                <img src="<?php echo !empty($profile['profile_picture']) ? 
                    '../../uploads/profile/' . htmlspecialchars($profile['profile_picture']) : 
                    '../../assets/images/default-profile.png'; ?>" 
                    alt="Profile Picture" class="profile-picture">
                <h2><?php echo htmlspecialchars($profile['name']); ?></h2>
                
                <?php if ($profile_id != $viewer_id): ?>
                    <button class="chat-btn" onclick="startChat(<?php echo $profile_id; ?>)">
                        <i class="fas fa-comment-dots"></i> Message
                    </button>
                <?php endif; ?>
            </div>

            <div class="memories-section">
                <h3>Pet Memories</h3>
                <div class="memories-grid">
                    <?php
                    $memories_stmt = $conn->prepare("SELECT * FROM memories WHERE user_id = ? ORDER BY created_at DESC");
                    $memories_stmt->bind_param("i", $profile_id);
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

    <script>
    function startChat(userId) {
        // Create a new conversation or redirect to existing one
        fetch('../messages/create_conversation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_id=' + userId
        })
        .then(response => response.json())
        .then(data => {
            if (data.conversation_id) {
                window.location.href = '../messages/chat.php?conversation=' + data.conversation_id;
            }
        })
        .catch(error => console.error('Error:', error));
    }
    </script>
</body>
</html>
