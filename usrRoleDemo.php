<?php
include("shared.php");

$usr1 = getUserRoleByID(1);
echo("<html>");
var_dump($usr1);
echo("<br/>");
$usr2 = getUserRoleByID(2);
var_dump($usr2);
echo("<br/>");
if ($usr1->role_id == $usr2->role_id)
echo("same group");
if ($usr1->privilege > $usr2->privilege)
echo("usr1>usr2");
echo("</html>");
?>