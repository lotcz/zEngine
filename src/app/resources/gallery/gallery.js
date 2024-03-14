/**
	USAGE:

	<div class="gallery-thumbnails">
		<div class="gallery-thumbnail" data-gallery-image-url="view/20200320-103345.jpg">
			<img src="thumb/20200320-103345.jpg">
		</div>
		<div class="gallery-thumbnail" data-gallery-image-url="view/20200427-185906.jpg">
			<img src="thumb/20200427-185906.jpg">
		</div>
	</div>
*/

window.addEventListener('load', () => gallery.init());

const gallery = {
	viewer: null,
	img: null,
	zoomAmountBox: null,
	images: [],
	current_image: null,
	scale: 1,
	offset_x: 0,
	offset_y: 0,
	drag_active: false,
	drag_start_x: 0,
	drag_start_y: 0,

	init: function() {
		const images = document.querySelectorAll('.gallery-thumbnail');
		images.forEach(
			(thumb) => {
				this.images.push(thumb);
				thumb.addEventListener('click', () => this.showImage(thumb));
			}
		);
	},

	emptyEvnt: function(e) {
		e.stopPropagation();
		e.preventDefault();
	},

	prevEvnt: function(e) {
		this.prevImage();
		this.emptyEvnt(e);
	},

	nextEvnt: function(e) {
		this.nextImage();
		this.emptyEvnt(e);
	},

	closeEvnt: function(e) {
		this.hideViewer();
		this.emptyEvnt(e);
	},

	zoomInEvnt: function(e) {
		this.zoom(0.2);
		this.emptyEvnt(e);
	},

	zoomOutEvnt: function(e) {
		this.zoom(-0.2);
		this.emptyEvnt(e);
	},

	zoomResetEvnt: function(e) {
		this.resetZoom();
		this.emptyEvnt(e);
	},

	zoomEvnt: function(e) {
		this.zoom(e.deltaY > 0 ? -0.2 : 0.2);
		this.emptyEvnt(e);
	},

	mouseDownEvnt: function(e) {
		this.drag_active = true;
		this.drag_start_x = e.pageX;
		this.drag_start_y = e.pageY;
		this.emptyEvnt(e);
	},

	touchDownEvnt: function(e) {
		if (this.scale == 1) {
			this.nextEvnt(e);
		} else {
			const touchobj = e.changedTouches[0];
			if (touchobj) {
				this.drag_active = true;
				this.drag_start_x = touchobj.pageX;
				this.drag_start_y = touchobj.pageY;
			}
			this.emptyEvnt(e);
		}
	},

	mouseUpEvnt: function(e) {
		this.drag_active = false;
		this.emptyEvnt(e);
	},

	mouseMoveEvnt: function(e) {
		if (this.drag_active) {
			this.zoom(
				0,
				e.pageX - this.drag_start_x,
				e.pageY - this.drag_start_y
			);
			this.drag_start_x = e.pageX;
			this.drag_start_y = e.pageY;
		}
		this.emptyEvnt(e);
	},

	touchMoveEvnt: function(e) {
		if (this.drag_active) {
			const touchobj = e.changedTouches[0];
			this.zoom(
				0,
				touchobj.pageX - this.drag_start_x,
				touchobj.pageY - this.drag_start_y
			);
			this.drag_start_x = touchobj.pageX;
			this.drag_start_y = touchobj.pageY;
		}
		this.emptyEvnt(e);
	},

	mouseOutEvnt: function(e) {
		this.drag_active = false;
	},

	imageLoadedEvnt: function() {
		this.imageLoaded();
	},

	createViewer: function() {
		const viewer = z.createElement(document.body, 'div');
		viewer.style.display = 'flex';
		viewer.setAttribute('id', 'gallery_viewer');
		viewer.addEventListener('click', (e) => this.closeEvnt(e));
		viewer.addEventListener('wheel', (e) => this.zoomEvnt(e));
		viewer.addEventListener('mouseout', (e) => this.mouseOutEvnt(e));

		const zoomInfo = z.createElement(viewer, 'div', 'zoom-info');
		const zoomOutBtn = z.createElement(zoomInfo, 'div', 'zoom-out-btn', '-', (e) => this.zoomOutEvnt(e));
		zoomOutBtn.addEventListener('touchstart', (e) => this.zoomOutEvnt(e));
		const zoomAmount = z.createElement(zoomInfo, 'div', 'zoom-amount', '100%', (e) => this.zoomResetEvnt(e));
		zoomAmount.addEventListener('touchstart', (e) => this.zoomResetEvnt(e));
		const zoomInBtn = z.createElement(zoomInfo, 'div', 'zoom-in-btn', '+', (e) => this.zoomInEvnt(e));
		zoomInBtn.addEventListener('touchstart', (e) => this.zoomInEvnt(e));

		const closeBtn = z.createElement(viewer, 'div', 'close-btn', null, (e) => this.closeEvnt(e));
		closeBtn.addEventListener('touchstart', (e) => this.closeEvnt(e));

		const arrows = z.createElement(viewer, 'div', 'arrows');
		const prevBtn = z.createElement(arrows, 'div', 'prev-btn', null, (e) => this.prevEvnt(e));
		prevBtn.addEventListener('touchstart', (e) => this.prevEvnt(e));
		const nextBtn = z.createElement(arrows, 'div', 'next-btn', null, (e) => this.nextEvnt(e));
		nextBtn.addEventListener('touchstart', (e) => this.nextEvnt(e));

		const loading = z.createElement(viewer, 'div', 'loading', 'Loading...');

		const img = z.createElement(viewer, 'img');
		img.addEventListener('click', (e) => {
			if (this.scale == 1) {
				this.nextEvnt(e);
			} else {
				this.emptyEvnt(e);
			}
		});
		img.addEventListener('load', (e) => this.imageLoadedEvnt(e));
		img.addEventListener('mousedown', (e) => this.mouseDownEvnt(e));
		img.addEventListener('touchstart', (e) => this.touchDownEvnt(e));
		img.addEventListener('mouseup', (e) => this.mouseUpEvnt(e));
		img.addEventListener('touchend', (e) => this.mouseUpEvnt(e));
		img.addEventListener('mousemove', (e) => this.mouseMoveEvnt(e));
		img.addEventListener('touchmove', (e) => this.touchMoveEvnt(e));

		return viewer;
	},

	getViewer: function() {
		if (!this.viewer) {
			this.viewer = this.createViewer();
		}
		return this.viewer;
	},

	getImage: function() {
		if (!this.img) {
			this.img = this.getViewer().querySelector('img');
		}
		return this.img;
	},

	getZoomAmountBox: function() {
		if (!this.zoomAmountBox) {
			this.zoomAmountBox = this.getViewer().querySelector('.zoom-amount');
		}
		return this.zoomAmountBox;
	},

	startLoading: function() {
		z.addClass(this.getViewer(), 'is-loading');
	},

	imageLoaded: function() {
		z.removeClass(this.getViewer(), 'is-loading');
	},

	hideViewer: function() {
		const viewer = this.getViewer();
		z.hide(viewer);
		z.enableScroll();
		window.removeEventListener('keydown', this.onKey);
	},

	showViewer: function() {
		const viewer = this.getViewer();
		z.show(viewer);
		z.disableScroll();
		window.addEventListener('keydown', this.onKey);
	},

	showImage: function(thumb) {
		this.showViewer();
		this.resetZoom();
		if (this.current_image === thumb || !thumb) return;
		this.current_image = thumb;
		const url = thumb.dataset.galleryImageUrl;
		const img = this.getImage();
		this.startLoading();
		img.setAttribute('src', url);
	},

	nextImage: function() {
		let i = this.images.indexOf(this.current_image);
		if (i >= (this.images.length - 1)) i = -1;
		this.showImage(this.images[i + 1]);
	},

	prevImage: function() {
		let i = this.images.indexOf(this.current_image);
		if (i <= 0) i = this.images.length;
		this.showImage(this.images[i - 1]);
	},

	zoom: function(scale = 0, x = 0, y = 0) {
		const img = this.getImage();
		const old = this.scale;
		const change = this.scale * scale;
		this.scale += change;
		this.scale = Math.max(1, Math.min(10, this.scale));
		this.offset_x += x;
		this.offset_y += y;
		// diminish offset while zooming out
		if (old > this.scale) {
			const portion = Math.abs(change / (old - 1));
			this.offset_x -= this.offset_x * portion;
			this.offset_y -= this.offset_y * portion;
		}
		// reset offset when not zoomed in
		if (this.scale <= 1) {
			this.offset_x = 0;
			this.offset_y = 0;
		}
		img.style.transform = `translate(${this.offset_x}px, ${this.offset_y}px) scale(${this.scale})`;
		this.getZoomAmountBox().innerText = `${Math.round(this.scale * 100)}%`;
	},

	resetZoom: function() {
		this.scale = 1;
		this.offset_x = 0;
		this.offset_y = 0;
		this.drag_active = false;
		this.drag_start_x = 0;
		this.drag_start_y = 0;
		this.zoom();
	},

	onKey: function(e) {
		//console.log(e.keyCode);

		switch (e.keyCode) {
			case 37: // left
				this.prevImage();
				break;
			case 39: //right
				this.nextImage();
				break;
			case 27: //esc
				this.hideViewer();
				break;
		}
	}

}
