<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    // Check if email exists
    $database = new Database();
    $conn = $database->getConnection();
    
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Generate reset token (in real project, send email)
        $token = bin2hex(random_bytes(32));
        $success = "Password reset link sent to your email!";
    } else {
        $error = "Email not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .auth-container { max-width: 400px; margin: 100px auto; padding: 30px; background: white; border-radius: 5px; }
        .form-group { margin-bottom: 20px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; }
        .btn-primary { width: 100%; background: #007bff; color: white; padding: 12px; border: none; cursor: pointer; }
        .auth-links { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <h2 class="auth-title">Forgot Password</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Enter your email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button type="submit" class="btn-primary">Send Reset Link</button>
            </form>
            
            <div class="auth-links">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>