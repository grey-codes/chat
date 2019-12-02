<?php
include("shared.php");
header('Content-Type: application/json');

if (!logged_in()) {
    die(json_encode(array()));
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];


if (isset($_POST["user_id"])) { 
    $userID = $_POST["user_id"];
}

$sessUser = getUserRoleByID($userID);

echo(json_encode($sessUser));
?>