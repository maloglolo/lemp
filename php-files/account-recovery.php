<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

// Get user-submitted username and recovery hash
$username = $_POST['username'] ?? '';
$userSubmittedHash = trim($_POST['recovery_hash'] ?? '');


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate and sanitize the submitted username
    $username = $_POST['username'] ?? ''; // Make sure to sanitize this input
    // Set the username in the session variable
    $_SESSION['username_for_password_reset'] = $username;
    // Redirect to the password reset form
    header('Location: password-reset.php');
    exit;
}

// Check if the username for password reset is set in the session
if (!isset($_SESSION['username_for_password_reset'])) {
    // Redirect to the account recovery form if not set
    header('Location: account-recovery.php');
    exit;
}


// Validate the SHA-256 hash format
if (!preg_match('/^[a-f0-9]{64}$/i', $userSubmittedHash)) {
    echo "Invalid recovery code format.";
    exit; 
}

// Prepare SQL to find the user by their username and unique hash
$sql = "SELECT * FROM user WHERE name = ? AND user_hash = ?";
$stmt = $mysqli->prepare($sql);

// Check if statement preparation was successful
if (!$stmt) {
    echo "Error preparing statement: " . $mysqli->error;
    exit;
}

$stmt->bind_param("ss", $username, $userSubmittedHash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


if ($user) {
    $_SESSION['user_verified'] = true; // Mark the user as verified
    $_SESSION['user_id_for_reset'] = $user['id']; // Store user ID for lookup during password reset
    
    header('Location: password-reset.php'); // Redirect to password reset page
    exit;

} else {
    echo "Invalid username or recovery code. Please try again.";
}

// Close statement and connection if not done automatically
$stmt->close();
$mysqli->close();