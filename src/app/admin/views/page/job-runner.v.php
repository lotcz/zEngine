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

<div id="console" class="mt-2">
	<div id="console_inner">
	</div>
</div>

<script>

	function addToConsole(str) {
		return z.createElement(z.getById('console_inner'), 'div', 'border-1 mt-2', str);
	}

	function jobFinished(el, response) {
		response.text().then(
			(text) => el.innerHTML = `${response.statusText} : ${text}`
		);
	}

	function runJob(name) {
		const el = addToConsole(`Running job ${name}...`);
		const finished = (response) => jobFinished(el, response);
		fetch(`<?=$this->url('jobs') ?>?job=${name}&security_token=<?=$this->z->jobs->getConfigValue('security_token') ?>`)
		.then(finished);
	}

</script>
