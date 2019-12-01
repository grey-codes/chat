<?php

require_once '/var/www/html/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); 

include('config.php');
/*
$dbhost = 'localhost:3306';
$dbuser = 'asda';
$dbname = 'asda';
$dbpass = 'asda';
*/

$PREFIX_UPLOADS="uploads/";
$PREFIX_THUMBNAILS="thumbs/";

$usrtb="users";
$chatb="channels";
$msgtb="messages";

$POSIX_FILE_PERMS = 511;
$PERM_READ = 4;
$PERM_WRITE = 2;
$PERM_EXECUTE = 1;


$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection failed to db.");
}


class Perm {
    public $r = false;
    public $w = false;
    public $x = false;
}

function getPermissionContext($user, $object) {
    global $PERM_READ;
    global $PERM_WRITE;
    global $PERM_EXECUTE;
    $permAr = getOctets($object->unixperm);
    $isOwned = false;
    $isGroup = false;

    if (!is_null($user)) {
        $isOwned = (($object->owner_id)==($user->user_id));
        $usrRole = getUserRoleByID($user->user_id);
        $ownRole = getUserRoleByID($object->owner_id);
        if (!is_null($usrRole) && !is_null($ownRole)) {
            if ($usrRole->privilege > $ownRole->privilege) {
                $p = new Perm();
                $p->r = true;
                $p->w = true;
                $p->x = true;
            
                return $p;
            } elseif ($usrRole->role_id == $ownRole->role_id) {
                $isGroup = true;
            }
        }
    }

    $canRead = true;
    $canWrite = true;
    $canExecute = true;

    if ($isOwned) {
        $canRead = $canRead && ( ($permAr[0] & $PERM_READ) == $PERM_READ);
        $canWrite = $canWrite && ( ($permAr[0] & $PERM_WRITE) == $PERM_WRITE);
        $canExecute = $canExecute && ( ($permAr[0] & $PERM_EXECUTE) == $PERM_EXECUTE);
    } elseif ($isGroup) {
        $canRead = $canRead && ( ($permAr[1] & $PERM_READ) == $PERM_READ);
        $canWrite = $canWrite && ( ($permAr[1] & $PERM_WRITE) == $PERM_WRITE);
        $canExecute = $canExecute && ( ($permAr[1] & $PERM_EXECUTE) == $PERM_EXECUTE);
    } else {
        $canRead = $canRead && ( ($permAr[2] & $PERM_READ) == $PERM_READ);
        $canWrite = $canWrite && ( ($permAr[2] & $PERM_WRITE) == $PERM_WRITE);  
        $canExecute = $canExecute && ( ($permAr[2] & $PERM_EXECUTE) == $PERM_EXECUTE);       
    }
    
    $p = new Perm();
    $p->r = $canRead;
    $p->w = $canWrite;
    $p->x = $canExecute;

    return $p;
    /*
    return array(
        "r" => $canRead,
        "w" => $canWrite,
        "x" => $canExecute
    );
    */
}

$usrByNameQuery = "SELECT * FROM " . $usrtb . " WHERE user_name = ?";
$usrByIDQuery = "SELECT * FROM " . $usrtb . " WHERE user_id = ?";
$usrRoleByIDQuery = "SELECT " . $usrtb . ".*, roles.* FROM " . $usrtb . " LEFT JOIN user_roles ON user_roles.user_id=" . $usrtb . ".user_id LEFT JOIN roles ON roles.role_id=user_roles.role_id WHERE users.user_id = ?";
$chaByIDQuery = "SELECT * FROM " . $chatb . " WHERE channel_id = ?";
$chaByNameQuery = "SELECT * FROM " . $chatb . " WHERE name = ?";
$usrRegisterQuery = "INSERT INTO " . $usrtb . " (user_id, user_name, pass_hash) VALUES (NULL, ?, ?)";
$msgSendQuery = "INSERT INTO " . $msgtb . " (msg_id, channel_id, owner_id, value) VALUES (NULL, ?, ?, ?)";
$chaAddQuery = "INSERT INTO " . $chatb . " (channel_id, name, owner_id, unixperm, minSentiment) VALUES (NULL, ?, ?, ?, ?)";

$getUserByNameStatement = $conn->prepare($usrByNameQuery);
$getUserByIDStatement = $conn->prepare($usrByIDQuery);
$usrRoleByIDStatement = $conn->prepare($usrRoleByIDQuery);
$getChannelByIDStatement = $conn->prepare($chaByIDQuery);
$getChannelByNameStatement = $conn->prepare($chaByNameQuery);
$usrRegisterStatement = $conn->prepare($usrRegisterQuery);
$msgSendStatement = $conn->prepare($msgSendQuery);
$chaAddStatement = $conn->prepare($chaAddQuery);

function getUserRoleByID($uid) {
    global $usrRoleByIDStatement;
    $usrRoleByIDStatement->bind_param("i", $uid);
    $usrRoleByIDStatement->execute();
    $result = $usrRoleByIDStatement->get_result();
    $user = $result->fetch_object();
    return $user;
}

function getUserByID($uid) {
    global $getUserByIDStatement;
    $getUserByIDStatement->bind_param("i", $uid);
    $getUserByIDStatement->execute();
    $result = $getUserByIDStatement->get_result();
    $user = $result->fetch_object();
    return $user;
}

function getUserByName($un) {
    global $getUserByNameStatement;
    global $conn;
    $safeuser_name = mysqli_real_escape_string($conn,$un);
    $getUserByNameStatement->bind_param("s", $safeuser_name);
    $getUserByNameStatement->execute();
    $result = $getUserByNameStatement->get_result();
    $user = $result->fetch_object();
    return $user;
}

function getChannelByID($cid) {
    global $getChannelByIDStatement;
    $getChannelByIDStatement->bind_param("i", $cid);
    $getChannelByIDStatement->execute();
    $result = $getChannelByIDStatement->get_result();
    $user = $result->fetch_object();
    return $user;
}

function getChannelByName($cname) {
    global $getChannelByNameStatement;
    global $conn;
    $cname_safe = mysqli_real_escape_string($conn,$cname);
    $getChannelByNameStatement->bind_param("s", $cname_safe);
    $getChannelByNameStatement->execute();
    $result = $getChannelByNameStatement->get_result();
    $chan = $result->fetch_object();
    return $chan;
}

function registerUser($un,$ha) {
    global $usrRegisterStatement;
    global $conn;
    $safeuser_name = mysqli_real_escape_string($conn,$un);
    $safepass_hash= mysqli_real_escape_string($conn,$ha);
    $usrRegisterStatement->bind_param("ss", $safeuser_name, $safepass_hash);
    $usrRegisterStatement->execute();
    $result = $usrRegisterStatement->get_result();
    //$resultobj = $resultobj->fetch_object();
    return $result;
}

function sendMessage($uid,$chid,$msg) {
    global $msgSendStatement;
    global $conn;
    //$msg_safe = htmlspecialchars($msg);
    $msgSendStatement->bind_param("iis", $chid, $uid, $msg);
    $msgSendStatement->execute();
    $result = $msgSendStatement->get_result();
    //$resultobj = $resultobj->fetch_object();
    return $result;
}

function addChannel($uid,$chana,$oct,$sent=-1) {
    global $chaAddStatement;
    global $conn;
    $chana_safe = mysqli_real_escape_string($conn,$chana);
    $chaAddStatement->bind_param("siid", $chana, $uid, $oct, $sent);
    $chaAddStatement->execute();
    $result = $chaAddStatement->get_result();
    //$resultobj = $resultobj->fetch_object();
    return $result;
}

function password_ver($plaintext, $hash) {
    return hash("sha512",$plaintext)==$hash;
}

function logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function verify_username($s) {
    $l=strlen($s);
    if ($l>20) { //too long
        return false;
    } else if ($l<3) { //too short
        return false;
    }
    if (htmlspecialchars($s)!=$s) { //weird chars in username?
        return false;
    }
    return true;
}

function verify_password($s) {
    $l=strlen($s);
    if ($l>128) { //too long
        return false;
    } else if ($l<3) { //too short
        return false;
    }
    if (count_digits($s)<=0) { //not enough numbers
        return false;   
    }
    if (count_specials($s)<=0) { //not enough specials
        return false;   
    }
    return true;
}

function count_digits( $s )
{
    return preg_match_all( "/[0-9]/", $s );
}

function count_specials( $s )
{
    return preg_match_all( "/[^a-zA-Z0-9\s]/", $s );
}

function getOctets($octal) {
    $ar = array(0,0,0);
    $ar[0] = floor($octal/64)%8;
    $ar[1] = floor($octal/8)%8;
    $ar[2] = $octal%8;
    return $ar;
}

?>