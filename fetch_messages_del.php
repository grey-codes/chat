<?php
include("shared.php");
header('Content-Type: application/json');

if (!logged_in()) {
    die(json_encode(array()));
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);

$msgQuery = "SELECT msg_id FROM " . $msgtb . "_deleted ORDER BY msg_id DESC LIMIT 25";
$msgStatement = $conn->prepare($msgQuery);
$msgStatement->execute();
$result = $msgStatement->get_result();

$msgAr = array();

if ( (!is_null($result)) && $result->num_rows > 0) { //for each channel
    while($o = $result->fetch_object()) {
        array_push($msgAr,$o->msg_id);
    }
}

echo(json_encode($msgAr));
?>