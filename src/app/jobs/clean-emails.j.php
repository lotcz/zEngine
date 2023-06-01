<?php
	$stmt = $this->z->emails->cleanSentEmails();
	$count = $stmt->rowCount();
	echo "Deleted <strong>$count</strong> old emails";
