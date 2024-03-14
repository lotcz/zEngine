<?php
	$count = $this->z->emails->cleanSentEmails();
	echo "Deleted $count old emails";
