<?php

	$deleted = $this->z->newsletter->cleanSubscriptionEmails();
	echo "Deleted <strong>$deleted</strong> invalid emails.\r\n";
