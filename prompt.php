		    <form action="login.php" method="post" autocomplete="off" >
			<label for="username"><b>Username</b></label>
			<input type="text" placeholder="Enter username (3-20 characters)" name="username" autocomplete="off" minlength="3" maxlength="20" required />
		
			<label for ="passwd"><b>Password</b></label>
			<input type="password" placeholder="Enter Password (3-128 characters, 1+ number, 1+ special)" name="password" autocomplete="off" minlength="3" maxlength="128" required/>
		
			<input type="submit" formaction="login.php" value="Log in">
			<input type="submit" formaction="register.php" value="Sign up">
            </form>