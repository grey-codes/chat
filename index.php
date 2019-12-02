<?php
include('shared.php');
?>
<!DOCTYPE html>
<html lang="en" csrfToken="<?php echo($_SESSION["csrfToken"]); ?>" >
	<head>
		<title>SEMO Chat</title>
        <link rel="stylesheet" href="index.css">
        <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
    <body>
    <div class="outerContainer">
    <div class="container"><div class="header-title">
			<h1>SEMO Chat</h1>
		</div>
    <?php
    if (logged_in()) {
        include('home.php');
    } else {
        include('prompt.php');
    }
    ?>
		
    <div class="footer">
        <p>SEMO Chat</p>
    </div>
    </div>
    </div>
    </body>
</html>