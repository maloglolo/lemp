<?php
session_start();



#session_start();
#echo '<pre>';
#var_dump($_SESSION);
#echo '</pre>';




// Check if the username for password reset is set in the session
if (!isset($_SESSION['username_for_password_reset'])) {
    // Redirect to the account recovery form if not set
    header('Location: account-recovery.php');
    exit;
}

// Include the database connection setup
$mysqli = require __DIR__ . "/database.php";

// Retrieve the new password and confirmation from the POST request
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$username = $_SESSION['username_for_password_reset'];

// Ensure the new passwords match and meet any required criteria
if ($newPassword !== $confirmPassword) {
    // Redirect or inform the user that passwords do not match
    // For better UX, consider using sessions to pass error messages
    $_SESSION['error_message'] = 'Passwords do not match.';
    header('Location: password-reset.php');
    exit;
}

// Add any additional password strength validation here

// Proceed with updating the user's password in the database
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$sql = "UPDATE user SET password_hash = ? WHERE name = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    // Log error and redirect or inform the user
    error_log("Prepare statement error: " . $mysqli->error);
    $_SESSION['error_message'] = 'An error occurred. Please try again.';
    header('Location: password-reset.php');
    exit;
}

// Bind parameters and execute the statement
$stmt->bind_param("ss", $hashedPassword, $username);
$executeSuccess = $stmt->execute();

// Check if the password was successfully updated
if ($executeSuccess && $stmt->affected_rows > 0) {
    // Password reset was successful
    unset($_SESSION['username_for_password_reset']); // Clear the username from the session
    session_regenerate_id(true); // Security measure to regenerate session ID
    // Redirect to a success notification page or the login page
    header('Location: login.php?reset=success');
    exit;
} else {
    // Handle cases where the password was not updated (e.g., user not found or DB error)
    // For DB errors, consider logging them for review
    if (!$executeSuccess) {
        error_log("Execution error: " . $stmt->error);
    }
    $_SESSION['error_message'] = 'Password reset failed. Please try again.';
    header('Location: password-reset.php');
    exit;
}

// Close statement and connection
$stmt->close();
$mysqli->close();