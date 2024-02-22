<?php

$mysqli = require __DIR__ . "/database.php";

$sql = sprintf("SELECT * FROM user
                WHERE email = '%s'",
                $mysqli->real_escape_string($_GET["email"]));            

$result = $mysqli->query($sql);

$is_available = $result->num_rows === 0 ;

echo json_encode(["available" => $is_available]);
