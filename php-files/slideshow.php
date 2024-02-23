<?php
// Previous session and security setup remains the same

session_start();

// You might still want to check if the user is logged in to access this page,
// depending on your application's requirements
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You must be logged in to view files.']);
    exit; // Optional: Depends if you want to restrict this to logged-in users
}

$mysqli = require __DIR__ . "/database_connection.php";

// Modified SQL query to select files from all users
$sql = "SELECT id, filepath, filename FROM uploaded_files";
$result = $mysqli->query($sql);

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = [
        'id' => $row['id'],
        'filepath' => $row['filepath'],
        'filename' => $row['filename']
    ];
}

header('Content-Type: application/json');
echo json_encode($files);
?>