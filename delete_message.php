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

if (!isset($_POST["message_id"])) { 
    die("{\"success\":false,\"error\":\"no msg id\"}");
}
$msgID = $_POST["message_id"];

$msgQuery = "SELECT messages.*,users.user_name,roles.* FROM messages INNER JOIN users ON messages.owner_id=users.user_id LEFT JOIN user_roles ON user_roles.user_id=users.user_id LEFT JOIN roles ON roles.role_id=user_roles.role_id WHERE messages.msg_id=?";
$msgStatement = $conn->prepare($msgQuery);
$msgStatement->bind_param("i", $msgID);
$msgStatement->execute();
$result = $msgStatement->get_result();
$resultObj = $result->fetch_object();

if (is_null($resultObj)) {
    die("{\"success\":false,\"error\":\"no msg present\"}");
}

$usrPermObj = getUserRoleByID($userID);

$usrPriv = isset($usrPermObj->privilege) ? ($usrPermObj->privilege) : 0; 
$ownPriv = isset($resultObj->privilege) ? ($resultObj->privilege) : 0; 

if ($usrPriv > $ownPriv || $userID == $resultObj->owner_id ) {
    deleteMessage($msgID);
    echo("{\"success\":true}");
}  else {
    die("{\"success\":false,\"error\":\"no perms\"}");
}

?>