<form method="POST">
	<input type="text" name="test_value" />
	<input type="submit" value="Insert" />
	<?php
		if (isset($this->data['message'])) {
			echo $this->data['message'];
		}
	?>
</form>