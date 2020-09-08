<form class="form-inline p-2">
	<div class="form-row">
		<div class="form-group col-12">
			<button type="button" onclick="javascript:runJob('clean');" class="btn btn-primary"><?=$this->t('Clean') ?></button>
		</div>
	</div>
</form>
<form class="form-inline p-2">
	<div class="form-row">
	  <div class="form-group">
	    <label for="custom">Custom:</label>
	    <input type="text" class="form-control" id="custom" name="custom" >
	  	<button type="button" onclick="javascript:runCustomJob();" class="btn btn-primary"><?=$this->t('Run') ?></button>
		</div>
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
