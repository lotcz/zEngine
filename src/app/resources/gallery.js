function galleryInit() {
	var fileInput = document.getElementById('gallery_image_file');
	if (fileInput) {
		fileInput.addEventListener(
			'change',
			function (evnt) {
				document.forms[0].submit();
			}
		);
	}
}

window.onload = galleryInit;
