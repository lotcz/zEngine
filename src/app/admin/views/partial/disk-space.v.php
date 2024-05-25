<?php
	$total = z::formatDiskSpace($this->z->admin->getTotalDiskSpace());
	$free = z::formatDiskSpace($this->z->admin->getFreeDiskSpace());
	$ratio = 1 - $this->z->admin->getFreeDiskSpaceRatio();
	$percent = round($ratio * 100);
	$thresholds = [0, 85, 95, PHP_FLOAT_MAX];
	$colors = ['success', 'warning', 'danger'];
	$i = 0;
	while ($i < count($thresholds) && ($percent >= $thresholds[$i + 1])) {
		$i++;
	}
	$color = $colors[$i];
?>
<div class="card">
	<div class="card-body">
		<p class="card-text"><?=sprintf($this->t('%s free of total %s'), $free, $total)?></p>
		<div class="progress">
			<div class="progress-bar bg-<?=$color?>" role="progressbar" style="width: <?=$percent?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
				<?=$percent?>%
			</div>
		</div>
	</div>
</div>
