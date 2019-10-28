<?php
include("shared.php");

if (!logged_in()) {
    die("<span>You must be logged in!</span>");
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);

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

$owner_id=$sessUser->user_id;


addChannel($owner_id,$chana,$octal);

?>