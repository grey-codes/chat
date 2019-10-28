<?php
include('shared.php');
session_destroy(); // Is Used To Destroy All Sessions
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>SEMO Chat</title>
        <link rel="stylesheet" href="index.css">
        <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
      <script type="text/javascript">
      setTimeout(function () {
         window.location.href = "/chat/";
      }, 2000);
      </script>
	</head>
    <body>
        <div class="outerContainer">
        <div class="container">
            <div class="header-title">
                <h1>SEMO Chat</h1>
            </div>
         <h1>Logged out.</h1>
         <div class="footer">
            <p>SEMO Chat</p>
         </div>
        </div>
   </body>
</html>