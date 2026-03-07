<?php
require_once 'includes/db.php';

$database = new Database();
$conn = $database->getConnection();

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id > 0) {
    $sql = "SELECT * FROM posts WHERE id = $post_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        $page_title = $post['title'];
        include 'header.php';
        ?>
        
        <article class="single-post">
            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
            
            <div class="post-meta">
                Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
            </div>
            
            <?php if (!empty($post['image'])): ?>
                <div class="featured-image-container">
                    <img src="<?php echo htmlspecialchars($post['image']); ?>" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>"
                         class="post-featured-image">
                    <?php if (!empty($post['image_caption'])): ?>
                        <div class="image-caption"><?php echo htmlspecialchars($post['image_caption']); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
            
            <a href="index.php" class="back-link">← Back to Home</a>
        </article>
        
        <?php
    } else {
        $page_title = 'Post Not Found';
        include 'header.php';
        echo '<p class="error">Post not found.</p>';
    }
} else {
    header('Location: index.php');
    exit();
}

include 'footer.php';
?>