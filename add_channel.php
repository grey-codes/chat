<?php
include("shared.php");
header('Content-Type: application/json');

if (!logged_in()) {
    die("{\"success\":false,\"error\":\"not logged in\"}");
}

dieCSRF();

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);
$sentiment=-1;

if (!isset($_POST["channel_name"])) { 
    die("{\"success\":false,\"error\":\"no channel name\"}");
}
$chana = $_POST["channel_name"];

if (!isset($_POST["octal"])) { 
    die("{\"success\":false,\"error\":\"no octal\"}");
}
$octal = $_POST["octal"];

$cha=getChannelByName($chana);
if (!is_null($cha)) { 
    die("{\"success\":false,\"error\":\"duplicate channel\"}");
}

if (isset($_POST["sentiment"])) { 
    $sentiment = $_POST["sentiment"];
}

$owner_id=$sessUser->user_id;

addChannel($owner_id,$chana,$octal,$sentiment);
echo("{\"success\":true}");

?>