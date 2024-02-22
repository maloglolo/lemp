<?php
session_start();
$mysqli = require __DIR__ . "/database_connection.php";

if (isset($_POST['file_id']) && isset($_SESSION['user_id'])) {
    $fileId = $_POST['file_id'];
    $userId = $_SESSION['user_id'];
    
    // Validate ownership
    $stmt = $mysqli->prepare("SELECT filepath FROM uploaded_files WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $fileId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        unlink($row['filepath']); // Delete the file from the filesystem
        $stmt = $mysqli->prepare("DELETE FROM uploaded_files WHERE id = ?");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        // Redirect back or indicate success
        header("Location: index.php");
        
        echo "File deleted successfully.";
    } else {
        echo "File not found or you don't have permission to delete it.";
    }
} else {
    echo "Invalid request.";
}
?>
