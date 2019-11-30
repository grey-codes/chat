<?php
include("shared.php");

if (!logged_in()) {
    die("<span>You must be logged in!</span>");
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserByID($userID);

$msgCount = 25;
$msgOffset = 0;
if (isset($_POST["offset"])) { 
    $msgOffset = $_POST["offset"];
}

$chid = $_POST["channel_id"];
if (is_null($chid)) { 
    die("<span>No channel ID given.</span>");
}

$cha=getChannelByID($chid);
if (is_null($cha)) { 
    die("<span>Invalid channel ID.</span>");
}

$rwx = getPermissionContext($sessUser, $cha); //get our permissions for it
if (!($rwx->r)) { //if we can't read it, fail
    die("<span>No permissions.</span>");
}

$msgQuery = "SELECT * FROM (SELECT msg_id, user_name, value FROM messages INNER JOIN users ON messages.owner_id=users.user_id WHERE messages.channel_id=? ORDER BY msg_id DESC LIMIT ? OFFSET ? ) AS msgT ORDER BY msg_id ASC";
$msgStatement = $conn->prepare($msgQuery);
$msgStatement->bind_param("iii", $chid,$msgCount,$msgOffset);
$msgStatement->execute();
$result = $msgStatement->get_result();

$msgAr = array();

if ( (!is_null($result)) && $result->num_rows > 0) { //for each channel
    while($o = $result->fetch_object()) {
        array_push($msgAr,$o);
    }
    /*
    $rows = $result->num_rows;
    $count=0;
    while($o = $result->fetch_object()) { //get an object
        $isLast = ( ++$count == $rows );
        echo("<div class=\"textRow");
        if ($isLast) {
            echo(" last\"");
        } else {
            echo("\"");
        }
        echo(">");
        echo("<span class=\"author\">" . $o->user_name . "</span>&nbsp;<span class=\"message\">" . $o->value . "</span>");
        echo("</div>");
    }
    */
    echo(json_encode($msgAr));
} else {
    echo("<div class=\"textRow\">No messages.</div>");
}

?>