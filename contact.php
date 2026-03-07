<?php
$page_title = 'Contact';
include 'header.php';
?>

<h2>Contact Us</h2>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <?php
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);
    
    // Here you can add code to send email or save to database
    ?>
    <div class="success-message">
        Thank you <?php echo $name; ?>! Your message has been sent.
    </div>
<?php endif; ?>

<form method="POST" action="" class="contact-form">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="5" required></textarea>
    </div>
    
    <button type="submit" class="btn">Send Message</button>
</form>

<?php include 'footer.php'; ?>