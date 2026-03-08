<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];

    // Pehle image path pata karein
    $img_sql = "SELECT image FROM posts WHERE id = ?";
    $img_stmt = $conn->prepare($img_sql);
    $img_stmt->bind_param("i", $delete_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();

    if ($img_result->num_rows > 0) {
        $post_data = $img_result->fetch_assoc();
        // Image file delete karein agar exist karti hai
        if (!empty($post_data['image']) && file_exists('../' . $post_data['image'])) {
            unlink('../' . $post_data['image']);
        }
    }
    $img_stmt->close();

    // Database se post delete karein
    $delete_sql = "DELETE FROM posts WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);

    if ($delete_stmt->execute()) {
        $_SESSION['message'] = 'Post deleted successfully!';
        $_SESSION['msg_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error deleting post: ' . $conn->error;
        $_SESSION['msg_type'] = 'error';
    }
    $delete_stmt->close();

    header('Location: index.php');
    exit();
}

// Fetch all posts
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>polished header · MUBIK</title>
    <!-- Font Awesome for icons (already used, keeping it) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/index_style.css">
</head>

<body>
    <!-- Polished header (replaces old admin-header) placed at top -->
    <div class="main">
        <!-- logo image: fallback kept -->
        <img class="logo" src="blue-simple-business-card-design_1051-632.avif"
            alt="MUBIK brand logo — abstract blue business card style"
            onerror="this.onerror=null; this.src='logo/logo.png';">


        <h1> <span>
                <i class="fas fa-user"></i>
                <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>
            </span></h1>

        <!-- dynamic admin links styled as buttons, preserving original functionality -->
        <a class="button" id="button1" href="add-post.php"><i class="fas fa-plus-circle"></i> New Post</a>
        <a class="button" href="../index.php" ><i class="fas fa-external-link-alt"></i> View Site</a>
        <a class="button" href="logout.php" style="background-color:#dc3545;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-container">
        <div class="container">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>" id="messageAlert">
                    <span>
                        <i class="fas fa-<?php echo $_SESSION['msg_type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        unset($_SESSION['msg_type']);
                        ?>
                    </span>
                    <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Posts</h4>
                        <div class="stat-number"><?php echo $result->num_rows; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>With Images</h4>
                        <div class="stat-number">
                            <?php
                            $img_count = 0;
                            if ($result->num_rows > 0) {
                                $result->data_seek(0);
                                while ($row = $result->fetch_assoc()) {
                                    if (!empty($row['image'])) $img_count++;
                                }
                                $result->data_seek(0);
                            }
                            echo $img_count;
                            ?>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-images"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Last Updated</h4>
                        <div class="stat-number">
                            <?php
                            if ($result->num_rows > 0) {
                                $result->data_seek(0);
                                $first = $result->fetch_assoc();
                                echo date('M d', strtotime($first['created_at']));
                                $result->data_seek(0);
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <h3>
                    <i class="fas fa-list"></i>
                    Manage Blog Posts
                </h3>
                <a href="add-post.php" class="add-new-btn">
                    <i class="fas fa-plus-circle"></i>
                    Add New Post
                </a>
            </div>

            <!-- Posts Table -->
            <div class="table-container">
                <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title & Caption</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result->data_seek(0);
                            while ($post = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><span class="date-badge">#<?php echo $post['id']; ?></span></td>
                                    <td>
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($post['image']); ?>"
                                                alt="Post image"
                                                class="post-thumb"
                                                onerror="this.onerror=null; this.src='../images/default-image.jpg';">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="post-title">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                            <?php if (!empty($post['image_caption'])): ?>
                                                <br><small><i class="fas fa-quote-left"></i> <?php echo htmlspecialchars($post['image_caption']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="date-badge">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="../single-post.php?id=<?php echo $post['id']; ?>"
                                                class="action-btn btn-view"
                                                 
                                                title="View Post">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit-post.php?id=<?php echo $post['id']; ?>"
                                                class="action-btn btn-edit"
                                                title="Edit Post">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)"
                                                onclick="confirmDelete(<?php echo $post['id']; ?>, '<?php echo addslashes($post['title']); ?>')"
                                                class="action-btn btn-delete"
                                                title="Delete Post">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-newspaper"></i>
                        <p>No blog posts yet. Create your first post!</p>
                        <a href="add-post.php" class="add-first-btn">
                            <i class="fas fa-plus-circle"></i>
                            Create First Post
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete post: <strong id="postTitle"></strong>?</p>
                <p style="color: #dc3545; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i>
                    This action cannot be undone!
                </p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn btn-cancel" onclick="closeModal()">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="modal-btn btn-confirm">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </div>
        </div>
    </div>

    <script>
        // Delete confirmation modal
        function confirmDelete(postId, postTitle) {
            document.getElementById('postTitle').textContent = postTitle;
            document.getElementById('confirmDeleteBtn').href = 'index.php?action=delete&id=' + postId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Auto hide message after 5 seconds
        setTimeout(function() {
            const alert = document.getElementById('messageAlert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);

        // Keyboard shortcut: Press 'n' for new post
        document.addEventListener('keydown', function(e) {
            if (e.key === 'n' && e.ctrlKey) {
                e.preventDefault();
                window.location.href = 'add-post.php';
            }
        });
    </script>
</body>

</html>