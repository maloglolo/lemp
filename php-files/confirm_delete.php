<?php
$fileId = $_GET['file_id'] ?? null; // Get the file ID from the URL

if (!$fileId) {
    echo "File ID is required.";
    exit;
}

// You can add additional checks here to ensure the file exists or belongs to the user
?>
<head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head> 

<h2>Are you sure you want to delete this file?</h2>

<form action="delete_file.php" method="post">
    <input type="hidden" name="file_id" value="<?php echo htmlspecialchars($fileId); ?>">
    <input type="submit" value="Yes, delete it!">
</form>

<a href="index.php">No, take me back</a>