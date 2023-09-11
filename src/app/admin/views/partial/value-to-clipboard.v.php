<div class="value-to-clipboard">
	<button
		class="btn btn-success btn-sm"
		onclick="(function(){;z.valueToClipboard('<?=$value?>'); return false;})();return false;"
		data-toggle="tooltip"
		title="<?=$this->t('Copy to clipboard')?>"
	>
		<img width="20" height="27" src="<?=$this->url('resources/img/clipboard.svg')?>" />
	</button>
</div>
