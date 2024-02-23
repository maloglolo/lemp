<?php
session_start();

// Include the database connection and capture the returned mysqli object
$mysqli = require __DIR__ . "/database_connection.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to upload files.');
} else {
    $userId = $_SESSION['user_id']; // Retrieve user_id from the session
    $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null; // Get the user's IP address
}

$target_dir = "uploads/";
$fileExtension = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));

// Ensure the uploads directory exists
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Check if file has been uploaded successfully
if (isset($_FILES["fileToUpload"]["tmp_name"]) && is_uploaded_file($_FILES["fileToUpload"]["tmp_name"])) {
    // Generate a unique identifier to append to the filename
    $uniqueId = uniqid();

    // Generate the new filename with the unique identifier and original file extension
    $newFileName = $uniqueId . '.' . $fileExtension;

    // Set the target file path with the new filename
    $target_file = $target_dir . $newFileName;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $filesize = $_FILES["fileToUpload"]["size"];
        $imageSize = getimagesize($target_file); // Check image size and type
        
        if ($imageSize !== false) {
            $imageWidth = $imageSize[0];
            $imageHeight = $imageSize[1];
            $mime = $imageSize['mime'];

            // Initialize variables to store EXIF data (if any)
            $cameraMake = $cameraModel = $exposureTime = $fNumber = $isoSpeedRatings = null;

            // Check if the image is a JPEG and attempt to read EXIF data
            if ($fileExtension == 'jpeg' || $fileExtension == 'jpg') {
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
            echo "Uploaded file is not an image.";
        }
    } else {
        echo "Sorry, there was an error moving the uploaded file.";
    }
} else {
    echo "File upload failed.";
}
?>