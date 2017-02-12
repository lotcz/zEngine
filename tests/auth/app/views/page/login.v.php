<?php
	$this->renderMessages();
?>

<form method="POST">
	
	<div>
		<label for="user_name">User name:</label> 
		<input type="text" name="user_name" />
	</div>
	
	<div>
		<label for="password">Password:</label> 
		<input type="password" name="password" />
	</div>
	
	<div>
		<input type="submit" value="Login" />
	</div>
	
</form>