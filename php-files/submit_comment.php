<?php
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax' // or 'Strict'
]);
session_start();  

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    exit(json_encode(['success' => false, 'message' => 'You must be logged in to submit a comment.']));
}

// Check if comment content and image ID are provided
if (!isset($_POST["commentContent"]) || !isset($_POST["imageId"])) {
    exit(json_encode(['success' => false, 'message' => 'Please provide comment content and image ID.']));
}

$userId = $_SESSION["user_id"];
$commentContent = $_POST["commentContent"];
$imageId = $_POST["imageId"]; // Retrieve the image ID from the form submission

// Database connection
$mysqli = require __DIR__ . "/database_connection.php";

// Check if the image exists
$stmt = $mysqli->prepare("SELECT id FROM uploaded_files WHERE id = ?");
$stmt->bind_param("i", $imageId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exit(json_encode(['success' => false, 'message' => 'The specified image does not exist.']));
}

// Update the SQL statement to include image_id
$sql = "INSERT INTO comments (user_id, content, image_id) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("isi", $userId, $commentContent, $imageId); // Bind the image ID as an integer

if ($stmt->execute()) {
    // Display a popup confirmation
    echo '<script>alert("Comment successfully added!");</script>';
    // Redirect to index.php after a short delay
    echo '<script>window.setTimeout(function(){ window.location.href = "index.php"; }, 1000);</script>';
    exit;
} else {
    exit(json_encode(['success' => false, 'message' => 'Error adding comment.']));
}
?>
