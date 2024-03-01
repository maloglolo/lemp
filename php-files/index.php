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
        .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px;
        background-color: #00ff00;
        color: #fff;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }
        
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
        .dropdown {
            position: relative;
            display: inline-block;
            text-align: right; /* Ensure dropdown text aligns to the right */
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: rgba(249, 249, 249, 0.01); /* Semi-transparent background */
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            padding: 12px 16px;
            z-index: 1;
            text-align: right; /* Align button texts to the right */
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content form,
        .dropdown-content a {
            margin: 5px 0;
        }
        
        /* Right-align buttons within forms and links */
        .dropdown-content form button,
        .dropdown-content a button {
            width: 100%; /* Make buttons fill the container */
            text-align: right; /* Align text to the right */
        }
    </style>

    <script src="js/slideshow.js"></script>
    <script>
        function updateImageId(imageId) {
            document.getElementById('currentImageId').value = imageId;
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

    <h1>Home</h1>
    <p>Hello <?= htmlspecialchars($user["name"])?> ! </p>

    <!-- Slideshow navigation buttons -->
    <div class="slideshow-navigation">
        <!-- Pass the imageId to the changeSlideAndUpdate function -->
            <button class="prev" onclick="changeSlide(-1)">&#10094; Prev</button>
            <button class="next" onclick="changeSlide(1)">Next &#10095;</button>
    </div>
    <div class="slideshow-container">
        <!-- Dynamic slides will be added here -->
    </div>
    <div class="comments-section">
    <h3>Comments</h3>
    <div class="comments-container">
        <!-- Comments will be loaded here by the loadCommentsForImage JavaScript function -->
    </div>
</div>


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
       
    <?php else: ?>
        <p><a href="login.php">Login in</a> or <a href="signup.html">sign up</a></p>
    <?php endif; ?>
</body>
</html>
