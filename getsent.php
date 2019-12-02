<?php

//use voku\helper\AntiXSS;
use Sentiment\Analyzer;


include("shared.php");
header('Content-Type: text/plain');

$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true);
$msg_markdown=$Parsedown->text($_POST["message"]);

$analyzer = new Analyzer();
$msgClean = strip_tags($msg_markdown);
$vader_result = $analyzer->getSentiment($msgClean . " " . $msgClean);

echo($_POST["message"] . "\n");
var_dump($vader_result);
?>
