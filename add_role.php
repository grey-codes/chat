<?php
include("shared.php");
header('Content-Type: application/json');

if (!logged_in()) {
    die("{\"success\":false,\"error\":\"not logged in\"}");
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$sessUser = getUserRoleByID($userID);

if (!isset($_POST["role_name"])) { 
    die("{\"success\":false,\"error\":\"no role name\"}");
}
$rolename = $_POST["role_name"];

if (!isset($_POST["privilege"])) { 
    die("{\"success\":false,\"error\":\"no privilege\"}");
}
$privilege = $_POST["privilege"];

$permStr = "{}";
if (isset($_POST["permission_json"])) {
    $permStr = $_POST["permission_json"];
}

if ( is_null($sessUser->permission_json) )  {
    die("{\"success\":false,\"error\":\"no permissions\"}");
}

try {
    $userPerms = json_decode($sessUser->permission_json);

    if ( !property_exists($userPerms,"role_add") ) {
        die("{\"success\":false,\"error\":\"no permissions\"}");
    }

    if ( !$userPerms->role_add ) {
        die("{\"success\":false,\"error\":\"no permissions\"}");
    }

    if ( $sessUser->privilege <= $privilege ) {
        die("{\"success\":false,\"error\":\"can't make rank at higher or equal privilege\"}");
    }
} catch (Exception $e) {
    die("{\"success\":false,\"error\":\"rank has invalid permissions\"}");
}

try {
    $testPerms = json_decode($permStr);
} catch (Exception $e) {
    die("{\"success\":false,\"error\":\"new rank has invalid permissions\"}");
}

$permObj = json_decode($permStr);
if (property_exists($permObj,"role_add") && property_exists($userPerms,"role_add")) {
    $permObj->role_add = $permObj->role_add && $userPerms->role_add;
}


$role=getRoleByName($rolename);
if (!is_null($role)) { 
    die("{\"success\":false,\"error\":\"duplicate role\"}");
}

addRole($rolename,intval($privilege),json_encode($permObj));

echo("{\"success\":true}");

?>