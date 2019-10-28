<?php
include('shared.php');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>SEMO Chat</title>
        <link rel="stylesheet" href="index.css">
        <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
    <body>
        <div class="outerContainer">
        <div class="container">
            <div class="header-title">
                <h1>SEMO Chat</h1>
            </div>
            <?php
            if ( ! empty( $_POST ) ) {
                if ( isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
                    $un=$_POST['username'];
                    $pass=$_POST['password'];
                    $good=true;
                    if (!verify_username($un)) {
                        echo("<h1>Username doesn't meet requirements!</h1>");
                        $good=false;
                    }
                    if (!verify_password($pass)) {
                        echo("<h1>Password doesn't meet requirements!</h1>");
                        $good=false;
                    }
                    if ($good) {
                        $user = getUserByName($un);
                        
                        if (is_null($user)) {
                            $hash = hash("sha512",$pass);
                            registerUser($un,$hash);
                            echo("<h1>User created. Please log in.</h1>");
                        } else {
                            echo("<h1>User already exists!</h1>");
                        }
                    }
                }
            }
            ?>
            
            <script type="text/javascript">
            setTimeout(function () {
            window.location.href = "index.php";
            }, 2000);
            </script>
                
            <div class="footer">
                <p>SEMO Chat designed by Grey Ruessler, Quinn Johnson,
                Logan Geppert, and Sawyer Loos 2019</p>
            </div>
        </div>
        </div>
    </body>
</html>