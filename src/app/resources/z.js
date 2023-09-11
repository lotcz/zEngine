const z = {

	getById : function(id) {
		return document.getElementById(id);
	},

	isString : function(s) {
		return	(typeof s === 'string' || s instanceof String);
	},

	isObject : function(o) {
		return (o !== null && typeof o === 'object');
	},

	getElement : function(idOrObject) {
		const el = (this.isString(idOrObject)) ? this.getById(idOrObject) : idOrObject;
		if (this.isObject(el)) {
			if (el.classList) {
				return el;
			}
		}
		return null;
	},

	hasClass : function(element, css) {
		return element.classList.contains(css);
	},

	addClass : function(idOrObject, css) {
		const el = this.getElement(idOrObject);
		if (Array.isArray((css)) && css.length > 0) {
			css.forEach((cls) => {
				if (typeof cls === 'string' && cls.length > 0) this.addClass(el, cls);
			});
		} else if (css) {
			css.split(' ').forEach((cls) => {
				if (typeof cls === 'string' && cls.length > 0 && !this.hasClass(el, cls)) el.classList.add(cls);
			});
		}
	},

	removeClass : function(idOrObject, cls) {
		const el = this.getElement(idOrObject);
		if (el) {
			el.classList.remove(cls);
		}
	},

	show : function(idOrObject) {
		const el = this.getElement(idOrObject);
		if (el) {
			const current = el.style.display;
			if (current != 'none') return;
			const original = el.getAttribute('data-z-original-display');
			el.style.display = (original) ? original : 'block';
		}
	},

	hide : function(idOrObject) {
		const el = this.getElement(idOrObject);
		if (el) {
			const old = el.style.display;
			if (old === 'none') return;
			el.setAttribute('data-z-original-display', old);
			el.style.display = 'none';
		}
	},

	val : function(idOrObject) {
		const el = this.getElement(idOrObject);
		if (el) {
			return el.value;
		}
		return null;
	},

	fetch : async function(url, body = null, method = 'GET') {
		return fetch(url, {
			method: method, // *GET, POST, PUT, DELETE, etc.
			mode: 'cors', // no-cors, *cors, same-origin
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			credentials: 'same-origin', // include, *same-origin, omit
			headers: {
				'Content-Type': 'application/json'
			},
			redirect: 'follow', // manual, *follow, error
			referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
			body: body ? JSON.stringify(body) : undefined// body data type must match "Content-Type" header
		})
			.then(
				(response) => response.json()
					.then(
						(json)=> new Promise((resolve, reject) => resolve({json: json, status: response.status}))
					)
			);
	},

	destroyElement : function(el) {
		if (!el) return;
		el.remove();
	},

	createElement : function(parent, tag, css = null, innerText = null, onClick = null) {
		const el = document.createElement(tag);
		this.addClass(el, css);
		if (parent) {
			parent.appendChild(el);
		}
		if (innerText) {
			el.innerHTML = innerText;
		}
		if (onClick) {
			el.addEventListener('click', onClick);
		}
		return el;
	},

	stopPropagation : function(e) {
		e.stopPropagation();
	},

	preventDefault : function(e) {
		e.preventDefault();
	},

	preventDefaultForScrollKeys : function(e) {
		// left: 37, up: 38, right: 39, down: 40, spacebar: 32, pageup: 33, pagedown: 34, end: 35, home: 36
		const scroll_keys = {38: 1, 40: 1, 33: 1, 34: 1, 35: 1, 36: 1};
		if (scroll_keys[e.keyCode]) {
			e.preventDefault();
			return false;
		}
	},

	scrollingDisabled : false,

	// modern Chrome requires { passive: false } when adding event
	supportsPassive : false,

	wheelEvent : 'onwheel' in document.createElement('div') ? 'wheel' : 'mousewheel',

	disableScroll: function () {
		if (this.scrollingDisabled) return;
		const wheelOpt = this.supportsPassive ? {passive: false} : false;
		window.addEventListener('DOMMouseScroll', this.preventDefault, false); // older FF
		window.addEventListener('wheel', this.preventDefault, wheelOpt); // modern desktop
		window.addEventListener('touchmove', this.preventDefault, wheelOpt); // mobile
		window.addEventListener('keydown', this.preventDefaultForScrollKeys, false);
		this.scrollingDisabled = true;
	},

	enableScroll: function() {
		const wheelOpt = this.supportsPassive ? {passive: false} : false;
		window.removeEventListener('DOMMouseScroll', this.preventDefault, false);
		window.removeEventListener('wheel', this.preventDefault, wheelOpt);
		window.removeEventListener('touchmove', this.preventDefault, wheelOpt);
		window.removeEventListener('keydown', this.preventDefaultForScrollKeys, false);
		this.scrollingDisabled = false;
	},

	getDateTimeLocalVal: function (date) {
		const d = new Date(date);
		d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
		return d.toISOString().slice(0,16);
	},

	parseDateTimeLocalVal: function (str) {
		const d = new Date(str);
		d.setMinutes(d.getMinutes() + d.getTimezoneOffset());
		return d;
	},

	valueToClipboard: function (value) {
		if (!window.isSecureContext) {
			console.error("Not in secure context! Use site with HTTPS.");
			return;
		}
		navigator.clipboard
			.writeText(value)
			.then(() => {
				console.log("Copied value to clipboard.");
			})
			.catch((e) => {
				console.error("Something went wrong when copying value to clipboard.", e);
			});
	}

}

try {
	window.addEventListener(
		'test',
		null,
		Object.defineProperty({}, 'passive', {
			get: function() {
				z.supportsPassive = true;
			},
		}),
	);
} catch (e) {}

Date.prototype.toJSON = function() {
	const hoursDiff = this.getHours() - this.getTimezoneOffset() / 60;
	const zoned = new Date(this);
	zoned.setHours(hoursDiff);
	return zoned.toISOString();
};
