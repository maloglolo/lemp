<?php
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax' // or 'Strict'
]);
session_start();  

if (isset($_SESSION["user_id"])) {
    $mysqli = require __DIR__ . "/database_connection.php";
    $sql = "SELECT * FROM user
                WHERE id = {$_SESSION["user_id"]}";
    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
     <?php if (isset($_SESSION['user_id'])): ?>
        <form action="logout.php" method="post" style="text-align: right;">
            <button type="submit">Logout</button>
        </form>
    <?php endif; ?>

    <h1>Home</h1>

    <?php if (isset($user)): ?>

        <p>Hello <?= htmlspecialchars($user["name"])?> ! </p>
        <form action="upload.php" method="post" enctype="multipart/form-data">
             Select image to upload:
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Upload Image" name="submit">
        </form>
<?php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to view files.');
}
// Get info from the db
$userId = $_SESSION['user_id'];
$result = $mysqli->query("SELECT id, filepath, filesize, filename, uploaded_at, user_ip FROM uploaded_files WHERE user_id = $userId");
// Display file info and delete script
while ($row = $result->fetch_assoc()) {
    $filepathEscaped = htmlspecialchars($row['filepath']);
    $filenameEscaped = htmlspecialchars($row['filename']);
    $filesizeMB = number_format($row['filesize'] / 1048576, 2); // Format the file size to KB with 2 decimal places
    $uploadedAtEscaped = htmlspecialchars($row['uploaded_at']);
    $userIp = htmlspecialchars($row['user_ip']); // Retrieve and escape the user's IP address   
    echo '<div>';
    // Make the thumbnail a clickable link that opens the full-size image in a new tab
    echo '<a href="' . $filepathEscaped . '" target="_blank">';
    echo '<img src="' . $filepathEscaped . '" alt="' . $filenameEscaped . '" style="width: 100px; height: auto;" />';
    echo '</a>';
    echo '<p>Filename: ' . $filenameEscaped . '</p>';
    echo '<p>Size: ' . $filesizeMB . ' MB</p>';
    echo '<p>Uploaded: ' . $uploadedAtEscaped . '</p>';
    echo '<p>User IP: ' . $userIp . '</p>'; // Display the user's IP address
    
    // Link to the confirmation page instead of a direct form submission
    echo '<a href="confirm_delete.php?file_id=' . $row['id'] . '" style="color: red;">Delete</a>';    
    echo '</div>';
}   
        else: ?>

        <p><a href="login.php">Login in</a> or <a href="signup.html">sign up</a></p>
<?php endif; ?>
</body>
</html>