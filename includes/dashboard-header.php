<!-- Dashboard Header -->
<header class="dashboard-header">
    <div class="search-container">
        <form action="/PetQuest/src/search/users.php" method="GET">
            <input type="search" name="q" placeholder="Search users...">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        // Get unread messages count first
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("
            SELECT 
                SUM(owner_unread_count) as total_unread,
                c.id,
                c.pet_id,
                c.founder_name as sender_name,
                c.founder_email as sender_email,
                c.owner_unread_count as unread_count,
                c.last_message_time as created_at,
                p.name as pet_name
            FROM conversations c
            JOIN pets p ON c.pet_id = p.id
            WHERE c.owner_id = ?
            GROUP BY c.id
            ORDER BY c.last_message_time DESC
            LIMIT 5
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $unread_messages = $stmt->get_result();
        
        // Get total unread count
        $stmt = $conn->prepare("
            SELECT SUM(owner_unread_count) as total_unread
            FROM conversations
            WHERE owner_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $unread_count = $row['total_unread'] ?: 0;
        ?>
        <div class="header-right">
            <div class="notifications-dropdown">
                <button class="notifications-btn">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-content">
                    <div class="dropdown-header">
                        <h3>Notifications</h3>
                        <?php if ($unread_count > 0): ?>
                            <a href="../messages/chat.php" class="view-all">View All</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notifications-list">
                        <?php if ($unread_count > 0 && $unread_messages->num_rows > 0): ?>
                            <?php while ($conversation = $unread_messages->fetch_assoc()): ?>
                                <a href="../messages/chat.php?conversation=<?php echo $conversation['id']; ?>" class="notification-item">
                                    <div class="notification-icon">
                                        <i class="fas fa-comment"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p class="notification-title">New message about <?php echo htmlspecialchars($conversation['pet_name']); ?></p>
                                        <p class="notification-text">From: <?php echo htmlspecialchars($conversation['sender_name']); ?></p>
                                        <p class="notification-time"><?php echo date('M j, g:i a', strtotime($conversation['created_at'])); ?></p>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-notifications">
                                <i class="fas fa-bell-slash"></i>
                                <p>No new notifications</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="user-menu" id="userMenu">
                <?php
                // Get user information including profile picture and bio
                $stmt = $conn->prepare("SELECT name, profile_picture, bio FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                
                // If no name field found, try to retrieve it from SESSION
                if(!isset($user['name']) && isset($_SESSION['username'])) {
                    $user['name'] = $_SESSION['username'];
                }
                ?>
                
                <img src="<?php echo !empty($user['profile_picture']) ? '/PetQuest/uploads/profile/' . htmlspecialchars($user['profile_picture']) : '/PetQuest/assets/images/default-profile.png'; ?>" 
                     alt="Profile"
                     class="user-avatar">
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars($user['name']); ?></span>
                    <span class="user-role">Pet Owner</span>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="dropdown-menu">
                        <a href="../settings/index.php">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</header>

<script>
document.getElementById('userMenu').addEventListener('click', function() {
    document.getElementById('profileDropdown').classList.toggle('show');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!document.getElementById('userMenu').contains(e.target)) {
        document.getElementById('profileDropdown').classList.remove('show');
    }
});
</script>