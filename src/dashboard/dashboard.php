<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// If no name field found, try to retrieve it from SESSION
if(!isset($user['name']) && isset($_SESSION['username'])) {
    $user['name'] = $_SESSION['username'];
}

// Get statistics
$stats = [
    'total_pets' => 0,
    'missing_pets' => 0,
    'found_pets' => 0,
    'unread_messages' => 0
];

// Total pets
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM pets WHERE owner_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_pets'] = $result->fetch_assoc()['count'];

// Missing pets
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM pets WHERE owner_id = ? AND status = 'missing'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['missing_pets'] = $result->fetch_assoc()['count'];

// Found pets
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM pets WHERE owner_id = ? AND status = 'found'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['found_pets'] = $result->fetch_assoc()['count'];

// Unread messages
$stmt = $conn->prepare("
    SELECT 
        SUM(owner_unread_count) as count 
    FROM conversations
    WHERE owner_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['unread_messages'] = $result->fetch_assoc()['count'] ?: 0;

// Get recent pets
$stmt = $conn->prepare("
    SELECT p.*, 
           COALESCE((SELECT SUM(owner_unread_count + founder_unread_count) 
                     FROM conversations 
                     WHERE pet_id = p.id), 0) as message_count,
           mr.last_seen_date, 
           mr.last_seen_location
    FROM pets p 
    LEFT JOIN missing_reports mr ON p.id = mr.pet_id
    WHERE p.owner_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 6
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_pets = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PetQuest</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
     <?php include '../../includes/sidebar.php'; ?>
     <?php include '../../includes/dashboard-header.php'; ?>
        <!-- Main Content -->
        <main class="main-content">
           
            <!-- Dashboard Content -->
            <div class="dashboard-content fade-in">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['missing_pets']; ?></h3>
                            <p>Missing Pets</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['found_pets']; ?></h3>
                            <p>Found Pets</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Pets -->
                <div class="section-title">
                    <h2>Recent Activity</h2>
                </div>

                <?php if ($recent_pets->num_rows === 0): ?>
                    <div class="empty-state">
                        <img src="../../assets/images/empty-pets.svg" alt="No pets">
                        <h3>No Pets Found</h3>
                        <p>You haven't reported any pets yet. Click the button below to report a missing pet.</p>
                        <a href="../report/report-pet.php" class="btn btn-primary">Report Missing Pet</a>
                    </div>
                <?php else: ?>
                    <div class="pets-grid">
                        <?php while ($pet = $recent_pets->fetch_assoc()): ?>
                            <div class="pet-card fade-in" data-qr="<?php echo SITE_URL . '/src/details/pet-details.php?id=' . $pet['id']; ?>">
                                <?php if ($pet['image_path']): ?>
                                    <img src="<?php echo SITE_URL . '/' . htmlspecialchars($pet['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($pet['name']); ?>" class="pet-image">
                                <?php else: ?>
                                    <div class="pet-image-placeholder">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="pet-info">
                                    <span class="pet-status status-<?php echo strtolower($pet['status']); ?>">
                                        <?php echo ucfirst($pet['status']); ?>
                                    </span>
                                    <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                                    <?php if ($pet['status'] === 'missing' && $pet['last_seen_date']): ?>
                                        <p><i class="fas fa-calendar"></i> Last seen: <?php echo date('M j, Y', strtotime($pet['last_seen_date'])); ?></p>
                                    <?php endif; ?>
                                    <?php if ($pet['status'] === 'missing' && $pet['last_seen_location']): ?>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($pet['last_seen_location']); ?></p>
                                    <?php endif; ?>
                                    <p><i class="fas fa-comments"></i> <?php echo $pet['message_count']; ?> messages</p>
                                    
                                    <div class="pet-actions">
                                        <a href="../details/pet-details.php?id=<?php echo $pet['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <?php if ($pet['status'] === 'missing'): ?>
                                            <button class="btn btn-success mark-found" data-pet-id="<?php echo $pet['id']; ?>">
                                                <i class="fas fa-check"></i> Mark as Found
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="qr-code-container">
                                    <div id="qr-<?php echo $pet['id']; ?>"></div>
                                    <button class="btn btn-secondary download-qr" data-pet-id="<?php echo $pet['id']; ?>">
                                        <i class="fas fa-qrcode"></i> Save QR
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../../assets/js/qrcode.min.js"></script>
    <script src="../../assets/js/qr-code.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }

    // Mark pet as found
    document.querySelectorAll('.mark-found').forEach(button => {
        button.addEventListener('click', async function() {
            const petId = this.dataset.petId;
            if (confirm('Are you sure you want to mark this pet as found?')) {
                try {
                    const response = await fetch('mark_found.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `pet_id=${petId}`
                    });
                    
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Failed to update pet status. Please try again.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            }
        });
    });

    // Search functionality
    const searchForm = document.querySelector('.search-form');
    const searchInput = searchForm.querySelector('input');
    
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = searchInput.value.trim();
        if (query) {
            window.location.href = `../missing/missing-pets.php?search=${encodeURIComponent(query)}`;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize QR codes
        initQRCodes();
        
        // Rest of your existing JavaScript...
    });
    </script>
</body>
</html> 