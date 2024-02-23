<?php
// Set session ini settings before starting the session
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax' // or 'Strict'
]);

// Start the session
session_start();  

// Check if the user is logged in
if (isset($_SESSION["user_id"])) {
    $mysqli = require __DIR__ . "/database_connection.php";
    
    // Fetch user details
    $sql = "SELECT * FROM user WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $_SESSION["user_id"]);
    if (!$stmt->execute()) {
        exit("Error fetching user details: " . $stmt->error);
    }
    $userResult = $stmt->get_result();
    $user = $userResult->fetch_assoc();

    // Retrieve image ID associated with the user
    $imageId = null;
    $sql = "SELECT id FROM uploaded_files WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $_SESSION["user_id"]);
    if (!$stmt->execute()) {
        exit("Error fetching image ID: " . $stmt->error);
    }
    $imageResult = $stmt->get_result();
    if ($imageResult->num_rows > 0) {
        $imageRow = $imageResult->fetch_assoc();
        $imageId = $imageRow["id"];
    }
}

// Define the response array
$response = array();

// Handle file deletion request
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
        
        if ($stmt->execute()) {
            // Set success response
            $response['success'] = true;
            $response['message'] = "File successfully deleted.";
        } else {
            // Set failure response
            $response['success'] = false;
            $response['message'] = "Error deleting file from database.";
        }
    } else {
        // Set failure response
        $response['success'] = false;
        $response['message'] = "File not found or you don't have permission to delete it.";
    }
    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script src="script.js"></script> 
    <script>
        function confirmDelete(fileId) {
            if (confirm("Are you sure you want to delete this file?")) {
                // Make an AJAX request to delete the file
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "profile.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Remove the deleted file element from the DOM
                            var fileElement = document.getElementById('file_' + fileId);
                            if (fileElement) {
                                fileElement.parentNode.removeChild(fileElement);
                            }
                        }
                        alert(response.message);
                    }
                };
                xhr.send("file_id=" + fileId);
            }
        }
    </script>
        <style>
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            padding: 12px 16px;
            z-index: 1;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content form,
        .dropdown-content a {
            margin: 5px 0;
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
    <div class="dropdown" style="float: right;">
        <button>Menu</button>
        <div class="dropdown-content">
            <form action="logout.php" method="post">
                <button type="submit">Logout</button>
            </form>
            <a href="profile.php" style="text-decoration: none;">
                <button>Profile</button>
            </a>
            <a href="index.php" style="text-decoration: none;">
                <button>Home</button>
            </a>
        </div>
    </div>
<?php endif; ?>

</div>

<?php
// DISPLAY FILEINFO AND DELETE
$userId = $_SESSION['user_id'];
$result = $mysqli->query("SELECT id, filepath, filesize, filename, uploaded_at, user_ip FROM uploaded_files WHERE user_id = $userId");

// Display file info and delete button
while ($row = $result->fetch_assoc()) {
    $fileId = $row['id'];
    $filepathEscaped = htmlspecialchars($row['filepath']);
    $filenameEscaped = htmlspecialchars($row['filename']);
    $filesizeMB = number_format($row['filesize'] / 1048576, 2); // Format the file size to KB with 2 decimal places
    $uploadedAtEscaped = htmlspecialchars($row['uploaded_at']);
    $userIp = htmlspecialchars($row['user_ip']); // Retrieve and escape the user's IP address   
    echo '<div id="file_' . $fileId . '">';
    // Make the thumbnail a clickable link that opens the full-size image in a new tab
    echo '<a href="' . $filepathEscaped . '" target="_blank">';
    echo '<img src="' . $filepathEscaped . '" alt="' . $filenameEscaped . '" style="width: 100px; height: auto;" />';
    echo '</a>';
    echo '<p>Filename: ' . $filenameEscaped . '</p>';
    echo '<p>Size: ' . $filesizeMB . ' MB</p>';
    echo '<p>Uploaded: ' . $uploadedAtEscaped . '</p>';
    echo '<p>User IP: ' . $userIp . '</p>'; // Display the user's IP address
    echo '<button style="color: red;" onclick="confirmDelete(' . $fileId . ')">Delete</button>';
    echo '</div>';
}
?>

</body>
</html>