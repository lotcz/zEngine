<div class="panel panel-default">
	<div class="panel-heading">
		<span class="panel-title"><?=$this->t('Maintenance Jobs') ?></span>
	</div>
	<div class="panel-body">
		<div>
			<a href="#" onclick="javascript:runJob('clean');return false;" class="btn btn-default"><?=$this->t('Clean sessions') ?></a>
			<a href="#" onclick="javascript:runJob('abx');return false;" class="btn btn-default"><?=$this->t('Import from ABX') ?></a>
			<a href="#" onclick="javascript:runJob('cube');return false;" class="btn btn-default"><?=$this->t('Import from Cubecart') ?></a>
			<a href="#" onclick="javascript:runJob('implangs');return false;" class="btn btn-default"><?=$this->t('Import translations') ?></a>
			<a href="#" onclick="javascript:runJob('explangs');return false;" class="btn btn-default"><?=$this->t('Export translations') ?></a>
		</div>

		<div>
			<div id="console">
				<div id="console_inner">
				</div>
			</div>
		</div>

	</div>
</div>

<script>

	function showAjaxLoaders() {
		cons('<span class="ajax-loader"></span>');
		$('.ajax-loader').animate({opacity:1});
	}

	function hideAjaxLoaders() {
		$('.ajax-loader').animate({opacity:0});
		$('#console_inner > .ajax-loader').remove();
	}

	function cons(str) {
		$('#console_inner').append(str);
		$('#console').scrollTop($('#console_inner').height());
	}

	function jobSuccess(message) {
		hideAjaxLoaders();
		cons(message + '<br/>');
	}

	function jobError(xhr, message) {
		hideAjaxLoaders();
		cons(xhr.responseText + '<br/>');
	}

	function runJob(name) {
		showAjaxLoaders();
		$.ajax({
			dataType: 'html',
			url: '<?=$this->url($this->z->jobs->base_url) ?>/' + name,
			data: {
					job: name,
					security_token: '<?=$this->z->jobs->security_token ?>'
				},
			success: jobSuccess,
			error: jobError
		});
	}

</script>
