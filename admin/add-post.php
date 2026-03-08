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

// Create upload directory if not exists
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_caption = trim($_POST['image_caption']);
    $image_path = '';
    
    // Validation
    if (empty($title) || empty($content)) {
        $error = 'Please fill in all required fields';
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $file = $_FILES['image'];
            $file_type = $file['type'];
            $file_size = $file['size'];
            $file_error = $file['error'];
            $file_tmp = $file['tmp_name'];
            
            // Validate file type
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Only JPG, PNG, GIF and WEBP images are allowed';
            }
            // Validate file size
            elseif ($file_size > $max_size) {
                $error = 'Image size should be less than 10MB';
            }
            else {
                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Upload file
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_path = 'uploads/posts/' . $new_filename;
                } else {
                    $error = 'Error uploading image';
                }
            }
        }
        
        // If no error, insert into database
        if (empty($error)) {
            $sql = "INSERT INTO posts (title, image, image_caption, content) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $title, $image_path, $image_caption, $content);
            
            if ($stmt->execute()) {
                $message = 'Post added successfully with image!';
                // Clear form fields
                $title = $content = $image_caption = '';
            } else {
                $error = 'Error adding post: ' . $conn->error;
                // Delete uploaded image if database insert fails
                if (!empty($image_path) && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
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
    <title>Add New Post with Image</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/add_post_style.css">
</head>
<body>
    <div class="container">
        <div class="admin-form">
            <h2 class="form-title">Add New Blog Post with Image</h2>
            
            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title" class="required">Post Title:</label>
                    <input type="text" id="title" name="title" 
                           value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="image">Featured Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                    <div class="file-info">Allowed: JPG, PNG, GIF, WEBP (Max: 10MB)</div>
                    <div id="imagePreview" class="image-preview">
                        <img src="" alt="Image Preview">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image_caption">Image Caption (Optional):</label>
                    <input type="text" id="image_caption" name="image_caption" 
                           value="<?php echo isset($image_caption) ? htmlspecialchars($image_caption) : ''; ?>"
                           placeholder="Brief description of the image">
                </div>
                
                <div class="form-group">
                    <label for="content" class="required">Post Content:</label>
                    <textarea id="content" name="content" rows="12" 
                              required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="submit-btn">Publish Post</button>
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