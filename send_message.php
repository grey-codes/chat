<?php

//use voku\helper\AntiXSS;
use Sentiment\Analyzer;
use mofodojodino\ProfanityFilter\Check;
use Snipe\BanBuilder\CensorWords;

$SWEAR_FILTER_MIN_SENTIMENT = -0.5;
$SWEAR_FILTER_CENSOR_SENTIMENT = 0;

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

sendMessage($owner_id,$chid_safe,$msg_markdown);
echo("{\"success\":true}");

?>
