<?php
session_start();

// Include the database connection and capture the returned mysqli object
$mysqli = require __DIR__ . "/database.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to upload files.');
}
     else {
    $userId = $_SESSION['user_id']; // Retrieve user_id from the session
    $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null; // Get the user's IP address
}

#echo "Your IP address: $userIp";


$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Ensure the uploads directory exists
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}


// Generate a unique identifier to append to the filename
$uniqueId = uniqid();

// Get the original filename
$originalFileName = basename($_FILES["fileToUpload"]["name"]);

// Extract the file extension from the original filename
$fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

// Generate the new filename with the unique identifier and original file extension
$newFileName = $uniqueId . '.' . $fileExtension;

// Set the target file path with the new filename
$target_file = $target_dir . $newFileName;




// Check if image file is an actual image or fake image
if (isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $filesize = $_FILES["fileToUpload"]["size"];
            $userId = $_SESSION['user_id'];
            $imageWidth = $check[0];
            $imageHeight = $check[1];
            $mime = $check['mime'];
            $filename = basename($_FILES["fileToUpload"]["name"]); // Get the filename

            // Initialize variables to store EXIF data (if any)
            $cameraMake = $cameraModel = $exposureTime = $fNumber = $isoSpeedRatings = null;

            // Check if the image is a JPEG and attempt to read EXIF data
            if ($imageFileType == 'jpeg' || $imageFileType == 'jpg') {
                $exif = @exif_read_data($target_file);
                if ($exif !== false) {
                    $cameraMake = $exif['Make'] ?? null;
                    $cameraModel = $exif['Model'] ?? null;
                    $exposureTime = $exif['ExposureTime'] ?? null;
                    $fNumber = $exif['FNumber'] ?? null;
                    $isoSpeedRatings = $exif['ISOSpeedRatings'] ?? null;
                }
            }

            // Extend your SQL query and bind_param call to include the 'filename' value
            $stmt = $mysqli->prepare("INSERT INTO uploaded_files (user_id, filepath, filesize, filename, width, height, mime, camera_make, camera_model, exposure_time, f_number, iso_speed_ratings, user_ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt === false) {
                die('Error preparing statement: ' . $mysqli->error);
            }

            $stmt->bind_param("isisiisssssss", $userId, $target_file, $filesize, $newFileName, $imageWidth, $imageHeight, $mime, $cameraMake, $cameraModel, $exposureTime, $fNumber, $isoSpeedRatings, $userIp);

            if ($stmt->execute()) {
                // Set a success message in session
                $_SESSION['upload_status'] = "Your IP address: $userIp. File uploaded and EXIF data stored successfully.";
            
                // Redirect to the confirmation page
                header('Location: confirmation.php');
                exit; // Ensure no further script execution after redirection
            } else {
                echo "Error storing file reference and EXIF data: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "File is not an image.";
    }
}

// Assuming database.php properly closes the mysqli connection
?>
