<?php
include("shared.php");
header('Content-Type: application/json');

if (!logged_in()) {
    die(json_encode(array()));
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);

$chQuery = "SELECT * FROM " . $chatb . " ORDER BY Name ASC";
$chStatement = $conn->prepare($chQuery);
$chStatement->execute();
$result = $chStatement->get_result();

$resultAr = array();

if ( (!is_null($result)) && $result->num_rows > 0) { //for each channel
    while($o = $result->fetch_object()) { //get an object
        $rwx = getPermissionContext($sessUser, $o); //get our permissions for it
        if ($rwx->r) { //if we can read it, list it
            array_push($resultAr,$o);
            /*
            echo("<div class=\"textRow\" channel_id=\"" . $o->channel_id . "\">");
            echo("<span=\"channelName\">".$o->name."</span>");
            echo("</div>");
            */
        }
    }
}

echo(json_encode($resultAr));
?>