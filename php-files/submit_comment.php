<?php
session_start();

// Check if the user is logged in, the comment content is provided, and the image ID is provided
if (!isset($_SESSION["user_id"]) || !isset($_POST["commentContent"]) || !isset($_POST["imageId"])) {
    exit("You must be logged in and provide all required fields to post comments.");
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
    exit("The specified image does not exist.");
}

// Update the SQL statement to include image_id
$sql = "INSERT INTO comments (user_id, content, image_id) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("isi", $userId, $commentContent, $imageId); // Bind the image ID as an integer

if ($stmt->execute()) {
    ob_start(); // Start output buffering
    echo "Comment successfully added!";
    ob_end_clean(); // Clean (discard) the output buffer
    header('Location: confirmation.php');
    exit(); // Ensure that no further code is executed after redirection
} else {
    echo "Error: " . $mysqli->error;
}
?>
