<?php

//use voku\helper\AntiXSS;

include("shared.php");

if (!logged_in()) {
    die("<span>You must be logged in!</span>");
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);

if (!isset($_POST["channel_id"])) { 
    die("<span>No channel ID given.</span>");
}
$chid = $_POST["channel_id"];

if (!isset($_POST["message"])) { 
    die("<span>No message given.</span>");
}
$msg = $_POST["message"];

$cha=getChannelByID($chid);
if (is_null($cha)) { 
    die("<span>Invalid channel ID.</span>");
}

$rwx = getPermissionContext($sessUser, $cha); //get our permissions for it
if (!($rwx->w)) { //if we can't read it, fail
    die("<span>No permissions.</span>");
}

$chid_safe=$cha->channel_id;
$owner_id=$sessUser->user_id;

$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true);
$msg_markdown=$Parsedown->text($msg);

sendMessage($owner_id,$chid_safe,$msg_markdown);

?>