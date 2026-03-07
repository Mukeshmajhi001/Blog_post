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
    <style>
        /* overall page comfort – now pushes .main to the top */
        body {
            margin-top: auto;
            min-height: 100vh;
            /* keep full viewport height */
            background: #f0f2f5;
            /* original admin background */
            font-family: 'Segoe UI', Roboto, system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            /* stack header and main content */
            align-items: center;
            padding: 0;
            /* remove side padding, handled by containers */
            box-sizing: border-box;
        }

        /* main header card – refined version, now placed at top */
        .main {
            width: 100%;
            max-width: 1400px;
            /* slightly wider to match admin */
            background-color: #d6f5f0;
            /* soft original background */
            border-radius: 0 0 32px 32px;
            /* rounded only at bottom */
            padding: 0.6rem 2rem 0.6rem 2rem;
            margin: 0 0 20px 0;
            /* no top margin, sits flush */
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem 1.2rem;
            box-shadow: 0 12px 28px -8px rgba(1, 58, 67, 0.25),
                0 4px 12px rgba(0, 80, 90, 0.15);
            backdrop-filter: blur(2px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-top: none;
            /* no top border */
            box-sizing: border-box;
        }

        /* circular logo with a subtle inner glow */
        .logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 10px rgba(0, 60, 70, 0.3);
            transition: transform 0.25s ease;
            background-color: #b0e0db;
            /* fallback color */
        }

        .logo:hover {
            transform: scale(1.02) rotate(1deg);
        }

        /* brand name with original fancy unicode */
        .name {
            font-size: 2.1rem;
            color: #05313b;
            margin: 0;
            font-weight: 500;
            letter-spacing: 1px;
            font-family: 'Brush Script MT', 'Segoe Script', 'Lucida Handwriting', cursive, sans-serif;
            text-shadow: 2px 2px 0 rgba(255, 255, 255, 0.7);
            line-height: 1.2;
        }

        /* push first button with auto-margin, but only when enough space */
        #button1 {
            margin-left: auto;
        }

        /* unified button style — modern & friendly */
        .button {
            padding: 10px 24px;
            color: white;
            background-color: #0c6b7e;
            text-decoration: none;
            border-radius: 60px;
            font-size: 1.05rem;
            font-weight: 550;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 14px -8px #05313b80, 0 2px 4px #c0fcf0 inset;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .button:hover {
            background-color: #024e5e;
            box-shadow: 0 10px 18px -6px #02262e;
            transform: translateY(-2px);
            border-color: white;
        }

        .button:active {
            transform: translateY(2px);
            box-shadow: 0 4px 8px -2px #001f26;
        }

        /* second button gets a slightly different hue */
        .button:last-child {
            background-color: #1f8a9c;
        }

        .button:last-child:hover {
            background-color: #086277;
        }

        /* responsive adjustments for the new header inside admin */
        @media (max-width: 650px) {
            .main {
                border-radius: 0 0 40px 40px;
                justify-content: center;
                padding: 1rem 1.5rem;
            }

            #button1 {
                margin-left: 0;
            }

            .name {
                font-size: 2rem;
                margin-right: auto;
            }
        }

        @media (max-width: 480px) {
            .main {
                flex-direction: column;
                text-align: center;
            }

            .name {
                margin-left: 0;
            }

            .button {
                width: 100%;
                white-space: normal;
            }
        }

        /* ----- original admin styles below (slightly adapted to fit new header) ----- */
        .admin-header {
            display: none;
            /* hiding old header – we replace with .main */
        }

        /* Main Container */
        .main-container {
            padding: 0 20px 30px 20px;
            /* top padding removed because header handles it */
            width: 100%;
            max-width: 1400px;
            box-sizing: border-box;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* Action Bar */
        .action-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .action-bar h3 {
            color: #333;
            font-size: 20px;
        }

        .action-bar h3 i {
            color: #667eea;
            margin-right: 10px;
        }

        .add-new-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: transform 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-new-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Message Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-info h4 {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .stat-info .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .stat-icon {
            font-size: 40px;
            color: #667eea;
            opacity: 0.6;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        tr:hover {
            background: #f8f9fa;
        }

        /* Post Image in Table */
        .post-thumb {
            width: 60px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .no-image {
            width: 60px;
            height: 50px;
            background: #f0f0f0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #999;
        }

        /* Post Title */
        .post-title {
            font-weight: 600;
            color: #333;
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .post-title small {
            font-weight: normal;
            color: #666;
            font-size: 12px;
        }

        /* Date Badge */
        .date-badge {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            color: #495057;
            display: inline-block;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            cursor: pointer;
        }

        .action-btn i {
            font-size: 14px;
        }

        .btn-view {
            background: #17a2b8;
            color: white;
        }

        .btn-view:hover {
            background: #138496;
            transform: translateY(-2px);
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
        }

        .btn-edit:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px;
        }

        .empty-state i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: #666;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .empty-state .add-first-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            display: inline-block;
        }

        /* Modal for Delete Confirmation */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            color: #dc3545;
            font-size: 24px;
        }

        .close-modal {
            font-size: 30px;
            cursor: pointer;
            color: #999;
        }

        .close-modal:hover {
            color: #333;
        }

        .modal-body {
            margin-bottom: 30px;
            font-size: 16px;
            color: #666;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-confirm {
            background: #dc3545;
            color: white;
        }

        .btn-confirm:hover {
            background: #c82333;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background: #545b62;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .action-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-buttons {
                flex-direction: column;
            }

            td {
                padding: 10px;
            }

            .post-thumb {
                width: 40px;
                height: 35px;
            }
        }

        .main span {
            display: inline-block;
            background-color: #f0f0f0;
            padding: 8px 15px;
            border-radius: 20px;
            margin: 10px 0;
            font-size: 14px;
            color: #333;
        }

        .main span i {
            margin-right: 5px;
            color: #388b9c;
        }
    </style>
</head>

<body>
    <!-- Polished header (replaces old admin-header) placed at top -->
    <div class="main">
        <!-- logo image: fallback kept -->
        <img class="logo" src="blue-simple-business-card-design_1051-632.avif"
            alt="MUBIK brand logo — abstract blue business card style"
            onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2270%22%20height%3D%2270%22%20viewBox%3D%220%200%2070%2070%22%3E%3Ccircle%20cx%3D%2235%22%20cy%3D%2235%22%20r%3D%2233%22%20fill%3D%22%23388b9c%22%20stroke%3D%22%23ffffff%22%20stroke-width%3D%223%22%2F%3E%3Ctext%20x%3D%2235%22%20y%3D%2248%22%20font-size%3D%2230%22%20text-anchor%3D%22middle%22%20fill%3D%22%23f0fcf9%22%20font-family%3D%22Arial%22%3EM%3C%2Ftext%3E%3C%2Fsvg%3E';">


        <h1> <span>
                <i class="fas fa-user"></i>
                <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>
            </span></h1>

        <!-- dynamic admin links styled as buttons, preserving original functionality -->
        <a class="button" id="button1" href="add-post.php"><i class="fas fa-plus-circle"></i> New Post</a>
        <a class="button" href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
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
                                                target="_blank"
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