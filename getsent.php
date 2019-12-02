<?php

//use voku\helper\AntiXSS;
use Sentiment\Analyzer;


include("shared.php");
header('Content-Type: text/plain');

$analyzer = new Analyzer();
$vader_result = $analyzer->getSentiment($_POST["message"]);
echo($_POST["message"] . "\n");
var_dump($vader_result);
?>
