<?php

include("shared.php");
header('Content-Type: application/json');

if (!logged_in()) {
    die("{\"success\":false,\"error\":\"not logged in\"}");
}

dieCSRF();

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserRoleByID($userID);

if (!isset($_POST["new_role"])) { 
    die("{\"success\":false,\"error\":\"no role name\"}");
}
$rolename = $_POST["new_role"]; //new_role is from homechat.js

if (!isset($_POST["usr_id"])) { 
    die("{\"success\":false,\"error\":\"no user\"}");
}
$target_user_id = $_POST["usr_id"]; //usr_id is from homechat.js

$targetUser = getUserRoleByID($target_user_id);
if (is_null($targetUser)) {
    die("{\"success\":false,\"error\":\"invalid user\"}");
}

$myPriv = $sessUser -> privilege;
$theirPriv = $targetUser -> privilege;

if ( $myPriv <= $theirPriv ) {
    die("{\"success\":false,\"error\":\"target is equal or higher rank\"}");
}

$roleObj = getRoleByName($rolename);
if (is_null($roleObj)) {
    die("{\"success\":false,\"error\":\"invalid role\"}");
}

if ( $myPriv <= $roleObj -> privilege ) {
    die("{\"success\":false,\"error\":\"desired role is equal or higher rank\"}");
}

$existsQuery = "SELECT * FROM user_roles WHERE user_id = ?";
$existsStmt = $conn->prepare($existsQuery);
$existsStmt->bind_param("i", $target_user_id);
$existsStmt->execute();
$existsRes = $existsStmt->get_result();

if ( !is_null( $existsRes->fetch_object() ) ) {
    $updateQuery = "UPDATE user_roles SET role_id = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $roleObj->role_id, $target_user_id);
    $updateStmt->execute();
} else {
    $insertQuery = "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("ii", $target_user_id, $roleObj->role_id);
    $insertStmt->execute();
}

echo("{\"success\":true}");
?>
