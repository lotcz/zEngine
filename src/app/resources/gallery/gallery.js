const gallery_images = [];
var gallery_current_image = null;

function galleryCreateViewer() {
	const prevEvnt = (e) => {
		e.stopPropagation();
		e.preventDefault();
		galleryPrevImage();
	};

	const nextEvnt = (e) => {
		e.stopPropagation();
		e.preventDefault();
		galleryNextImage();
	};

	const closeEvnt = (e) => {
		e.stopPropagation();
		e.preventDefault();
		galleryHideViewer();
	};

	const viewer = z.createElement(document.body, 'div', null, null, closeEvnt);
	viewer.style.display = 'flex';
	viewer.setAttribute('id', 'gallery_viewer');
	viewer.addEventListener('touchstart', closeEvnt);
	const closeBtn = z.createElement(viewer, 'div', 'close-btn');
	closeBtn.addEventListener('touchstart', closeEvnt);

	const arrows = z.createElement(viewer, 'div', 'arrows');
	const prevBtn = z.createElement(arrows, 'div', 'prev-btn',  null, prevEvnt);
	prevBtn.addEventListener('touchstart', prevEvnt);
	const nextBtn = z.createElement(arrows, 'div', 'next-btn', null, nextEvnt);
	nextBtn.addEventListener('touchstart', nextEvnt);

	const img = z.createElement(viewer, 'img', null, null, nextEvnt);
	img.addEventListener('touchstart', nextEvnt);

	return viewer;
}

function galleryGetViewer() {
	const viewer = z.getById('gallery_viewer');
	if (viewer) return viewer;
	return galleryCreateViewer();;
}

function galleryGetImage() {
	const viewer = galleryGetViewer();
	return viewer.querySelector('img');
}

function galleryHideViewer() {
	const viewer = galleryGetViewer();
	z.hide(viewer);
	z.enableScroll();
	window.removeEventListener('keydown', galleryOnKey);
}

function galleryShowViewer() {
	const viewer = galleryGetViewer();
	z.show(viewer);
	z.disableScroll();
	window.addEventListener('keydown', galleryOnKey);
}

function galleryShowImage(thumb) {
	if (!thumb) return;
	gallery_current_image = thumb;
	const url = thumb.dataset.galleryImageUrl;
	const img = galleryGetImage();
	img.setAttribute('src', url);
	galleryShowViewer();
}

function galleryNextImage() {
	let i = gallery_images.indexOf(gallery_current_image);
	if (i >= gallery_images.length - 1) i = -1;
	const next = gallery_images[i + 1];
	galleryShowImage(next);
}

function galleryPrevImage() {
	let i = gallery_images.indexOf(gallery_current_image);
	if (i <= 0) i = gallery_images.length;
	const next = gallery_images[i - 1];
	galleryShowImage(next);
}

function galleryOnKey(e) {
	//console.log(e.keyCode);

	switch (e.keyCode) {
		case 37: // left
			galleryPrevImage();
			break;
		case 39: //right
			galleryNextImage();
			break;
		case 27: //esc
			galleryHideViewer();
			break;
	}
}

function galleryInit() {
	const images = document.querySelectorAll('.gallery-thumbnail');
	images.forEach(
		(thumb) => {
			gallery_images.push(thumb);
			thumb.addEventListener('click', () => galleryShowImage(thumb));
		}
	);

}

window.addEventListener('load', galleryInit);
