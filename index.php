<?php
require_once 'includes/db.php';
$page_title = 'Home';
include 'header.php';

$database = new Database();
$conn = $database->getConnection();

// Fetch all blog posts
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);
?>


<div class="blog-posts">
    <?php if ($result->num_rows > 0): ?>
        <?php while($post = $result->fetch_assoc()): ?>
            <article class="post">
                <?php if (!empty($post['image'])): ?>
                    <div class="post-image">
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>"
                             class="featured-image">
                        <?php if (!empty($post['image_caption'])): ?>
                            <div class="image-caption"><?php echo htmlspecialchars($post['image_caption']); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <h3><a href="single-post.php?id=<?php echo $post['id']; ?>">
                    <?php echo htmlspecialchars($post['title']); ?>
                </a></h3>
                
                <div class="post-meta">
                    Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                </div>
                
                <div class="post-excerpt">
                    <?php 
                    $excerpt = substr(strip_tags($post['content']), 0, 400);
                    echo $excerpt . '...';
                    ?>
                </div>
                
                <a href="single-post.php?id=<?php echo $post['id']; ?>" class="read-more">Read More</a>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No blog posts found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>