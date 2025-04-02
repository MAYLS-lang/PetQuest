<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $bio = trim($_POST['bio']);
    
    // First update bio
    $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
    $stmt->bind_param("si", $bio, $user_id);
    
    if (!$stmt->execute()) {
        header('Location: index.php?error=Failed to update bio');
        exit();
    }

    // Handle profile picture upload if provided
    if (!empty($_FILES['profile_picture']['name'])) {
        $file = $_FILES['profile_picture'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            header('Location: index.php?error=Upload failed: ' . $file['error']);
            exit();
        }

        if ($file['size'] > $maxFileSize) {
            header('Location: index.php?error=File size too large. Maximum size is 5MB');
            exit();
        }

        $uploadDir = '../../uploads/profile/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($file['name']));
        $targetPath = $uploadDir . $fileName;

        $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $validExtensions)) {
            header('Location: index.php?error=Invalid file type. Allowed types: JPG, JPEG, PNG, GIF');
            exit();
        }

        if (!getimagesize($file['tmp_name'])) {
            header('Location: index.php?error=Invalid image file');
            exit();
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Get old profile picture
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $oldPicture = $stmt->get_result()->fetch_assoc()['profile_picture'];

            // Update database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $fileName, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                // Delete old profile picture if it exists and is not the default
                if ($oldPicture && $oldPicture !== 'default-profile.png') {
                    $oldPath = $uploadDir . $oldPicture;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                header('Location: index.php?success=Profile picture updated successfully');
            } else {
                unlink($targetPath); // Delete uploaded file if database update fails
                header('Location: index.php?error=Database update failed');
            }
        } else {
            header('Location: index.php?error=Failed to upload file');
        }
    }

    header('Location: index.php?success=Profile updated successfully!');
    exit();
}
