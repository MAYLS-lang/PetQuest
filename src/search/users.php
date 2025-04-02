<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$users = [];

if (!empty($search)) {
    $search = "%$search%";
    $stmt = $conn->prepare("SELECT id, name, profile_picture FROM users 
                           WHERE (name LIKE ? OR email LIKE ?) 
                           AND id != ? LIMIT 20");
    $stmt->bind_param("ssi", $search, $search, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Users - PetQuest</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../dashboard/css/dashboard.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/dashboard-header.php'; ?>

    <div class="main-content">
        <div class="search-results">
            <h2>Search Results</h2>
            
            <?php if (empty($users) && !empty($search)): ?>
                <p>No users found matching your search.</p>
            <?php endif; ?>

            <div class="users-grid">
                <?php foreach ($users as $user): ?>
                    <a href="../profile/view.php?id=<?php echo $user['id']; ?>" class="user-card">
                        <img src="../../uploads/profile/<?php echo !empty($user['profile_picture']) ? 
                            htmlspecialchars($user['profile_picture']) : 'default-profile.png'; ?>" 
                            alt="<?php echo htmlspecialchars($user['name']); ?>">
                        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
