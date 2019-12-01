<?php

//use voku\helper\AntiXSS;

include("shared.php");
header('Content-Type: application/json');

if (!logged_in()) {
    die("{\"success\":false,\"error\":\"not logged in\"}");
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);

if (!isset($_POST["channel_id"])) { 
    die("{\"success\":false,\"error\":\"no channel id given\"}");
}
$chid = $_POST["channel_id"];

if (!isset($_POST["message"])) { 
    die("{\"success\":false,\"error\":\"no message given\"}");
}
$msg = $_POST["message"];

$cha=getChannelByID($chid);
if (is_null($cha)) { 
    die("{\"success\":false,\"error\":\"invalid channel\"}");
}

$rwx = getPermissionContext($sessUser, $cha); //get our permissions for it
if (!($rwx->w)) { //if we can't read it, fail
    die("{\"success\":false,\"error\":\"no permissions\"}");
}

$chid_safe=$cha->channel_id;
$owner_id=$sessUser->user_id;

$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true);
$msg_markdown=$Parsedown->text($msg);

sendMessage($owner_id,$chid_safe,$msg_markdown);
echo("{\"success\":true}");

?>