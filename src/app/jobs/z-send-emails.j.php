<?php

	$sent = $this->z->emails->processQueue();
	echo "Sent $sent emails.";
