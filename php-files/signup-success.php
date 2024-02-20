<?php
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
session_start();
session_regenerate_id(); 
    if (isset($_SESSION['user_hash'])) {

    $user_hash = $_SESSION['user_hash']; 
    
    }   else {

    echo "Hash not found."; // Handle cases where the hash is not set.
    exit;    
}
session_destroy();
#var_dump($user_hash);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Signup</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>

<body>
    <h1>Signup</h1>

    <p>Your unique recovery code is: <?php echo htmlspecialchars($user_hash); ?><br>
        <br>Keep this key in a safe place since it is used to rest your password!<br>
    <p>Signup successful.</p>
        You can now <a href="login.php">login</a>.</p>

</body>
</html>