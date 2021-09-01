<form class="form">
	<div class="jobs">
		<?php
			foreach ($jobs as $job) {
				?>
					<div class="my-1">
							<button type="button" onclick="javascript:runJob('<?=$job?>');" class="btn btn-primary mr-1"><?=$job ?></button>
							<span><?=$this->z->jobs->getJobUrl($job) ?></span>
					</div>
				<?php
			}
		?>
	</div>
</form>

<div id="console">
	<div id="console_inner">
	</div>
</div>

<script>

	function addToConsole(str) {
		$('#console_inner').html(str);
	}

	function jobFinished(data) {
		addToConsole(data + '<br/>');
	}

	function jobErrored(request, message, error) {
		addToConsole(request.responseText + '<br/>');
	}

	function runCustomJob() {
		var job_name = $('#custom').val();
		runJob(job_name);
	}

	function runJob(name) {
		$.get({
			dataType: 'html',
			url: '<?=$this->url('jobs') ?>',
			data: {
				job: name,
				security_token: '<?=$this->z->jobs->getConfigValue('security_token') ?>'
			},
			success: jobFinished,
			error: jobErrored
		});
	}

</script>
