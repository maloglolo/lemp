<?php
mysqli_report(MYSQLI_REPORT_OFF);


$is_invalid = false;
if ($_SERVER["REQUEST_METHOD"] === "POST"){

    $mysqli = require __DIR__ . "/database.php";

    $sql = sprintf("SELECT * FROM user
            WHERE email = '%s'",
            $mysqli->real_escape_string($_POST["email"]));

    $result = $mysqli->query($sql);

    $user = $result->fetch_assoc();

    if ($user) {

        if (password_verify($_POST["password"], $user["password_hash"])) {

            ini_set('session.cookie_secure', '1');
            ini_set('session.cookie_httponly', '1');
            session_set_cookie_params([
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax' // or 'Strict'
            ]);

            session_start();  

            session_regenerate_id();

            $_SESSION["user_id"] = $user["id"];

            header("Location: index.php");
            exit;
        }
    }
    $is_invalid = true;
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>

    <h1>Login</h1>

    <?php if ($is_invalid): ?>
        <em>Invalid login</em>
    <?php endif; ?>


    <form method="post">
        <label for="email">email</label>
        <input type="email" name="email" id="email"
                value="<?= htmlspecialchars($_POST["email"] ?? "" )?>">

        <label for="password">Password</label>
        <input type="password" name="password" id="password">

        <button>Login!</button>
        
    </form>

    <a href="account-recovery.html" class="button">Recovery</a>

</body>