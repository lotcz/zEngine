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
			el.style.display = 'block';
		}
	},

	hide : function(idOrObject) {
		const el = this.getElement(idOrObject);
		if (el) {
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

	fetch : async function(url, body, method = 'GET') {
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
			body: body // body data type must match "Content-Type" header
		});
	},

	createElement : function(parent, tag, css = null, innerText = null, onClick = null) {
		const el = document.createElement(tag);
		this.addClass(el, css);
		if (parent) {
			parent.appendChild(el);
		}
		if (innerText) {
			el.innerText = innerText;
		}
		if (onClick) {
			el.addEventListener('click', onClick);
		}
		return el;
	}

}
