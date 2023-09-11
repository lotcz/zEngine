export const MODE_MONTH = 'month';
export const MODE_DAY = 'day';
export const MONTHS = ['leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec'];
export const MONTHS_DECLINATED = ['ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince'];
export const DAYS = ['pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota', 'neděle'];
export const DAYS_SHORT = ['PO', 'ÚT', 'ST', 'ČT', 'PÁ', 'SO', 'NE'];

class CalendarMode {
	calendar;
	key;
	name;
	modeUpKey;

	constructor(calendar, key, name, up) {
		this.calendar = calendar;
		this.key = key;
		this.name = name;
		this.modeUpKey = up;
	}

	static getDayOfWeek(date) {
		const d = date.getDay();
		return d === 0 ? 7 : d;
	}

	static formatDate(date) {
		return `${date.getDate()}. ${date.getMonth() + 1}. ${date.getFullYear()}`;
	}

	static formatDateLong(date) {
		return `${date.getDate()}. ${MONTHS_DECLINATED[date.getMonth()]}. ${date.getFullYear()}`;
	}

	static roundDateDay(date) {
		return new Date(date.getFullYear(), date.getMonth(), date.getDate());
	}

	static isSameDay(date1, date2) {
		return this.roundDateDay(date1).getTime() === this.roundDateDay(date2).getTime();
	}

	static createElement(parent, tag, css = '', html = '') {
		const el = document.createElement(tag);
		el.className = css;
		el.innerHTML = html;
		parent.appendChild(el);
		return el;
	}

	getDescription() {}

	getDateNext(currentDay) {}

	getDatePrev(currentDay) {}

	render() {}

	leaving() {}
}

class ModeMonth extends CalendarMode {
	constructor(calendar) {
		super(calendar, MODE_MONTH, 'Měsíc');
	}

	roundDate(date) {
		const start = new Date(date);
		start.setDate(1);
		return CalendarMode.roundDateDay(start);
	}

	getDescription() {
		return `${MONTHS[this.calendar.currentDay.getMonth()]} ${this.calendar.currentDay.getFullYear()}`;
	}

	getDateNext(currentDay) {
		const nextMonth = new Date(currentDay);
		nextMonth.setMonth(currentDay.getMonth() + 1);
		return this.roundDate(nextMonth);
	}

	getDatePrev(currentDay) {
		const prevMonth = new Date(currentDay);
		prevMonth.setMonth(currentDay.getMonth() - 1);
		return this.roundDate(prevMonth);
	}

	render() {
		this.calendar.view.innerHTML = '';
		const view = CalendarMode.createElement(this.calendar.view, 'div', 'view-month');
		const lastDate = this.getDateNext(this.roundDate(this.calendar.currentDay));
		lastDate.setDate(0);
		const lastDay = lastDate.getDate();
		const now = new Date();
		let dayOfWeek = CalendarMode.getDayOfWeek(this.calendar.currentDay);

		for (let day = 0; day < 7; day++) {
			CalendarMode.createElement(view, 'div', 'slot-day label', DAYS_SHORT[day]);
		}

		for (let day = dayOfWeek; day > 1; day--) {
			CalendarMode.createElement(view, 'div', 'slot-day disabled');
		}

		for (let day = 1; day <= lastDay; day++) {
			const date = new Date(this.calendar.currentDay);
			date.setDate(day);
			dayOfWeek = CalendarMode.getDayOfWeek(date);
			const slot = CalendarMode.createElement(view, 'div', 'slot-day', day);
			const dateNext = new Date(date);
			dateNext.setDate(dateNext.getDate() + 1);
			const reservations = this.calendar.getReservations(date, dateNext);
			if (reservations.length > 0) {
				const r9s = z.createElement(slot, 'div', 'reservations');
				reservations.forEach((r) => z.createElement(r9s, 'div', 'reservation'));
			}
			if (CalendarMode.isSameDay(date, now)) {
				z.addClass(slot, 'today');
			}
			if (dayOfWeek > 5) {
				z.addClass(slot, 'weekend');
			} else {
				if (date.getTime() < now.getTime()) {
					z.addClass(slot, 'past');
				} else {
					z.addClass(slot, 'available');
					slot.addEventListener('click', () => this.calendar.setModeAndDay(MODE_DAY, date));
				}
			}
		}

		for (let day= CalendarMode.getDayOfWeek(lastDate); day < 7; day++) {
			CalendarMode.createElement(view, 'div', 'slot-day disabled');
		}
	}
}

class ModeDay extends CalendarMode {
	constructor(calendar) {
		super(calendar, MODE_DAY, 'Den', MODE_MONTH);
	}

	roundDate(date) {
		return CalendarMode.roundDateDay(date);
	}

	getDescription() {
		return `${DAYS[(this.calendar.currentDay.getDay() + 6) % 7]}, ${CalendarMode.formatDateLong(this.calendar.currentDay)}`;
	}

	getDateNext(currentDay) {
		const nextDay = new Date(currentDay);
		nextDay.setDate(currentDay.getDate() + 1);
		return this.roundDate(nextDay);
	}

	getDatePrev(currentDay) {
		const prevDay = new Date(currentDay);
		prevDay.setDate(currentDay.getDate() - 1);
		return this.roundDate(prevDay);
	}

	formatSlotTime(time) {
		const hours = Math.floor(time);
		const minutes = String((time - hours) * 60);
		return `${String(hours).padStart(2, '0')}:${minutes.padStart(2, '0')}`;
	}

	render() {
		this.calendar.view.innerHTML = '';
		const view = CalendarMode.createElement(this.calendar.view, 'div', 'view-day');
		const date = this.calendar.currentDay;
		for (let time = this.calendar.minStartTime; time < this.calendar.maxEndTime; time = time + 1) {
			const slot = CalendarMode.createElement(view, 'div', 'slot d-flex flex-row');
			const hour = CalendarMode.createElement(slot, 'div', 'slot-time p-2 text-center', this.formatSlotTime(time));
			const minutes = CalendarMode.createElement(slot, 'div', 'slot-minutes text-small muted');
			for (let minute = time, max = time + 1; minute < max; minute = minute + this.calendar.slotDuration) {
				const minuteSlot = CalendarMode.createElement(minutes, 'div', 'slot-body ps-2');
				z.createElement(minuteSlot, 'div', 'time', this.formatSlotTime(minute));
				date.setHours(time);
				const mins = (minute - time) * 60;
				date.setMinutes(mins);
				const next = new Date(date);
				next.setMinutes(mins + (this.calendar.slotDuration * 60));
				const reservations = this.calendar.getReservations(date, next);
				if (reservations.length > 0) {
					const reservation = reservations[0];
					z.addClass(minuteSlot, 'occupied');
					minuteSlot.addEventListener('click', () => this.calendar.showForm(reservation));
					if (this.calendar.adminMode || (this.calendar.user && reservation.email === this.calendar.user.email)) {
						z.createElement(minuteSlot, 'div', 'usr-email ps-2', reservation.email);
					}
				} else {
					z.addClass(minuteSlot, 'available');
					const slotDate = new Date(date);
					minuteSlot.addEventListener('click', () => {
						const service = this.calendar.getService();
						const reservation = {
							start: slotDate,
							duration: service ? service.duration_minutes : 15,
							cosmetic_service_id: service ? service.id : null,
							email: this.calendar.user ? this.calendar.user.email : ''
						};
						this.calendar.reservations.push(reservation);
						this.render();
						this.calendar.showForm(reservation)
					});
				}
				minuteSlot.style.height = `${this.calendar.slotHeightPx}px`;
			}
		}
	}

	leaving() {
		this.calendar.hideForm();
	}
}

export default class Calendar {
	modes;
	dom;
	mode;
	currentDay;
	adminMode;
	reservations = null;
	reservation = null;

	minStartTime = 8;
	maxEndTime = 18;
	slotDuration = 0.25;
	slotHeightPx = 15;

	constructor(dom, admin = false, mode = MODE_MONTH, day = new Date()) {
		this.modes = [new ModeMonth(this), new ModeDay(this)];

		this.dom = dom;
		this.adminMode = admin;
		this.currentDay = day;
		this.services = {};

		this.dom.innerHTML =
			`<form class="calendar-menu mb-3 d-flex flex-row justify-content-center">
				<button type="button" class="up-button btn btn-primary">&nbsp;</button>
				<button type="button" class="prev-button btn btn-primary">&nbsp;</button>
				<div class="description d-flex flex-row align-items-center">
					<div class="date-desc"></div>
				</div>
				<button type="button" class="next-button btn btn-primary">&nbsp;</button>
			</form>
			<div class="calendar-view">
				<div class="calendar-mode">
				</div>
				<div class="calendar-form">
				</div>
			</div>`;

		this.upButton = this.dom.querySelector('.up-button');
		this.upButton.addEventListener('click', () => this.up());
		this.prevButton = this.dom.querySelector('.prev-button');
		this.prevButton.addEventListener('click', () => this.prev());
		this.nextButton = this.dom.querySelector('.next-button');
		this.nextButton.addEventListener('click', () => this.next());

		this.dateDesc = this.dom.querySelector('.date-desc');
		this.view = this.dom.querySelector('.calendar-mode');
		this.form = this.dom.querySelector('.calendar-form');

		this.setMode(mode);
		this.loadServices();
		this.reloadCurrentUser();
	}

	setMode(modeKey) {
		this.setModeAndDay(modeKey, this.currentDay);
	}

	setCurrentDay(day) {
		this.setModeAndDay(this.mode.key, day);
	}

	setModeAndDay(modeKey, day) {
		if (this.mode) {
			if (this.mode.key !== modeKey) {
				this.mode.leaving();
			}
		}
		this.today = CalendarMode.roundDateDay(new Date());
		this.mode = this.modes.find((m) => m.key === modeKey);
		this.currentDay = this.mode.roundDate(day);
		this.reload();
	}

	up() {
		if (this.mode.modeUpKey) this.setMode(this.mode.modeUpKey);
	}

	prev() {
		this.setCurrentDay(this.mode.getDatePrev(this.currentDay));
	}

	next() {
		this.setCurrentDay(this.mode.getDateNext(this.currentDay));
	}

	getReservations(from, to) {
		if (!this.reservations) return [];
		return this.reservations.filter((r) => {
			const start = new Date(r.start);
			const end = new Date(r.start);
			end.setMinutes(end.getMinutes() + Number(r.duration));
			return end.getTime() > from.getTime() && start.getTime() < to.getTime();
		});
	}

	reloaded() {
		if (this.reservation) {
			this.reservations.push(this.reservation);
			this.reservation.start.setFullYear(
				this.currentDay.getFullYear(),
				this.currentDay.getMonth(),
				this.currentDay.getDate()
			);
			this.showForm(this.reservation);
		}
		this.render();
	}

	reload() {
		this.reservations = null;
		this.render();

		const from = this.currentDay;
		const to = this.mode.getDateNext(this.currentDay);
		z.fetch(`/json/default/calendar?from=${from.toISOString()}&to=${to.toISOString()}`)
			.then((response) => {
				this.reservations = response.json;
				console.log(this.reservations);
				this.reloaded();
			});
	}

	loadServices() {
		z.fetch('/json/default/cosmetic-services')
			.then((response) => {
				this.services = response.json;
			});
	}

	getService(id = null) {
		for (const catName in this.services) {
			const services = this.services[catName];
			for (const service of services) {
				if (service.id == id || id === null) {
					return service;
				}
			}
		}
		return null;
	}

	reloadCurrentUser(loaded = null) {
		z.fetch('/json/default/current-user')
			.then((response) => {
				this.user = response.json;
				if (loaded) loaded(this.user);
			});
	}

	showFormMessage(message = '&nbsp;', style = 'light') {
		this.message.innerHTML = '';
		z.createElement(this.message, 'div', `alert alert-${style}`, message);
	}

	showLoading(message = '') {
		const loader = `
			<div class="text-center">
				<div class="spinner-border text-warning" role="status">
					<span class="visually-hidden">${message}</span>
				</div>
			</div>
		`;
		this.showFormMessage(loader);
	}

	hideForm() {
		this.reservation = null;
		z.destroyElement(this.formWrapper);
		this.formWrapper = null;
		this.frm = null;
		this.message = null;
	}

	saveForm() {
		this.showLoading();
		z.fetch('/json/default/calendar', this.reservation, 'POST')
			.then((response) => {
				console.log(response.status);
				const json = response.json;
				const message = json.message;
				this.showFormMessage(message, response.status === 200 ? 'success' : 'warning');
				const result = json.result;
				this.reservation.id = result.id;
			});
	}

	showForm(reservation) {
		this.hideForm();
		this.reservation = reservation;
		this.formWrapper = z.createElement(this.form, 'div', 'form-wrapper');
		const formInner = z.createElement(this.formWrapper, 'div', 'form');
		z.createElement(formInner, 'h4', 'header text-center', 'Rezervace termínu');
		const form = z.createElement(formInner, 'form', 'pt-3 px-5');
		this.frm = form;
		const day = z.createElement(form, 'div', 'day row mb-2');
		z.createElement(day, 'label', 'py-1 col-sm-4 col-form-label', 'Datum').setAttribute('for', 'date');
		const dateCol = z.createElement(day, 'div', 'col-sm-8');
		const date = z.createElement(dateCol, 'input', 'form-control');
		date.setAttribute('id', 'date');
		date.setAttribute('type', 'datetime-local');
		date.setAttribute('min', z.getDateTimeLocalVal(this.today));
		date.setAttribute('value', z.getDateTimeLocalVal(reservation.start));
		date.addEventListener('change', (e) => {
			const old = this.currentDay;
			const n = new Date(e.target.value);
			reservation.start = n;
			if (CalendarMode.isSameDay(old, n)) {
				this.mode.render();
			} else {
				this.setCurrentDay(n);
			}
		});

		const service = z.createElement(form, 'div', 'service row mb-2');
		z.createElement(service, 'label', 'py-1 col-sm-4 col-form-label', 'Hlavní objednávka').setAttribute('for', 'service');
		const serviceCol = z.createElement(service, 'div', 'col-sm-8');
		const select = z.createElement(serviceCol, 'select', 'form-select');
		select.setAttribute('id', 'service');
		select.addEventListener('change', (e) => {
			reservation.cometic_service_id = e.target.value;
			const service = this.getService(reservation.cometic_service_id);
			if (service) {
				reservation.duration = service.duration_minutes;
				this.frm.elements['duration'].value = reservation.duration;
				this.mode.render();
			}
		});

		for (const catName in this.services) {
			const group = z.createElement(select, 'optgroup');
			group.setAttribute('label', catName);
			const services = this.services[catName];
			services.forEach((service) => {
				const option = z.createElement(group, 'option', null, service.name);
				option.setAttribute('value', service.id);
				if (reservation.cometic_service_id == service.id) {
					option.setAttribute('selected', 'selected');
				}
			});
		}

		const duration = z.createElement(form, 'div', 'duration row mb-2');
		z.createElement(duration, 'label', 'py-1 col-sm-4 col-form-label', 'Délka rezervace').setAttribute('for', 'duration');
		const durationCol = z.createElement(duration, 'div', 'col-sm-8');
		const durationGrp = z.createElement(durationCol, 'div', 'input-group');
		const inp = z.createElement(durationGrp, 'input', 'form-control');
		inp.setAttribute('id', 'duration');
		inp.setAttribute('name', 'duration');
		inp.setAttribute('type', 'number');
		inp.setAttribute('min', 60 * this.slotDuration);
		inp.setAttribute('max', 60 * 16 * this.slotDuration);
		inp.setAttribute('step', 60 * this.slotDuration);
		inp.setAttribute('value', reservation.duration);
		inp.addEventListener('change', (e) => {
			reservation.duration = e.target.value;
			this.mode.render();
		});
		const durationTxt = z.createElement(durationGrp, 'span', 'input-group-text', 'minut');

		const email = z.createElement(form, 'div', 'email row');
		z.createElement(email, 'label', 'py-1 col-sm-4 col-form-label', 'Email').setAttribute('for', 'email');
		const emailCol = z.createElement(email, 'div', 'col-sm-8');
		const inpem = z.createElement(emailCol, 'input', 'form-control');
		inpem.setAttribute('id', 'email');
		inpem.setAttribute('type', 'email');
		inpem.setAttribute('value', reservation.email);
		inpem.addEventListener('change', (e) => {
			reservation.email = e.target.value;
		});

		const message = z.createElement(form, 'div', 'message row');
		this.message = z.createElement(message, 'div', 'col pt-2');

		const buttonsWrapper = z.createElement(formInner, 'div', 'buttons container');
		const buttons = z.createElement(buttonsWrapper, 'div', 'row justify-content-between');
		const close = z.createElement(buttons, 'div', 'col text-center');
		z.createElement(
			close,
			'button',
			'btn btn-secondary',
			'Zavřít',
			() => {
				this.unsetReservation();
				this.hideForm();
			}
		);
		const save = z.createElement(buttons, 'div', 'col text-center');
		z.createElement(
			save,
			'button',
			'btn btn-primary',
			'Objednat',
			() => this.saveForm()
		);

		this.showFormMessage();
	}

	render() {
		this.upButton.disabled = (this.mode.modeUpKey === undefined);
		this.prevButton.disabled = (this.mode.getDatePrev(this.currentDay) < this.mode.roundDate(this.today));
		this.dateDesc.innerText = this.mode.getDescription(this.currentDay);

		if (this.reservations === null) {
			const spinner = CalendarMode.createElement(
				this.view,
				'div',
				'loading d-flex align-items-center justify-items-center',
				`<div class="spinner-border text-warning my-5 m-auto p-5" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>`
			);
			return;
		}

		this.mode.render();
	}

	unsetReservation() {
		if (!this.reservation) return;
		if (this.reservation.id && this.reservation.id > 0) return;
		this.reservations.splice(this.reservations.indexOf(this.reservation), 1);
		this.mode.render();
	}

}
