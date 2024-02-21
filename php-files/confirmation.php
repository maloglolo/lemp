<?php
session_start();
$message = $_SESSION['upload_status'] ?? 'Operation completed.';
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <meta http-equiv="refresh" content="5;url=index.php">
</head>
<body>
    <p><?php echo htmlspecialchars($message); ?></p>
</body>
</html>