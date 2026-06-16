class CancellablePromise {

	isCancelled = false;

	promise;

	constructor(promise) {
		this.promise = new Promise(
			(resolve, reject) => {
				promise
					.then(
						result => {
							if (!this.isCancelled) {
								return resolve(result);
							}
						}
					)
					.catch(
						e => {
							if (!this.isCancelled) {
								reject(e);
							}
						}
					);
			}
		);
	}

	cancel() {
		this.isCancelled = true;
	}

}

class Autocomplete {

	element;

	input;

	hiddenInput;

	dropdown = null;

	promise;

	constructor(element) {
		this.element = element;
		this.input = element.querySelector('input[type=text]');
		this.input.addEventListener(
			'input',
			(e) => {
				if (z.isEmpty(e.target.value)) {
					this.selectOption(null, '');
				}
				this.openDropdown();
				this.loadOptions(e.target.value);
			}
		);
		this.input.addEventListener(
			'focus',
			(e) => {
				this.openDropdown();
				this.loadOptions();
			}
		);
		this.input.addEventListener(
			'blur',
			(e) => {
				this.closeDropdown();
				this.cancel();
			}
		);
		this.hiddenInput = element.querySelector('input[type=hidden]');
		if (z.notEmpty(this.hiddenInput.value)) {
			this.loadItem(this.hiddenInput.value);
		}
	}

	getData(name) {
		return this.element.dataset[name];
	}

	getEndpoint() {
		return this.getData('endpoint');
	}

	getIdName() {
		return this.getData('select_id_field');
	}

	getLabelName() {
		return this.getData('select_label_field');
	}

	getUrl() {
		return `/json/json/autocomplete?endpoint=${this.getEndpoint()}`;
	}

	isOpen() {
		return (this.dropdown !== null);
	}

	cancel() {
		if (this.promise) this.promise.cancel();
		this.promise = null;
	}

	closeDropdown() {
		if (!this.isOpen()) return;
		z.destroyElement(this.dropdown);
		this.dropdown = null;
	}

	openDropdown() {
		if (this.isOpen()) return;
		this.dropdown = z.createElement(this.element, 'ul', 'dropdown');
	}

	selectOption(id, label) {
		this.input.value = label;
		this.hiddenInput.value = id;
		this.closeDropdown();
	}

	addOption(id, label) {
		this.openDropdown();
		const option = z.createElement(this.dropdown, 'li', 'option', label);
		option.addEventListener(
			'mousedown',
			(e) => {
				e.preventDefault();
				this.selectOption(id, label);
			});
	}

	loadOptions(search) {
		if (!this.isOpen()) return;
		this.cancel();
		this.promise = new CancellablePromise(z.fetch(`${this.getUrl()}&search=${search || ''}`));
		this.promise.promise.then(
			(response) => {
				this.closeDropdown();
				const results = response.json;
				results.forEach((item) => this.addOption(item[this.getIdName()], item[this.getLabelName()]));
			}
		);
	}

	loadItem(id) {
		this.promise = new CancellablePromise(z.fetch(`${this.getUrl()}&id=${id}`));
		this.promise.promise.then(
			(response) => {
				const result = response.json;
				if (result) this.input.value = result[this.getLabelName()];
			}
		);
	}

	static initialize() {
		document.querySelectorAll('.autocomplete').forEach(
			(element) => {
				const autocomplete = new Autocomplete(element);
			}
		)
	}
}

window.addEventListener('DOMContentLoaded', () => Autocomplete.initialize());

