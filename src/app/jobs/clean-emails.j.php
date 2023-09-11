<?php
	$stmt = $this->z->emails->cleanSentEmails();
	$count = $stmt->rowCount();
	echo "Deleted $count old emails";
