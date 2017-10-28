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

<div class="panel panel-default">
	<div class="panel-heading">
		<span class="panel-title"><?=$this->t('User sessions') ?></span>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-4">
				<canvas id="myChart1" />
			</div>
			<div class="col-md-4">
				<canvas id="myChart2" />
			</div>
			<div class="col-md-4">
				<canvas id="myChart3" />
			</div>
		</div>
	</div>
</div>

<script src="http://localhost/Chart.js/Chart.min.js"></script>
<!-- https://cdnjs.com/libraries/chart.js-->

<script>
	Chart.defaults.global.responsive = true;

	var data = [
		<?php
			$sessions = $this->getData('sessions');
			foreach ($sessions as $s) {
				?>
					{
						value: <?=$s->val('c') ?>,
						label: "<?=$s->val('n') ?>"
					},
				<?php
			}
		?>
	]


	// For a pie chart
	var ctx = document.getElementById('myChart1').getContext('2d');
	var myPieChart = new Chart(ctx).Pie(data);

	// And for a doughnut chart
	var ctx2 = document.getElementById("myChart2").getContext("2d");
	var myDoughnutChart = new Chart(ctx2).Doughnut(data);


	var ldata = {
		labels: ["January", "February", "March", "April", "May", "June", "July"],
		datasets: [
			{
				label: "My First dataset",
				fillColor: "rgba(220,220,220,0.2)",
				strokeColor: "rgba(220,220,220,1)",
				pointColor: "rgba(220,220,220,1)",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(220,220,220,1)",
				data: [65, 59, 80, 81, 56, 55, 40]
			},
			{
				label: "My Second dataset",
				fillColor: "rgba(151,187,205,0.2)",
				strokeColor: "rgba(151,187,205,1)",
				pointColor: "rgba(151,187,205,1)",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(151,187,205,1)",
				data: [28, 48, 40, 19, 86, 27, 90]
			}
		]
	};

	var ctx3 = document.getElementById('myChart3').getContext('2d');
	var myLineChart = new Chart(ctx3).Line(ldata);


</script>
