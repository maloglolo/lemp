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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
        .slideshow-navigation {
            text-align: center; /* Center the navigation buttons */
            margin-bottom: 20px; /* Add some space between the buttons and the slideshow */
        }

        .slideshow-container {
            max-width: 600px; /* Adjust based on your desired slideshow width */
            margin: auto;
            position: relative;
        }

        .mySlides {
            display: none;
            width: 100%; /* Ensure slide width matches the container */
        }

        .mySlides img {
            width: 100%; /* Make images fill the slide */
            height: auto; /* Maintain aspect ratio */
        }

        /* Style adjustments for .prev and .next if needed */
        .prev, .next {
            cursor: pointer;
            padding: 16px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.6s ease;
            background-color: rgba(0,0,0,0.8);
            display: inline-block; /* Display buttons inline */
            user-select: none;
        }

        /* Hover effect for buttons */
        .prev:hover, .next:hover {
            background-color: rgba(0,0,0,1);
        }

        .comment {
            border-bottom: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }

        textarea {
            width: 100%;
            margin-bottom: 10px;
        }
    </style>

    <script src="js/slideshow.js"></script>
    <script>
        function updateImageId(imageId) {
            document.getElementById('currentImageId').value = imageId;
        }

        function changeSlideAndUpdate(imageId, direction) {
            changeSlide(direction);
            updateImageId(imageId);
        }
    </script>
    <script>
        function confirmDelete(fileId) {
            if (confirm("Are you sure you want to delete this file?")) {
                window.location.href = "delete_file.php?file_id=" + fileId;
            }
        }
    </script>
    <script src="js/comments.js"></script>
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <form action="logout.php" method="post" style="text-align: right;">
            <button type="submit">Logout</button>
        </form>
    <?php endif; ?>

    <h1>Home</h1>
    <p>Hello <?= htmlspecialchars($user["name"])?> ! </p>

    <!-- Slideshow navigation buttons -->
    <div class="slideshow-navigation">
        <!-- Pass the imageId to the changeSlideAndUpdate function -->
        <button class="prev" onclick="changeSlideAndUpdate(<?php echo $imageId; ?>, -1)">&#10094; Prev</button>
        <button class="next" onclick="changeSlideAndUpdate(<?php echo $imageId; ?>, 1)">Next &#10095;</button>
    </div>
    <div class="slideshow-container">
        <!-- Dynamic slides will be added here -->
    </div>

    <?php
    $mysqli = require __DIR__ . "/database_connection.php";

    $sql = "SELECT comments.content, comments.created_at, user.name FROM comments JOIN user ON comments.user_id = user.id ORDER BY comments.created_at DESC";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div class='comment'>";
            echo "<p><strong>" . htmlspecialchars($row["name"]) . "</strong> at " . $row["created_at"] . "</p>";
            echo "<p>" . htmlspecialchars($row["content"]) . "</p>";
            echo "</div>";
        }
    } else {
        echo "No comments found."; // Add this line for debugging purposes
    }
    echo "Image ID: " . $imageId;
    ?>

    <form action="submit_comment.php" method="post">
        <textarea name="commentContent" required></textarea>
        <input type="hidden" id="currentImageId" name="imageId" value="<?php echo $imageId; ?>">
        <button type="submit">Post Comment</button>
    </form>

    <?php if (isset($user)): ?>
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
            echo '<button style="color: red;" onclick="confirmDelete(' . $row['id'] . ')">Delete</button>';
            // Link to the confirmation page instead of a direct form submission
            #echo '<a href="confirm_delete.php?file_id=' . $row['id'] . '" style="color: red;">Delete</a>';    
            echo '</div>';
        }
        ?>
    <?php else: ?>
        <p><a href="login.php">Login in</a> or <a href="signup.html">sign up</a></p>
    <?php endif; ?>
</body>
</html>