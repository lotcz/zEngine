<?php
	if ($this->isAuth()) {
		$user = $this->getUser();
		?>
			<h2><?=$user->val('user_login') ?></h2>
			<p>Email: <strong><?=$user->val('user_email') ?></strong></p>
		<?php
	} else {
		?>
			Not logged in.
		<?php
	}
?>