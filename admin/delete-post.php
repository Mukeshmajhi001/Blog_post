<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id > 0) {
    // Pehle image path pata karein
    $sql = "SELECT image FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        
        // Image file delete karein agar exist karti hai
        if (!empty($post['image']) && file_exists('../' . $post['image'])) {
            unlink('../' . $post['image']);
        }
    }
    $stmt->close();
    
    // Ab database se post delete karein
    $sql = "DELETE FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Post deleted successfully!';
    } else {
        $_SESSION['error'] = 'Error deleting post: ' . $conn->error;
    }
    $stmt->close();
}

// Redirect back to admin panel
header('Location: index.php');
exit();
?>