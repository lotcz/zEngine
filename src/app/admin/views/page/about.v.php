<div class="row">
	<div class="col-md-4">
		<table>
			<tr>
				<td><?=$this->t('Application version')?>:</td>
				<td><strong><?=$this->z->app->version ?></strong></td>
			</tr>
			<tr>
				<td><?=$this->t('zEngine version')?>:</td>
				<td><strong><?=$this->z->version ?></strong> (<?=$this->t('required at least %s', $this->z->app->require_z_version) ?>)</td>
			</tr>
		</table>
	</div>
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<span class="panel-title"><?=$this->t('User sessions') ?></span>
			</div>
			<div class="panel-body">
				<canvas id="myChart1" />
			</div>
		</div>
	</div>
</div>
	
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>

<script>
	window.onload = function() {
		var pieData = [];
		var pieLabels = [];

			<?php
				$sessions = $this->getData('sessions');
				foreach ($sessions as $s) {
					?>
						pieData.push(<?=$s->val('c') ?>);
						pieLabels.push("<?=$s->val('n') ?>");
					<?php
				}
			?>

		var config = {
				 type: 'pie',
				 data: {
						 datasets: [{
								 data: pieData,
								 label: 'Sessions',
								 backgroundColor: [
	                'Red',
	                'Blue',
	                'Green'
	            ]
						 }],
						 labels: pieLabels
				 },
				 options: {
						 responsive: true
				 }
		 };

		var ctx = document.getElementById('myChart1').getContext('2d');
		var myPieChart = new Chart(ctx, config);
	};
</script>
