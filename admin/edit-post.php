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

$message = '';
$error = '';

// Image upload settings
$target_dir = "../uploads/posts/";
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 10 * 1024 * 1024; // 10MB

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    header('Location: index.php');
    exit();
}

// Fetch post data
$sql = "SELECT * FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit();
}

$post = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_caption = trim($_POST['image_caption']);
    $image_path = $post['image']; // Keep existing image by default
    $remove_image = isset($_POST['remove_image']) ? true : false;
    
    // Validation
    if (empty($title) || empty($content)) {
        $error = 'Please fill in all fields';
    } else {
        // Handle image upload if new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $file = $_FILES['image'];
            $file_type = $file['type'];
            $file_size = $file['size'];
            
            // Validate file type
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Only JPG, PNG, GIF and WEBP images are allowed';
            }
            // Validate file size
            elseif ($file_size > $max_size) {
                $error = 'Image size should be less than 5MB';
            }
            else {
                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Upload file
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    // Delete old image if exists
                    if (!empty($post['image']) && file_exists('../' . $post['image'])) {
                        unlink('../' . $post['image']);
                    }
                    $image_path = 'uploads/posts/' . $new_filename;
                } else {
                    $error = 'Error uploading image';
                }
            }
        }
        
        // Handle image removal
        if ($remove_image && !empty($post['image'])) {
            if (file_exists('../' . $post['image'])) {
                unlink('../' . $post['image']);
            }
            $image_path = null;
        }
        
        // If no error, update database
        if (empty($error)) {
            $sql = "UPDATE posts SET title = ?, image = ?, image_caption = ?, content = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $image_path, $image_caption, $content, $post_id);
            
            if ($stmt->execute()) {
                $message = 'Post updated successfully!';
                // Refresh post data
                $post['title'] = $title;
                $post['content'] = $content;
                $post['image'] = $image_path;
                $post['image_caption'] = $image_caption;
            } else {
                $error = 'Error updating post: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Same styles as add-post.php plus these additional styles */
        .current-image {
            margin: 15px 0;
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .current-image img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 3px;
        }
        .current-image p {
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .remove-image {
            margin-top: 10px;
        }
        .remove-image label {
            display: inline !important;
            font-weight: normal !important;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-form">
            <h2 class="form-title">Edit Post</h2>
            
            <div class="post-info">
                <strong>Post ID:</strong> <?php echo $post['id']; ?> | 
                <strong>Created:</strong> <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
            </div>
            
            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Post Title:</label>
                    <input type="text" id="title" name="title" 
                           value="<?php echo htmlspecialchars($post['title']); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label>Current Image:</label>
                    <?php if (!empty($post['image'])): ?>
                        <div class="current-image">
                            <img src="../<?php echo htmlspecialchars($post['image']); ?>" 
                                 alt="Current featured image">
                            <?php if (!empty($post['image_caption'])): ?>
                                <p>Caption: <?php echo htmlspecialchars($post['image_caption']); ?></p>
                            <?php endif; ?>
                            <div class="remove-image">
                                <input type="checkbox" id="remove_image" name="remove_image">
                                <label for="remove_image">Remove current image</label>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>No image currently</p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="image">Change Image (Optional):</label>
                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                    <div class="file-info">Allowed: JPG, PNG, GIF, WEBP (Max: 10MB)</div>
                    <div id="imagePreview" class="image-preview">
                        <img src="" alt="New Image Preview">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image_caption">Image Caption:</label>
                    <input type="text" id="image_caption" name="image_caption" 
                           value="<?php echo htmlspecialchars($post['image_caption'] ?? ''); ?>"
                           placeholder="Brief description of the image">
                </div>
                
                <div class="form-group">
                    <label for="content">Post Content:</label>
                    <textarea id="content" name="content" rows="12" 
                              required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="update-btn">Update Post</button>
                    <a href="index.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const previewImg = preview.querySelector('img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
            previewImg.src = '';
        }
    }
    </script>
</body>
</html>