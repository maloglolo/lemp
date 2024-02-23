<?php
if (isset($_GET['imageId'])) {
    $imageId = $_GET['imageId'];

    $mysqli = require __DIR__ . "/database_connection.php";

    $stmt = $mysqli->prepare("SELECT comments.content, comments.created_at, user.name FROM comments JOIN user ON comments.user_id = user.id WHERE comments.image_id = ? ORDER BY comments.created_at DESC");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($comments);
}
?>