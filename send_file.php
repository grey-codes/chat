<?php
include("shared.php");

if (!logged_in()) {
    die("<span>You must be logged in!</span>");
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);


if (!isset($_POST["channel_id"])) { 
    print_r($_POST);
    die("<span>No channel ID given.</span>");
}
$chid = $_POST["channel_id"];

$cha=getChannelByID($chid);
if (is_null($cha)) { 
    die("<span>Invalid channel ID.</span>");
}

$rwx = getPermissionContext($sessUser, $cha); //get our permissions for it
if (!($rwx->w)) { //if we can't read it, fail
    die("<span>No permissions.</span>");
}

if (!isset($_FILES["myfile"])) {
    die("<span>No file given!</span>");
}

$fileExtensions = ['jpeg','jpg','png'];

$fileName = $_FILES['myfile']['name'];
$fileSize = $_FILES['myfile']['size'];
$fileTmpName  = $_FILES['myfile']['tmp_name'];
$fileType = $_FILES['myfile']['type'];

$chid_safe=$cha->channel_id;
$owner_id=$sessUser->user_id;

$fileHash = sha1($fileName . $fileSize . $fileType);
$tmp=explode('.',$fileName);
$fileExt = strtolower(end($tmp));

if (isset($fileName)) {
    if (!in_array($fileExt,$fileExtensions)) {
        die("This process does not support this file type.");
    }
    $imgPath="uploads/" . $owner_id . "/" . $fileHash . "." . $fileExt;
    $imgPathAbs=getcwd() . "/" . $imgPath;
    $pathParts = pathinfo($imgPathAbs);
    $dirToMake=$pathParts["dirname"]."/";
    if (!file_exists($dirToMake)) {
        mkdir($dirToMake,0770,true);
    }
    $didUpload = move_uploaded_file($fileTmpName, $imgPathAbs);
    if ($didUpload) {
        chmod($imgPathAbs,0660);
        $sz = getimagesize($imgPathAbs);
        $msg="<img src=\"" . $imgPath .  "\" width=\"" . $sz[0] . "px\" height=\"" . $sz[1] . "px\"></img>";
        sendMessage($owner_id,$chid_safe,$msg);
    }
}
?>