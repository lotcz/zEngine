function preventDefaults(e) {
	e.preventDefault();
	e.stopPropagation();
}

function galleryHandleDrop(e) {
	preventDefaults(e);
	const dt = e.dataTransfer;
	const files = dt.files;

	[...files].forEach(galleryUploadFile);
}

function galleryUploadFile(file) {
	const url = document.location;
	const formData = new FormData();

	formData.append('image_file', file);

	fetch(
		url,
		{
			method: 'POST',
			body: formData
		}
	)
	.then(() => {
		document.location = document.location;
	})
	.catch((e) => {
		console.error("Image upload failed.", e);
	});
}

function galleryInitAdmin() {
	const fileInput = document.getElementById('gallery_image_file');
	if (fileInput) {
		fileInput.addEventListener(
			'change',
			function (evnt) {
				document.forms[0].submit();
			}
		);
	}

	const dropArea = document.querySelector('.gallery-admin-wrapper');
	if (dropArea) {
		const events = ['dragenter', 'dragover', 'dragleave'];
		events.forEach(eventName => dropArea.addEventListener(eventName, preventDefaults, false));
		dropArea.addEventListener('drop', galleryHandleDrop, false);
	}
}

window.addEventListener('load', galleryInitAdmin);
