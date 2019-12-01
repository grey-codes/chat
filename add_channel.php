<?php
include("shared.php");

if (!logged_in()) {
    die("<span>You must be logged in!</span>");
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);
$sentiment=-1;

if (!isset($_POST["channel_name"])) { 
    die("<span>No channel name given.</span>");
}
$chana = $_POST["channel_name"];

if (!isset($_POST["octal"])) { 
    die("<span>No octal given.</span>");
}
$octal = $_POST["octal"];

$cha=getChannelByName($chana);
if (!is_null($cha)) { 
    die("<span>Channel already exists!.</span>");
}

if (isset($_POST["sentiment"])) { 
    $sentiment = $_POST["sentiment"];
}

$owner_id=$sessUser->user_id;

echo(addChannel($owner_id,$chana,$octal,$sentiment));

?>