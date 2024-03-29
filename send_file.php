<?php
include("shared.php");
include("simpleimg.php");
header('Content-Type: application/json');

use Sentiment\Analyzer;
use mofodojodino\ProfanityFilter\Check;
use Snipe\BanBuilder\CensorWords;

if (!logged_in()) {
    die("{\"success\":false,\"error\":\"not logged in\"}");
}

dieCSRF();

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);


if (!isset($_POST["channel_id"])) { 
    print_r($_POST);
    die("{\"success\":false,\"error\":\"no channel id given\"}");
}
$chid = $_POST["channel_id"];

$cha=getChannelByID($chid);
if (is_null($cha)) { 
    die("{\"success\":false,\"error\":\"invalid channel id\"}");
}

$rwx = getPermissionContext($sessUser, $cha); //get our permissions for it
if (!($rwx->w)) { //if we can't read it, fail
    die("{\"success\":false,\"error\":\"no write permissions\"}");
}

if (!isset($_FILES["myfile"])) {
    die("{\"success\":false,\"error\":\"no file attached\"}");
}

$fileName = $_FILES['myfile']['name'];
$fileSize = $_FILES['myfile']['size'];
$fileTmpName  = $_FILES['myfile']['tmp_name'];
$fileType = $_FILES['myfile']['type'];

$PREVIEW_WIDTH=500;
$PREVIEW_HEIGHT=500;

$chid_safe=$cha->channel_id;
$owner_id=$sessUser->user_id;

$fileHash = sha1($fileName . $fileSize . $fileType);
$tmp=explode('.',$fileName);
$fileExt = strtolower(end($tmp));

if (isset($fileName)) {
    if (!@is_array(getimagesize($fileTmpName))) {
        die("{\"success\":false,\"error\":\"file type not supported\"}");
    }
    $imgPath=$PREFIX_UPLOADS . $owner_id . "/" . $fileHash . "." . $fileExt;
    $thumbPath=$PREFIX_THUMBNAILS . $owner_id . "/" . $fileHash . ".jpg";
    $imgPathAbs=getcwd() . "/" . $imgPath;
    $thumbPathAbs=getcwd() . "/" . $thumbPath;
    $pathParts = pathinfo($imgPathAbs);
    $dirToMake=$pathParts["dirname"]."/";
    if (!file_exists($dirToMake)) {
        mkdir($dirToMake,0770,true);
    }
    $pathParts = pathinfo($thumbPathAbs);
    $thumbsDir=$pathParts["dirname"]."/";
    if (!file_exists($thumbsDir)) {
        mkdir($thumbsDir,0770,true);
    }
    $didUpload = move_uploaded_file($fileTmpName, $imgPathAbs);
    if ($didUpload) {
        chmod($imgPathAbs,0660);

        $sz = getimagesize($imgPathAbs);
        if ($sz[0]>$PREVIEW_WIDTH) {
            $rat = $sz[1]/$sz[0];
            $sz[0]=$PREVIEW_WIDTH;
            $sz[1]=$sz[0]*$rat;
        }
        if ($sz[1]>$PREVIEW_HEIGHT) {
            $rat = $sz[0]/$sz[1];
            $sz[1]=$PREVIEW_HEIGHT;
            $sz[0]=$sz[1]*$rat;
        }

        $image = new SimpleImage();
        $image->load($imgPathAbs);
        $image->resize($sz[0], $sz[1]);
        $image->save($thumbPathAbs);

        $totalMsg="<p><img data-src=\"" . $thumbPath .  "\" src=\"\" fullSrc=\"" . $imgPath . "\" width=\"" . $sz[0] . "px\" height=\"" . $sz[1] . "px\"></img></p>";
        
        if (isset($_POST["message"])) {
            $msg = $_POST["message"];
            $min_Sentiment=$cha->minSentiment;
            if ($min_Sentiment >= $SWEAR_FILTER_MIN_SENTIMENT) {
                $check = new Check();
                if ($check->hasProfanity($msg) && $min_Sentiment >= $SWEAR_FILTER_CENSOR_SENTIMENT) {
                    die("{\"success\":false,\"error\":\"Message does not follow channel rules - no profanity.\"}");
                } else {
                    $censor = new CensorWords;
                    $string = $censor->censorString($msg);
                    $msg = $string["clean"];
                }
            }

            $Parsedown = new Parsedown();
            $Parsedown->setSafeMode(true);
            $msg_markdown=$Parsedown->text($msg);

            $analyzer = new Analyzer();
            $msgClean = strip_tags($msg_markdown);
            $vader_result = $analyzer->getSentiment($msgClean . " " . $msgClean);
            $score = $vader_result["compound"];

            if ($min_Sentiment > $score) { //check the channel sentiment vs the message score
                die("{\"success\":false,\"error\":\"Message does not follow channel rules.\"}");
           }

            $totalMsg = $totalMsg . $msg_markdown;
        }

        sendMessage($owner_id,$chid_safe,$totalMsg);
        echo("{\"success\":true}");
    }
}
?>