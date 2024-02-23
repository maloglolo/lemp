<?php
mysqli_report(MYSQLI_REPORT_OFF);
if(empty($_POST["name"])) {
    die("Name is required");
}
if ( ! filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
    die("Valid email is required");
}
if (strlen($_POST["password"])  < 8 ) {
    die("Password must be atleast 8 characters");
}
if ( ! preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain atleast one letter");
}
if ( ! preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain atleast one number");
}
if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match!");
}
$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$email = $_POST['email'];
$currentTime = time();
$randomBytes = openssl_random_pseudo_bytes(16);
$randomComponent = bin2hex($randomBytes);
$user_hash = hash('sha256', $email . $currentTime . $randomComponent);

$short_user_hash = substr($user_hash, 0, 8); 

#var_dump($short_user_hash);

$mysqli = require __DIR__ . "/database_connection.php";

ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax' // or 'Strict'
]);
session_start();     
                                    
$_SESSION['user_hash'] = $user_hash; 
$_SESSION['short_user_hash'] = $short_user_hash;

$sql = "INSERT INTO user (name, email, password_hash, user_hash, short_user_hash)
        VALUES( ?, ?, ?, ?, ?)";

$stmt = $mysqli->stmt_init();

if ( ! $stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}
$stmt->bind_param("sssss",
                    $_POST["name"],
                    $_POST["email"],
                    $password_hash,
                    $user_hash,
                    $short_user_hash);
if ($stmt->execute()){
    header("Location: signup-success.php");
    exit;
} else {
    if ($mysqli->errno === 1062) {
        die("Email already taken.");
    }
    die("Error: " . $stmt->error . " " . $stmt->errno);
}