<form class="form">
	<div class="jobs">
		<?php
			foreach ($jobs as $job) {
				$url = $this->z->jobs->getJobUrl($job);
				?>
					<div class="my-1">
						<button type="button" onclick="javascript:runJob('<?=$job?>', '<?=$url?>');" class="btn btn-primary mr-1"><?=$job ?></button>
						<span><?=$url?></span>
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

	function addToConsole(title) {
		const item = z.createElement(null, 'div', 'border rounded p-1 mt-2');
		z.getById('console_inner').prepend(item);
		const itemhead = z.createElement(item, 'div', 'd-flex gap-2 border-bottom');
		z.createElement(itemhead, 'span', '', new Date().toLocaleTimeString());
		z.createElement(itemhead, 'strong', '', title);
		const itemcontent = z.createElement(item, 'div');
		z.createElement(itemcontent, 'div', 'spinner-border spinner-border-sm');
		return itemcontent;
	}

	function jobFinished(itemel, started, response) {
		response.text().then(
			(text) => {
				itemel.innerHTML = '';

				const units = ['d', 'h', 'm', 's', 'ms'];
				const mags = [1, 24, 60, 60, 1000];
				let diff = new Date().getTime() - started.getTime();
				const results = [0, 0, 0, 0, 0];
				for (let i = 4; i >= 0; i--) {
					const next = Math.floor(diff / mags[i]);
					results[i] = diff - (next * mags[i]);
					diff = next;
				}

				let timeStr = '';
				let printed = false;
				for (let i = 0; i < 5; i++) {
					const amount = results[i];
					if (amount > 0 || i === 4) {
						timeStr += amount + ' ' + units[i] + ' ';
						if (printed) break;
						printed = true;
					}
				}

				z.createElement(itemel, 'strong', '', `${response.status} - ${response.statusText}`);
				z.createElement(itemel, 'div', '', timeStr);
				z.createElement(itemel, 'pre', 'border-1', text);
			}
		);
	}

	function runJob(name, url) {
		const el = addToConsole(name);
		const started = new Date();
		fetch(url).then((response) => jobFinished(el, started, response));
	}

</script>
