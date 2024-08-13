<?php
	$asyncJob = $job;
	$name = $asyncJob->getJobName();
	$processing = $asyncJob->getItemsCountProcessing();
	$waiting = $asyncJob->getItemsCountWaiting();
?>
<div>
	<h4><?=$name?></h4>
	<strong><?=$waiting?></strong> čeká, <strong><?=$processing?></strong> se právě zpracovává
</div>
