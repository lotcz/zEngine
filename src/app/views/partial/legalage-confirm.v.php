<div id="legalage_confirm" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Citlivý obsah</h5>
				<button type="button" class="btn-close" onclick="ageNotConfirmed();" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Na této stránce se nachází citlivý obsah nevhodný pro nezletilé osoby.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" onclick="ageNotConfirmed();">Odejít</button>
				<button type="button" class="btn btn-primary" onclick="ageConfirmed();">Potvrzuji, že jsem plnoletý</button>
			</div>
		</div>
	</div>
</div>

<script>
	const legalageModalElement = document.getElementById('legalage_confirm');
	const legalageModal = new bootstrap.Modal(legalageModalElement);

	function ageConfirmed() {
		legalageSetIsConfirmed(true);
		legalageModal.hide();
	}

	function ageNotConfirmed() {
		legalageSetIsConfirmed(false);
		legalageFallbackRedirect();
	}

	if (!legalageIsConfirmed()) {
		legalageModal.show();
	}

	legalageModalElement.addEventListener('hide.bs.modal', function () {
		if (!legalageIsConfirmed()) {
			legalageFallbackRedirect();
		}
	});
</script>
