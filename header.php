<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_TITLE; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- overall page comfort – now pushes .main to the top -->
    <div class="main">
        <!-- logo image: kept original filename, but added reliable fallback & description -->
        <img class="logo" src="admin/logo/logo.png" 
             alt="MUBIK brand logo — abstract blue business card style"
             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2270%22%20height%3D%2270%22%20viewBox%3D%220%200%2070%2070%22%3E%3Ccircle%20cx%3D%2235%22%20cy%3D%2235%22%20r%3D%2233%22%20fill%3D%22%23388b9c%22%20stroke%3D%22%23ffffff%22%20stroke-width%3D%223%22%2F%3E%3Ctext%20x%3D%2235%22%20y%3D%2248%22%20font-size%3D%2230%22%20text-anchor%3D%22middle%22%20fill%3D%22%23f0fcf9%22%20font-family%3D%22Arial%22%3EM%3C%2Ftext%3E%3C%2Fsvg%3E';">

        <h1 class="name">𝓜𝓤𝓑𝓘𝓚</h1>

        <!-- navigation buttons with PHP links -->
        <a class="button" id="button1" href="index.php" target="_blank" rel="noopener">home</a>
        <a class="button" href="admin/login.php" target="_blank" rel="noopener">Admin Login</a>
      
    </div>

    <!-- micro explanation (invisible, just for structure completeness) 
         all original classes preserved: .main, .logo, .name, #button1, .button -->
</body>
</html>